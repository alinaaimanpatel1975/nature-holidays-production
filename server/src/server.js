
import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import mongoose from "mongoose";
import Booking from "./models/Booking.js";
import bcrypt from "bcryptjs";
import jwt from "jsonwebtoken";
import Admin from "./models/Admin.js";
import verifyAdmin from "./middleware/verifyAdmin.js";
import crypto from "node:crypto";
import { paymentMode, razorpay } from "./config/payment.js";
dotenv.config({ path: "server/.env" });
import Enquiry from "./models/Enquiry.js";
import { notifyBusinessOwner } from "./services/whatsappNotification.js";


const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(cors());
app.use(express.json());
app.post("/api/admin/login", async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: "Email and password are required.",
      });
    }

    const admin = await Admin.findOne({
      email: email.toLowerCase().trim(),
    }).select("+password");

    if (!admin) {
      return res.status(401).json({
        success: false,
        message: "Invalid email or password.",
      });
    }

    const passwordMatches = await bcrypt.compare(
      password,
      admin.password
    );

    if (!passwordMatches) {
      return res.status(401).json({
        success: false,
        message: "Invalid email or password.",
      });
    }

    const token = jwt.sign(
      {
        adminId: admin._id,
        role: admin.role,
      },
      process.env.JWT_SECRET,
      { expiresIn: "2h" }
    );

    return res.status(200).json({
      success: true,
      message: "Admin login successful.",
      token,
      admin: {
        id: admin._id,
        name: admin.name,
        email: admin.email,
        role: admin.role,
      },
    });
  } catch (error) {
    console.error("Admin login error:", error.message);

    return res.status(500).json({
      success: false,
      message: "Something went wrong during login.",
    });
  }
});


// Create a new booking
app.post("/api/bookings", async (req, res) => {
  try {
    const booking = new Booking(req.body);

    const savedBooking = await booking.save();

    res.status(201).json({
      success: true,
      message: "Booking created successfully!",
      booking: savedBooking,
    });
  } catch (error) {
    res.status(400).json({
      success: false,
      message: error.message,
    });
  }
});

app.post("/api/payments/create-order", async (req, res) => {
  try {
    const { bookingId } = req.body;

    if (!bookingId || !mongoose.isValidObjectId(bookingId)) {
      return res.status(400).json({
        success: false,
        message: "A valid booking ID is required.",
      });
    }

    const booking = await Booking.findById(bookingId);

    if (!booking) {
      return res.status(404).json({
        success: false,
        message: "Booking not found.",
      });
    }

    if (booking.bookingStatus === "cancelled") {
      return res.status(400).json({
        success: false,
        message: "Cancelled bookings cannot be paid.",
      });
    }

    if (booking.paymentStatus !== "pending") {
      return res.status(400).json({
        success: false,
        message: "This booking is not awaiting payment.",
      });
    }

    // Development-only simulation order: â‚¹1, never a real package price.
    if (paymentMode === "simulation") {
      const orderId = `sim_${crypto.randomUUID()}`;

      booking.paymentMethod = "simulation";
      booking.paymentOrderId = orderId;
      await booking.save();

      return res.status(201).json({
        success: true,
        mode: "simulation",
        message: "Demo payment order created.",
        order: {
          id: orderId,
          amount: 100,
          currency: "INR",
        },
      });
    }

    return res.status(503).json({
      success: false,
      message: "Razorpay orders are not enabled until server-side package pricing is configured.",
    });
  } catch (error) {
    console.error("Payment order error:", error.message);

    return res.status(500).json({
      success: false,
      message: "Could not create payment order.",
    });
  }
});

