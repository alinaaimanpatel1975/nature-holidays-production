
import { useEffect, useState } from "react";
import { Link, NavLink, useLocation } from "react-router-dom";

import "./Navbar.css";

const moreLinks = [
  { label: "Destinations", path: "/destinations" },
  { label: "Things to do", path: "/explore/things-to-do" },
  { label: "Accommodation", path: "/explore/accommodation" },
  { label: "Transport", path: "/explore/transport" },
];

function navClass({ isActive }) {
  return isActive
    ? "nh-nav-link nh-nav-link--active"
    : "nh-nav-link";
}

export default function Navbar({
  showCityPicker = false,
  homeCity = "Bengaluru",
  onHomeCityChange,
}) {
  const [menuOpen, setMenuOpen] = useState(false);
  const [moreOpen, setMoreOpen] = useState(false);

  const location = useLocation();

  useEffect(() => {
    setMenuOpen(false);
    setMoreOpen(false);
  }, [location.pathname, location.search]);

  const isMoreActive =
    location.pathname.startsWith("/explore") ||
    location.pathname.startsWith("/destinations");

  return (
    <header className="nh-header">
      <nav className="nh-navbar" aria-label="Main navigation">
        <Link
          className="nh-brand"
          to="/"
          aria-label="Nature Holidays home"
        >
          <img
            src={`${import.meta.env.BASE_URL}images/logo.png`}
            alt="Nature Holidays"
          />

          <span>
            Nature <strong>Holidays</strong>
            <small>TRAVEL. YOUR WAY.</small>
          </span>
        </Link>

        <button
          className="nh-mobile-toggle"
          type="button"
          aria-label={
            menuOpen
              ? "Close navigation menu"
              : "Open navigation menu"
          }
          aria-expanded={menuOpen}
          aria-controls="nh-navigation"
          onClick={() => setMenuOpen((current) => !current)}
        >
          <span />
          <span />
          <span />
        </button>

        <div
          className={`nh-navigation ${
            menuOpen ? "nh-navigation--open" : ""
          }`}
          id="nh-navigation"
        >
          <div className="nh-nav-links">
            <NavLink
              to="/"
              end
              className={navClass}
              onClick={() => setMenuOpen(false)}
            >
              Home
            </NavLink>

            <NavLink
              to="/tours-experiences"
              className={navClass}
              onClick={() => setMenuOpen(false)}
            >
              Tours & Experiences
            </NavLink>

            <NavLink
              to="/packages"
              className={navClass}
              onClick={() => setMenuOpen(false)}
            >
              Packages
            </NavLink>

            <details
              className={`nh-experiences ${
                isMoreActive ? "nh-experiences--active" : ""
              }`}
              open={moreOpen}
              onToggle={(event) => {
                setMoreOpen(event.currentTarget.open);
              }}
            >
              <summary
                className={
                  isMoreActive
                    ? "nh-nav-link nh-nav-link--active"
                    : "nh-nav-link"
                }
              >
                More
                <span
                  className="nh-dropdown-arrow"
                  aria-hidden="true"
                >
                  ⌄
                </span>
              </summary>

              <div className="nh-experiences-menu">
                <span className="nh-menu-eyebrow">
                  DISCOVER INDIA
                </span>

                {moreLinks.map((item) => (
                  <NavLink
                    key={item.path}
                    to={item.path}
                    className={navClass}
                    onClick={() => {
                      setMenuOpen(false);
                      setMoreOpen(false);
                    }}
                  >
                    {item.label}
                    <span aria-hidden="true">↗</span>
                  </NavLink>
                ))}
              </div>
            </details>

            <NavLink
              to="/my-trips"
              className={navClass}
              onClick={() => setMenuOpen(false)}
            >
              My Trips
            </NavLink>

            <NavLink
              to="/contact"
              className={navClass}
              onClick={() => setMenuOpen(false)}
            >
              Contact
            </NavLink>

            <NavLink
              to="/about"
              className={navClass}
              onClick={() => setMenuOpen(false)}
            >
              About Us
            </NavLink>
          </div>

          <div className="nh-nav-actions">
            {showCityPicker && (
              <label className="nh-city-picker">
                <span>Your city</span>

                <select
                  aria-label="Home city"
                  value={homeCity}
                  onChange={(event) =>
                    onHomeCityChange?.(event.target.value)
                  }
                >
                  <option>Bengaluru</option>
                  <option>Mumbai</option>
                  <option>Delhi</option>
                  <option>Chennai</option>
                  <option>Hyderabad</option>
                  <option>Pune</option>
                </select>
              </label>
            )}

            {showCityPicker && (
              <span className="nh-currency">₹ INR</span>
            )}
          </div>
        </div>
      </nav>
    </header>
  );
}
