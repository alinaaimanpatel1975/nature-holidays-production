
import { useEffect, useState } from "react";
import { Link, useLocation, useParams } from "react-router-dom";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

import "./EnquiryConfirmation.css";


function formatDate(value) {
  if (!value) return "Flexible / not decided";

  const date = /^\d{4}-\d{2}-\d{2}$/.test(value)
    ? new Date(`${value}T00:00:00`)
    : new Date(value);

  if (Number.isNaN(date.getTime())) {
    return "Not specified";
  }

  return date.toLocaleDateString("en-IN", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function formatCreatedAt(value) {
  if (!value) return "";

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) return "";

  return date.toLocaleString("en-IN", {
    dateStyle: "medium",
    timeStyle: "short",
  });
}

function formatPrice(amount) {
  if (amount === null || amount === undefined) {
    return "Price on request";
  }

  return `₹${new Intl.NumberFormat("en-IN").format(amount)}`;
}

export default function EnquiryConfirmation() {
  const { id } = useParams();
  const location = useLocation();

  const [enquiry] = useState(
    location.state?.enquiry || null
  );

  const isLoading = false;
  const error = enquiry ? "" : "Your confirmation details aren't available in this browser session.";
  if (isLoading) {
    return (
      <>
        <Navbar />

        <main className="enquiry-missing">
          <span className="enquiry-eyebrow">PLEASE WAIT</span>
          <h1>Retrieving your enquiry.</h1>
          <p>We're loading your submitted trip details.</p>
        </main>

        <Footer />
      </>
    );
  }

  if (error || !enquiry) {
    return (
      <>
        <Navbar />

        <main className="enquiry-missing">
          <span className="enquiry-eyebrow">ENQUIRY UNAVAILABLE</span>
          <h1>We couldn't retrieve your enquiry.</h1>
          <p>
            {error ||
              "This enquiry could not be found. Please check your reference or contact Nature Holidays."}
          </p>

          <Link to="/contact" className="enquiry-primary-link">
            Start a new enquiry
          </Link>
        </main>

        <Footer />
      </>
    );
  }

  const { customer = {}, trip = {}, price = {}, cart = {} } = enquiry;
  const cartItems = Array.isArray(cart?.items) ? cart.items : [];

  return (
    <>
      <Navbar />

      <main className="enquiry-confirmation-page">
        <nav className="enquiry-breadcrumb" aria-label="Breadcrumb">
          <Link to="/">Home</Link>
          <span>/</span>
          <Link to="/contact">Contact</Link>
          <span>/</span>
          <span aria-current="page">Enquiry details</span>
        </nav>

        <section className="enquiry-success-panel">
          <div className="enquiry-success-icon" aria-hidden="true">
            ✓
          </div>

          <span className="enquiry-eyebrow">
            YOUR ENQUIRY HAS BEEN RECEIVED
          </span>

          <h1>Your next chapter starts here.</h1>

          <p>
            Your travel enquiry has been successfully submitted
            and saved. The Nature Holidays team can now review
            your requirements and follow up with suitable options.
          </p>

          <div className="enquiry-reference">
            <span>YOUR ENQUIRY REFERENCE</span>
            <strong>{enquiry.reference || enquiry.id}</strong>
          </div>

          <div className="enquiry-status-note" role="status">
            <strong>Enquiry submitted · Awaiting response</strong>
            <span>
              Your enquiry is recorded, but your trip is not yet
              confirmed. Final pricing and availability must be
              verified by Nature Holidays.
            </span>
          </div>
        </section>

        <section className="enquiry-details-panel">
          <div className="enquiry-details-heading">
            <div>
              <span className="enquiry-eyebrow">A LITTLE RECAP</span>
              <h2>Your trip details.</h2>
            </div>

            <span className="enquiry-created">
              Submitted {formatCreatedAt(enquiry.createdAt)}
            </span>
          </div>

          <div className="enquiry-detail-grid">
            <div className="enquiry-detail-item">
              <span>Package</span>
              <strong>{trip.packageName || "Custom trip"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Destination</span>
              <strong>{trip.destination || "Not specified"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Travel date</span>
              <strong>{formatDate(trip.travelDate)}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Return date</span>
              <strong>{formatDate(trip.endDate)}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Travellers</span>
              <strong>{trip.travellers ?? "Not specified"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Trip type</span>
              <strong>{trip.tripType || "Not specified"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Departure city</span>
              <strong>{trip.departureCity || "Not specified"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Displayed estimate</span>
              <strong>
                {price.label ||
                  (price.amount != null
                    ? formatPrice(price.amount)
                    : "Price on request")}
              </strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Contact name</span>
              <strong>{customer.name || "Not specified"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Email address</span>
              <strong>{customer.email || "Not specified"}</strong>
            </div>

            <div className="enquiry-detail-item">
              <span>Phone number</span>
              <strong>{customer.phone || "Not specified"}</strong>
            </div>
          </div>

          {trip.requests && (
            <div className="enquiry-special-requests">
              <span>Additional requirements</span>
              <p>{trip.requests}</p>
            </div>
          )}

          {cartItems.length > 0 && (
            <div className="enquiry-special-requests">
              <span>Selected experiences ({cartItems.length})</span>

              <div className="enquiry-confirmation-cart">
                {cartItems.map((item, index) => (
                  <article
                    className="enquiry-confirmation-cart-item"
                    key={item.cartId || `${item.name}-${index}`}
                  >
                    {item.image && (
                      <img
                        src={item.image}
                        alt={item.name || "Selected experience"}
                      />
                    )}

                    <div>
                      <strong>
                        {item.name || "Selected experience"}
                      </strong>

                      <p>
                        {[item.destination, item.packageName]
                          .filter(Boolean)
                          .join(" · ") || "Destination not specified"}
                      </p>

                      <small>
                        {item.quantity || 1}{" "}
                        {(item.quantity || 1) === 1
                          ? "traveller"
                          : "travellers"}
                        {item.date ? ` · ${item.date}` : ""}
                      </small>
                    </div>

                    <strong>
                      {item.pricingStatus === "listed" &&
                      item.total != null
                        ? formatPrice(item.total)
                        : "Quote required"}
                    </strong>
                  </article>
                ))}
              </div>
            </div>
          )}
        </section>

        <div className="enquiry-bottom-links">
          <Link to="/my-trips">View My Trips ↗</Link>
          <Link to="/packages">Explore more packages</Link>
        </div>
      </main>

      <Footer />
    </>
  );
}