app.post("/api/payments/verify", async (req, res) => {
  try {
    const { bookingId } = req.body;

    if (!bookingId || !mongoose.isValidObjectId(bookingId)) {
      return res.status(400).json({
        success: false,
        message: "A valid booking ID is required.",
      });
    }

    const booking = await Booking.findById(bookingId);

    if (!booking) {
      return res.status(404).json({
        success: false,
        message: "Booking not found.",
      });
    }

    if (booking.paymentStatus !== "pending") {
      return res.status(400).json({
        success: false,
        message: "This booking has already been processed.",
      });
    }

    // Local development simulator only.
    if (paymentMode === "simulation") {
      const { orderId } = req.body;
      const remoteAddress = req.socket.remoteAddress;

      const isLocalRequest = [
        "127.0.0.1",
        "::1",
        "::ffff:127.0.0.1",
      ].includes(remoteAddress);

      if (
        process.env.NODE_ENV === "production" ||
        !isLocalRequest ||
        !orderId ||
        orderId !== booking.paymentOrderId
      ) {
        return res.status(403).json({
          success: false,
          message: "Simulation confirmation is only available locally.",
        });
      }

      booking.paymentStatus = "paid";
      booking.paymentMethod = "simulation";
      booking.paymentId = `sim_payment_${crypto.randomUUID()}`;

      await booking.save();

      return res.status(200).json({
        success: true,
        mode: "simulation",
        message: "Demo payment recorded. No money was charged.",
        booking,
      });
    }

    // Real Razorpay signature verification.
    const {
      razorpay_order_id,
      razorpay_payment_id,
      razorpay_signature,
    } = req.body;

    if (
      !razorpay_order_id ||
      !razorpay_payment_id ||
      !razorpay_signature ||
      razorpay_order_id !== booking.paymentOrderId
    ) {
      return res.status(400).json({
        success: false,
        message: "Payment details are incomplete or do not match.",
      });
    }

    const expectedSignature = crypto
      .createHmac("sha256", process.env.RAZORPAY_KEY_SECRET)
      .update(`${razorpay_order_id}|${razorpay_payment_id}`)
      .digest("hex");

    const expectedBuffer = Buffer.from(expectedSignature);
    const receivedBuffer = Buffer.from(razorpay_signature);

    if (
      expectedBuffer.length !== receivedBuffer.length ||
      !crypto.timingSafeEqual(expectedBuffer, receivedBuffer)
    ) {
      return res.status(400).json({
        success: false,
        message: "Payment signature verification failed.",
      });
    }

    booking.paymentStatus = "paid";
    booking.paymentMethod = "razorpay";
    booking.paymentId = razorpay_payment_id;
    booking.paymentSignature = razorpay_signature;

    await booking.save();

    return res.status(200).json({
      success: true,
      mode: "razorpay",
      message: "Payment signature verified successfully.",
      booking,
    });
  } catch (error) {
    console.error("Payment verification error:", error.message);

    return res.status(500).json({
      success: false,
      message: "Could not verify payment.",
    });
  }
});

// Get all bookings
app.get("/api/bookings", verifyAdmin, async (req, res) => {
  try {
    const bookings = await Booking.find().sort({ createdAt: -1 });

    res.status(200).json({
      success: true,
      count: bookings.length,
      bookings,
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: error.message,
    });
  }
});


 // Update booking status
app.patch("/api/bookings/:id/status", verifyAdmin, async (req, res) => {
  try {
    const { bookingStatus } = req.body;

    if (!["pending", "confirmed", "cancelled"].includes(bookingStatus)) {
      return res.status(400).json({
        success: false,
        message: "Invalid booking status.",
      });
    }

    const updatedBooking = await Booking.findByIdAndUpdate(
      req.params.id,
      { bookingStatus },
      { new: true, runValidators: true }
    );

    if (!updatedBooking) {
      return res.status(404).json({
        success: false,
        message: "Booking not found.",
      });
    }

    res.json({
      success: true,
      message: "Booking status updated!",
      booking: updatedBooking,
    });
  } catch (error) {
    res.status(400).json({
      success: false,
      message: error.message,
    });
  }
});


// Delete a booking
app.delete("/api/bookings/:id", verifyAdmin, async (req, res) => {
  try {
    const deletedBooking = await Booking.findByIdAndDelete(
      req.params.id
    );

    if (!deletedBooking) {
      return res.status(404).json({
        success: false,
        message: "Booking not found.",
      });
    }

    res.status(200).json({
      success: true,
      message: "Booking deleted successfully!",
    });
  } catch (error) {
    res.status(400).json({
      success: false,
      message: error.message,
    });
  }
});



// Health check route
app.get("/api/health", (req, res) => {
  res.json({
    success: true,
    message: "Nature Holidays API is running!",
  });
});

