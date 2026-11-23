
import { useEffect, useState } from "react";
import { Link, NavLink, useLocation } from "react-router-dom";

import "./Navbar.css";

const serviceCategories = [
  {
    id: "holidays",
    number: "01",
    title: "Holidays & Packages",
    description: "Find a journey that feels like yours.",
    services: [
      { label: "Holiday packages", to: "/packages" },
      {
        label: "Family holidays",
        to: "/contact?tripType=Family%20Holiday",
      },
      {
        label: "Honeymoon trips",
        to: "/contact?tripType=Honeymoon",
      },
      {
        label: "Weekend getaways",
        to: "/contact?tripType=Weekend%20%26%20Day%20Outing",
      },
    ],
  },
  {
    id: "adventure",
    number: "02",
    title: "Adventure & Special Interest",
    description: "For the curious, the bold and the outdoorsy.",
    services: [
      {
        label: "Trekking & outdoors",
        to: "/tours-experiences?category=adventure",
      },
      {
        label: "Wildlife experiences",
        to: "/tours-experiences?category=nature",
      },
      {
        label: "Camping & adventure",
        to: "/tours-experiences?category=adventure",
      },
      {
        label: "Special interest tours",
        to: "/tours-experiences",
      },
    ],
  },
  {
    id: "culture",
    number: "03",
    title: "Cultural, Heritage & Religious",
    description: "Discover the stories behind every place.",
    services: [
      {
        label: "Pilgrimage journeys",
        to: "/tours-experiences?category=culture",
      },
      {
        label: "Heritage tours",
        to: "/tours-experiences?category=culture",
      },
      {
        label: "Cultural experiences",
        to: "/tours-experiences?category=culture",
      },
    ],
  },
  {
    id: "education",
    number: "04",
    title: "Educational & Institutional",
    description: "Meaningful journeys for learning together.",
    services: [
      {
        label: "School excursions",
        to: "/contact?tripType=School%20%26%20College",
      },
      {
        label: "College tours",
        to: "/contact?tripType=School%20%26%20College",
      },
      {
        label: "Educational trips",
        to: "/contact?tripType=School%20%26%20College",
      },
    ],
  },
  {
    id: "corporate",
    number: "05",
    title: "Corporate & Business Travel",
    description: "Thoughtful travel for teams and organisations.",
    services: [
      {
        label: "Corporate trips",
        to: "/contact?tripType=Corporate%20%26%20Group",
      },
      {
        label: "Team outings",
        to: "/contact?tripType=Corporate%20%26%20Group",
      },
      {
        label: "Business travel",
        to: "/contact?tripType=Corporate%20%26%20Group",
      },
    ],
  },
  {
    id: "hotels",
    number: "06",
    title: "Hotels & Accommodation",
    description: "Find a stay that makes the journey.",
    services: [
      {
        label: "Hotels & stays",
        to: "/tours-experiences?category=stays",
      },
      {
        label: "Retreats",
        to: "/tours-experiences?category=stays",
      },
      {
        label: "Accommodation enquiries",
        to: "/contact",
      },
    ],
  },
  {
    id: "reservations",
    number: "07",
    title: "Reservations",
    description: "Plan the connections around your trip.",
    services: [
      { label: "Flight enquiries", to: "/contact" },
      { label: "Train enquiries", to: "/contact" },
      { label: "Bus enquiries", to: "/contact" },
    ],
  },
  {
    id: "transport",
    number: "08",
    title: "Transport & Transfers",
    description: "Make getting there part of the plan.",
    services: [
      {
        label: "Transport options",
        to: "/explore/transport",
      },
      {
        label: "Transfers",
        to: "/explore/transport",
      },
      {
        label: "Transport enquiries",
        to: "/contact",
      },
    ],
  },
  {
    id: "planning",
    number: "09",
    title: "Travel Planning & Concierge",
    description: "A little help turning ideas into itineraries.",
    services: [
      { label: "Custom trip planning", to: "/contact" },
      { label: "Personalised itineraries", to: "/contact" },
      { label: "Speak to our team", to: "/contact" },
    ],
  },
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
  const [servicesOpen, setServicesOpen] = useState(false);
  const [activeService, setActiveService] = useState("holidays");

  const location = useLocation();

  const activeCategory =
    serviceCategories.find((item) => item.id === activeService) ||
    serviceCategories[0];

  useEffect(() => {
    setMenuOpen(false);
    setServicesOpen(false);
  }, [location.pathname, location.search]);

  useEffect(() => {
    function closeOnEscape(event) {
      if (event.key === "Escape") {
        setServicesOpen(false);
        setMenuOpen(false);
      }
    }

    document.addEventListener("keydown", closeOnEscape);

    return () => {
      document.removeEventListener("keydown", closeOnEscape);
    };
  }, []);

  function closeMenus() {
    setServicesOpen(false);
    setMenuOpen(false);
  }

  return (
    <header className="nh-header">
      <nav className="nh-navbar" aria-label="Main navigation">
        <Link
          className="nh-brand"
          to="/"
          aria-label="Nature Holidays home"
          onClick={closeMenus}
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
            menuOpen ? "Close navigation menu" : "Open navigation menu"
          }
          aria-expanded={menuOpen}
          aria-controls="nh-navigation"
          onClick={() => {
            setMenuOpen((current) => !current);
            setServicesOpen(false);
          }}
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
              onClick={closeMenus}
            >
              Home
            </NavLink>

            <NavLink
              to="/packages"
              className={navClass}
              onClick={closeMenus}
            >
              Packages
            </NavLink>

            <NavLink
              to="/destinations"
              className={navClass}
              onClick={closeMenus}
            >
              Destinations
            </NavLink>

            <NavLink
              to="/tours-experiences"
              className={navClass}
              onClick={closeMenus}
            >
              Tours & Experiences
            </NavLink>

            <div
              className={`nh-services ${
                servicesOpen ? "nh-services--open" : ""
              }`}
            >
              <button
                type="button"
                className={`nh-nav-link nh-services-trigger ${
                  servicesOpen ? "nh-nav-link--active" : ""
                }`}
                aria-expanded={servicesOpen}
                aria-controls="nh-services-menu"
                onClick={() => setServicesOpen((current) => !current)}
              >
                Services
                <span className="nh-dropdown-arrow" aria-hidden="true">
                  ⌄
                </span>
              </button>

              {servicesOpen && (
                <>
                  <button
                    type="button"
                    className="nh-menu-backdrop"
                    aria-label="Close Services menu"
                    onClick={() => setServicesOpen(false)}
                  />

                  <div
                    className="nh-services-menu"
                    id="nh-services-menu"
                  >
                    <div className="nh-services-sidebar">
                      <span className="nh-menu-eyebrow">
                        WHAT WE CAN HELP WITH
                      </span>

                      <div className="nh-service-category-list">
                        {serviceCategories.map((category) => (
                          <button
                            type="button"
                            key={category.id}
                            className={`nh-service-category ${
                              activeService === category.id
                                ? "nh-service-category--active"
                                : ""
                            }`}
                            onClick={() =>
                              setActiveService(category.id)
                            }
                            onMouseEnter={() =>
                              setActiveService(category.id)
                            }
                            aria-pressed={activeService === category.id}
                          >
                            <span className="nh-service-category-number">
                              {category.number}
                            </span>

                            <span>{category.title}</span>

                            <span
                              className="nh-service-category-arrow"
                              aria-hidden="true"
                            >
                              →
                            </span>
                          </button>
                        ))}
                      </div>
                    </div>

                    <div className="nh-services-content">
                      <span className="nh-menu-eyebrow">
                        EXPLORE OUR SERVICES
                      </span>

                      <h2>{activeCategory.title}</h2>
                      <p>{activeCategory.description}</p>

                      <div className="nh-service-sublist">
                        {activeCategory.services.map((service) => (
                          <Link
                            key={service.label}
                            to={service.to}
                            className="nh-service-subitem"
                            onClick={closeMenus}
                          >
                            <span>{service.label}</span>
                            <span aria-hidden="true">↗</span>
                          </Link>
                        ))}
                      </div>

                      <Link
                        to="/contact"
                        className="nh-services-cta"
                        onClick={closeMenus}
                      >
                        Talk to our travel team
                        <span aria-hidden="true">↗</span>
                      </Link>
                    </div>
                  </div>
                </>
              )}
            </div>

            <NavLink
              to="/contact"
              className={navClass}
              onClick={closeMenus}
            >
              Contact
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

            <Link
              className="nh-plan-button"
              to="/contact"
              onClick={closeMenus}
            >
              Plan a trip <span aria-hidden="true">↗</span>
            </Link>
          </div>
        </div>
      </nav>
    </header>
  );
}
