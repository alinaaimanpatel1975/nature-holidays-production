
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

import {
  readCart,
  updateCartItem,
  removeCartItem,
  subscribeToCart,
  getCartTotals,
  getDistinctExperienceCount,
} from "../utils/cart";

import "./CartPage.css";

const money = (value) =>
  new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(value);

export default function CartPage() {
  const [items, setItems] = useState([]);

  useEffect(() => {
    setItems(readCart());

    return subscribeToCart(setItems);
  }, []);

  function changeQuantity(item, amount) {
    const updatedQuantity = Math.min(
      20,
      Math.max(1, item.quantity + amount)
    );

    updateCartItem(item.cartId, {
      quantity: updatedQuantity,
      people: updatedQuantity,
    });
  }

  function removeItem(cartId) {
    removeCartItem(cartId);
  }

  const totals = getCartTotals(items);
  const experienceCount = getDistinctExperienceCount(items);

  return (
    <div className="nh-cart-page">
      <Navbar />

      <main className="nh-cart-shell">
        <div className="nh-cart-breadcrumb">
          <Link to="/">Home</Link>
          <span>/</span>
          <span>Your cart</span>
        </div>

        <header className="nh-cart-heading">
          <span>YOUR TRIP COLLECTION</span>
          <h1>Your cart.</h1>
          <p>
            Keep the experiences you don't want to miss in one place.
          </p>
        </header>

        {items.length === 0 ? (
          <div className="nh-cart-empty">
            <span>✦</span>
            <h2>Your cart is waiting for a story.</h2>
            <p>
              Explore India and add an experience to start planning.
            </p>
            <Link to="/tours-experiences">
              Explore experiences →
            </Link>
          </div>
        ) : (
          <div className="nh-cart-layout">
            <section className="nh-cart-items">
              {items.map((item) => (
                <article
                  className="nh-cart-item"
                  key={item.cartId}
                >
                  {item.image ? (
                    <img
                      src={item.image}
                      alt={item.name}
                    />
                  ) : (
                    <div className="nh-cart-item-placeholder">
                      NH
                    </div>
                  )}

                  <div className="nh-cart-item-info">
                    <span>{item.destination}</span>

                    <h2>{item.name}</h2>

                    <p>{item.packageName}</p>

                    <p>
                      {item.date || "Date to be confirmed"}
                    </p>

                    <div className="nh-cart-quantity">
                      <button
                        type="button"
                        onClick={() => changeQuantity(item, -1)}
                        disabled={item.quantity <= 1}
                        aria-label={`Remove one traveller from ${item.name}`}
                      >
                        −
                      </button>

                      <strong>{item.quantity}</strong>

                      <button
                        type="button"
                        onClick={() => changeQuantity(item, 1)}
                        disabled={item.quantity >= 20}
                        aria-label={`Add one traveller to ${item.name}`}
                      >
                        +
                      </button>
                    </div>

                    <small>
                      {item.quantity}{" "}
                      {item.quantity === 1 ? "traveller" : "travellers"}
                    </small>
                  </div>

                  <div className="nh-cart-item-price">
                    <strong>
                      {item.pricingStatus === "listed" &&
                      item.total !== null
                        ? money(item.total)
                        : "Enquire for price"}
                    </strong>

                    {item.pricingStatus === "listed" && (
                      <small>Listed-price estimate</small>
                    )}

                    <button
                      type="button"
                      onClick={() => removeItem(item.cartId)}
                    >
                      Remove
                    </button>
                  </div>
                </article>
              ))}
            </section>

            <aside className="nh-cart-summary">
              <h2>Trip summary</h2>

              <div>
                <span>Selected experiences</span>
                <strong>{experienceCount}</strong>
              </div>

              <div>
                <span>Estimated total</span>
                <strong>
                  {totals.hasPricedItems
                    ? money(totals.total)
                    : "—"}
                </strong>
              </div>

              {totals.hasQuoteItems && (
                <p>
                  Some experiences need a custom quote. Their prices
                  are not included in this estimate.
                </p>
              )}

              <Link
                to="/contact"
                className="nh-cart-enquiry"
              >
                Continue to enquiry →
              </Link>

              <small>
                This is a saved selection, not a confirmed booking
                or payment. Availability and final prices must be
                confirmed by Nature Holidays.
              </small>
            </aside>
          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}