// CREATE A NEW TRAVEL ENQUIRY
app.post("/api/enquiries", async (req, res) => {
  try {
    const {
      reference,
      customer,
      trip,
      price,
    } = req.body;

    if (
      !reference ||
      !customer?.name ||
      !customer?.email ||
      !customer?.phone ||
      !Number.isInteger(Number(trip?.travellers)) ||
      Number(trip.travellers) < 1
    ) {
      return res.status(400).json({
        success: false,
        message: "Please provide all required enquiry details.",
      });
    }

    const enquiry = await Enquiry.create({
      reference,
      customer: {
        name: customer.name,
        email: customer.email,
        phone: customer.phone,
      },
      trip: {
        packageName: trip.packageName || "",
        tripType: trip.tripType || "",
        destination: trip.destination || "",
        travelDate: trip.travelDate || null,
        travellers: Number(trip.travellers),
        departureCity: trip.departureCity || "",
        requests: trip.requests || "",
      },
      price: {
        indicativeAmount: price?.amount ?? null,
        basis: price?.basis || "",
      },
    });

    console.log("Enquiry saved. Starting WhatsApp notification...");

// Notify the business owner separately.
// A notification failure must not undo the saved enquiry.
try {
  await notifyBusinessOwner(enquiry);
} catch (notificationError) {
  console.error("WhatsApp notification failed:", notificationError);
}
    return res.status(201).json({
      success: true,
      message: "Enquiry saved successfully.",
      enquiry,
    });
  } catch (error) {
    console.error("Enquiry creation failed:", error);

    return res.status(500).json({
      success: false,
      message: "Could not save your enquiry.",
    });
  }
});

// Start server

// ADMIN: GET ALL ENQUIRIES
app.get("/api/admin/enquiries", verifyAdmin, async (req, res) => {
  try {
    const enquiries = await Enquiry.find().sort({ createdAt: -1 });

    return res.status(200).json({
      success: true,
      count: enquiries.length,
      enquiries,
    });
  } catch (error) {
    console.error("Fetch enquiries error:", error.message);

    return res.status(500).json({
      success: false,
      message: "Could not retrieve enquiries.",
    });
  }
});

// ADMIN: UPDATE ENQUIRY STATUS
app.patch("/api/admin/enquiries/:id/status", verifyAdmin, async (req, res) => {
  try {
    const { status } = req.body;
    const allowedStatuses = ["new", "contacted", "quoted", "confirmed", "cancelled"];

    if (!allowedStatuses.includes(status)) {
      return res.status(400).json({
        success: false,
        message: "Invalid enquiry status.",
      });
    }

    const enquiry = await Enquiry.findByIdAndUpdate(
      req.params.id,
      { status },
      { new: true, runValidators: true }
    );

    if (!enquiry) {
      return res.status(404).json({
        success: false,
        message: "Enquiry not found.",
      });
    }

    return res.json({
      success: true,
      message: "Enquiry status updated.",
      enquiry,
    });
  } catch (error) {
    console.error("Update enquiry status error:", error.message);
    return res.status(400).json({
      success: false,
      message: "Could not update enquiry status.",
    });
  }
});

// ADMIN: ADD PRIVATE ENQUIRY NOTE
app.post(
  "/api/admin/enquiries/:id/notes",
  verifyAdmin,
  async (req, res) => {
    try {
      const text = String(req.body.text || "").trim();

      if (!text || text.length > 2000) {
        return res.status(400).json({
          success: false,
          message: "Please enter a note between 1 and 2000 characters.",
        });
      }

      const enquiry = await Enquiry.findById(req.params.id);

      if (!enquiry) {
        return res.status(404).json({
          success: false,
          message: "Enquiry not found.",
        });
      }

      enquiry.internalNotes.push({
        text,
        author: req.admin?.name || req.admin?.email || "Admin",
        createdAt: new Date(),
      });

      await enquiry.save();

      return res.status(201).json({
        success: true,
        message: "Private note saved.",
        enquiry,
      });
    } catch (error) {
      console.error("Add enquiry note error:", error.message);

      return res.status(500).json({
        success: false,
        message: "Could not save the private note.",
      });
    }
  }
);

