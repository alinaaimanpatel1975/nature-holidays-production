
import mongoose from "mongoose";

const cartItemSchema = new mongoose.Schema(
  {
    cartId: { type: String, default: "" },
    name: { type: String, required: true, trim: true, maxlength: 160 },
    destination: { type: String, default: "", maxlength: 120 },
    packageName: { type: String, default: "", maxlength: 160 },
    image: { type: String, default: "", maxlength: 1000 },
    date: { type: String, default: "", maxlength: 40 },
    quantity: { type: Number, required: true, min: 1, max: 20 },
    pricingStatus: {
      type: String,
      enum: ["listed", "quote-required"],
      default: "quote-required",
    },
    total: { type: Number, default: null, min: 0 },
  },
  { _id: false }
);

const enquirySchema = new mongoose.Schema(
  {
    reference: {
      type: String,
      required: true,
      unique: true,
      trim: true,
    },

    customer: {
      name: { type: String, required: true, trim: true, maxlength: 100 },
      email: { type: String, required: true, lowercase: true, trim: true },
      phone: { type: String, required: true, trim: true, maxlength: 20 },
    },

    trip: {
      packageName: { type: String, trim: true, default: "", maxlength: 160 },
      tripType: { type: String, default: "", maxlength: 100 },
      destination: { type: String, default: "", maxlength: 120 },
      travelDate: { type: Date, default: null },
      endDate: { type: Date, default: null },
      travellers: { type: Number, required: true, min: 1, max: 5000 },
      departureCity: { type: String, default: "", maxlength: 100 },
      requests: { type: String, default: "", maxlength: 1200 },
    },

    price: {
      indicativeAmount: { type: Number, default: null, min: 0 },
      basis: { type: String, default: "", maxlength: 120 },
    },

    cart: {
      items: { type: [cartItemSchema], default: [] },
      experienceCount: { type: Number, default: 0, min: 0 },
      totalTravellers: { type: Number, default: 0, min: 0 },
    },

    internalNotes: [
      {
        text: { type: String, required: true, trim: true },
        author: { type: String, default: "Admin" },
        createdAt: { type: Date, default: Date.now },
      },
    ],

    status: {
      type: String,
      enum: ["new", "contacted", "quoted", "confirmed", "cancelled"],
      default: "new",
    },
  },
  { timestamps: true }
);

const Enquiry = mongoose.model("Enquiry", enquirySchema);

export default Enquiry;
