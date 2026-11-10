
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import {
  readCart,
  subscribeToCart,
  getDistinctExperienceCount,
} from "../utils/cart";

import "./FloatingCart.css";

export default function FloatingCart() {
  const [count, setCount] = useState(0);

  useEffect(() => {
    const syncCart = (items) => {
      setCount(getDistinctExperienceCount(items));
    };

    syncCart(readCart());

    return subscribeToCart(syncCart);
  }, []);

  return (
    <Link
      to="/cart"
      className="nh-floating-cart"
      aria-label={`Open your trip cart. ${count} selected experiences`}
    >
      <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="1.7"
        strokeLinecap="round"
        strokeLinejoin="round"
        aria-hidden="true"
      >
        <path d="M3 3h2l2.4 12.2a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 7H6" />
        <circle cx="10" cy="21" r="1" />
        <circle cx="18" cy="21" r="1" />
      </svg>

      <span className="nh-floating-cart-label">My Trip</span>

      {count > 0 && (
        <span className="nh-floating-cart-count">
          {count > 99 ? "99+" : count}
        </span>
      )}
    </Link>
  );
}