// CREATE A NEW TRAVEL ENQUIRY
app.post("/api/enquiries", async (req, res) => {
  try {
    const { reference, customer, trip, price, cart } = req.body;

    if (
      typeof reference !== "string" ||
      reference.length < 8 ||
      reference.length > 100 ||
      !customer?.name?.trim() ||
      !customer?.email?.trim() ||
      !customer?.phone?.trim() ||
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(customer.email) ||
      !Number.isInteger(Number(trip?.travellers)) ||
      Number(trip.travellers) < 1 ||
      Number(trip.travellers) > 5000
    ) {
      return res.status(400).json({
        success: false,
        message: "Please provide valid enquiry details.",
      });
    }

    const cartItems = Array.isArray(cart?.items) ? cart.items : [];

    if (cartItems.length > 50) {
      return res.status(400).json({
        success: false,
        message: "Too many cart items.",
      });
    }

    const safeCartItems = cartItems.map((item) => ({
      cartId: String(item.cartId || "").slice(0, 100),
      name: String(item.name || "").trim().slice(0, 160),
      destination: String(item.destination || "").slice(0, 120),
      packageName: String(item.packageName || "").slice(0, 160),
      image: String(item.image || "").slice(0, 1000),
      date: String(item.date || "").slice(0, 40),
      quantity: Math.min(20, Math.max(1, Number(item.quantity) || 1)),
      pricingStatus:
        item.pricingStatus === "listed" ? "listed" : "quote-required",
      total:
        item.pricingStatus === "listed" &&
        Number.isFinite(Number(item.total)) &&
        Number(item.total) >= 0
          ? Number(item.total)
          : null,
    }));

    if (safeCartItems.some((item) => !item.name)) {
      return res.status(400).json({
        success: false,
        message: "A selected cart item is missing its name.",
      });
    }

    const safeDate = (value) => {
      if (!value) return null;
      const date = new Date(value);
      return Number.isNaN(date.getTime()) ? null : date;
    };

    const enquiry = await Enquiry.create({
      reference: reference.trim(),
      customer: {
        name: customer.name.trim().slice(0, 100),
        email: customer.email.trim().toLowerCase(),
        phone: customer.phone.trim().slice(0, 20),
      },
      trip: {
        packageName: String(trip.packageName || "").slice(0, 160),
        tripType: String(trip.tripType || "").slice(0, 100),
        destination: String(trip.destination || "").slice(0, 120),
        travelDate: safeDate(trip.travelDate),
        endDate: safeDate(trip.endDate),
        travellers: Number(trip.travellers),
        departureCity: String(trip.departureCity || "").slice(0, 100),
        requests: String(trip.requests || "").slice(0, 1200),
      },
      price: {
        indicativeAmount:
          price?.amount !== null &&
          price?.amount !== undefined &&
          Number.isFinite(Number(price.amount)) &&
          Number(price.amount) >= 0
            ? Number(price.amount)
            : null,
        basis: String(price?.basis || "").slice(0, 120),
      },
      cart: {
        items: safeCartItems,
        experienceCount: safeCartItems.length,
        totalTravellers: safeCartItems.reduce(
          (sum, item) => sum + item.quantity,
          0
        ),
      },
    });

    try {
      await notifyBusinessOwner(enquiry);
    } catch (notificationError) {
      console.error("WhatsApp notification failed:", notificationError);
    }

    return res.status(201).json({
      success: true,
      message: "Enquiry saved successfully.",
      enquiry: {
        reference: enquiry.reference,
      },
    });
  } catch (error) {
    console.error("Enquiry creation failed:", error);

    if (error.code === 11000) {
      return res.status(409).json({
        success: false,
        message: "This enquiry reference already exists. Please try again.",
      });
    }

    return res.status(500).json({
      success: false,
      message: "Could not save your enquiry.",
    });
  }
});

// CUSTOMER: RETRIEVE AN ENQUIRY USING ITS REFERENCE
app.get("/api/enquiries/:reference", async (req, res) => {
  try {
    const enquiry = await Enquiry.findOne({
      reference: req.params.reference,
    }).select("-internalNotes -__v");

    if (!enquiry) {
      return res.status(404).json({
        success: false,
        message: "Enquiry not found.",
      });
    }

    return res.json({
      success: true,
      enquiry: {
        id: enquiry.reference,
        reference: enquiry.reference,
        createdAt: enquiry.createdAt,
        status: enquiry.status,
        customer: enquiry.customer,
        trip: enquiry.trip,
        price: {
          amount: enquiry.price?.indicativeAmount ?? null,
          basis: enquiry.price?.basis || "",
          label: enquiry.price?.indicativeAmount != null
            ? `₹${new Intl.NumberFormat("en-IN").format(enquiry.price.indicativeAmount)}`
            : "Price on request",
        },
        cart: enquiry.cart,
      },
    });
  } catch (error) {
    console.error("Enquiry retrieval failed:", error.message);

    return res.status(500).json({
      success: false,
      message: "Could not retrieve your enquiry.",
    });
  }
});


async function startServer() {
  try {
    await mongoose.connect(process.env.MONGODB_URI);

    console.log("MongoDB connected successfully!");

    app.listen(PORT, () => {
      console.log(`Nature Holidays API running on http://localhost:${PORT}`);
    });
  } catch (error) {
    console.error("Database connection failed:", error.message);
    process.exit(1);
  }
}

startServer();



