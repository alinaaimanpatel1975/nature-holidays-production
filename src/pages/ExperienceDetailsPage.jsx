
import { useMemo, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

import {
  marketplacePackages,
  marketplaceExperiences,
  marketplaceDestinations,
} from "../data/marketplace";

import { destinationImages } from "../data/destination-images";
import { addCartItem } from "../utils/cart";

import "./ExperienceDetailsPage.css";

function getName(item) {
  return item?.name || item?.title || "Experience";
}

function getDestination(item) {
  return marketplaceDestinations.find(
    (destination) =>
      destination.slug === item?.destinationSlug ||
      destination.name?.toLowerCase() ===
        String(item?.destination || "").toLowerCase()
  );
}

function getDuration(item) {
  const duration = item?.duration;

  if (!duration) return "Duration confirmed after enquiry";

  if (typeof duration === "string" || typeof duration === "number") {
    return String(duration);
  }

  if (duration.label) return duration.label;
  if (duration.hours) return `Approx. ${duration.hours} hours`;

  if (duration.days && duration.nights !== undefined) {
    return `${duration.days} days · ${duration.nights} nights`;
  }

  if (duration.days) return `${duration.days} days`;

  return "Duration confirmed after enquiry";
}

function getImages(item, destination) {
  const directImages = [
    ...(Array.isArray(item?.images) ? item.images : []),
    item?.image,
    item?.coverImage,
    item?.thumbnail,
  ].filter(Boolean);

  const destinationName = (
    destination?.name ||
    item?.destination ||
    ""
  ).toLowerCase();

  const localImages = destinationImages
    .filter(
      (photo) =>
        photo.destination?.toLowerCase() === destinationName
    )
    .map((photo) => photo.image);

  return [...new Set([...directImages, ...localImages])].slice(0, 8);
}

function getTextList(value) {
  if (!Array.isArray(value)) return [];

  return value
    .map((entry) => {
      if (typeof entry === "string") return entry;

      return (
        entry?.title ||
        entry?.name ||
        entry?.description ||
        entry?.text ||
        ""
      );
    })
    .filter(Boolean);
}

function getActualPrice(item) {
  const value =
    item?.pricing?.startingPrice ??
    item?.startingPrice ??
    (typeof item?.price === "number" ? item.price : null);

  return Number.isFinite(Number(value)) && Number(value) > 0
    ? Number(value)
    : null;
}

function formatINR(value) {
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(value);
}

function getOptions(item) {
  const supplied = item?.packageOptions || item?.options;

  if (Array.isArray(supplied) && supplied.length) {
    return supplied.map((option, index) => {
      const rawPrice = option.price;

      const price =
        rawPrice !== null &&
        rawPrice !== undefined &&
        rawPrice !== "" &&
        Number.isFinite(Number(rawPrice)) &&
        Number(rawPrice) > 0
          ? Number(rawPrice)
          : null;

      return {
        id: String(option.id || index),
        name: option.name || option.title || `Option ${index + 1}`,
        price,
        description: option.description || "",
      };
    });
  }

  return [
    {
      id: "standard",
      name: "Standard experience",
      price: getActualPrice(item),
      description: "Details and final pricing confirmed by our team.",
    },
  ];
}

function DetailSection({ title, children, id }) {
  return (
    <section className="ed-section" id={id}>
      <h2>{title}</h2>
      {children}
    </section>
  );
}

export default function ExperienceDetailsPage() {
  const { id } = useParams();
  const navigate = useNavigate();

  const listing = useMemo(() => {
    const allListings = [
      ...marketplacePackages.map((item) => ({
        ...item,
        productType: "package",
      })),

      ...marketplaceExperiences.map((item) => ({
        ...item,
        productType: "experience",
      })),
    ];

    return allListings.find(
      (item) => String(item.id) === String(id)
    );
  }, [id]);

  const destination = listing ? getDestination(listing) : null;
  const images = listing ? getImages(listing, destination) : [];
  const options = listing ? getOptions(listing) : [];

  const [activeImage, setActiveImage] = useState(0);

  const [selectedOption, setSelectedOption] = useState(
    options[0]?.id || "standard"
  );

  const [travelDate, setTravelDate] = useState("");
  const [quantity, setQuantity] = useState(1);
  const [showAllHighlights, setShowAllHighlights] = useState(false);
  const [added, setAdded] = useState(false);

  if (!listing) {
    return (
      <div className="ed-page">
        <Navbar />

        <main className="ed-not-found">
          <span>404</span>
          <h1>Experience not found</h1>
          <p>This listing may have moved or is no longer available.</p>
          <Link to="/tours-experiences">
            Explore experiences
          </Link>
        </main>

        <Footer />
      </div>
    );
  }

  const name = getName(listing);

  const location =
    listing.location ||
    listing.destination ||
    destination?.name ||
    listing.state ||
    "India";

  const description =
    listing.overview ||
    listing.description ||
    listing.summary ||
    destination?.introduction ||
    "Discover this experience with Nature Holidays. Contact our team for the complete experience details.";

  const highlights = getTextList(
    listing.highlights || listing.experienceHighlights
  );

  const itinerary = getTextList(listing.itinerary);

  const inclusions = getTextList(
    listing.inclusions || listing.included
  );

  const exclusions = getTextList(
    listing.exclusions || listing.notIncluded
  );

  const information = getTextList(
    listing.importantInformation || listing.beforeYouBook
  );

  const selectedPackage =
    options.find((option) => option.id === selectedOption) ||
    options[0];

  const actualPrice = selectedPackage?.price;

  const canShowPrice =
    Number.isFinite(Number(actualPrice)) &&
    Number(actualPrice) > 0;

  const minimumDate = new Date().toISOString().split("T")[0];

  function addToCart() {
    if (!travelDate) {
      document.getElementById("ed-travel-date")?.focus();
      return;
    }

    const cartItem = {
      cartId: `${listing.id}-${Date.now()}`,
      listingId: String(listing.id),
      name,
      destination: location,
      productType: listing.productType,
      packageName:
        selectedPackage?.name || "Standard experience",
      date: travelDate,
      quantity,
      people: quantity,
      unitPrice: canShowPrice ? Number(actualPrice) : null,
      pricingStatus: canShowPrice ? "listed" : "quote-required",
      image: images[0] || "",
    };

    addCartItem(cartItem);

    setAdded(true);
  }

  return (
    <div className="ed-page">
      <Navbar />

      <main className="ed-main">
        <div className="ed-container">
          <nav className="ed-breadcrumb">
            <Link to="/">Home</Link>
            <span>/</span>
            <Link to="/tours-experiences">
              Tours & experiences
            </Link>
            <span>/</span>
            <span>{name}</span>
          </nav>

          <header className="ed-heading">
            <div>
              <span className="ed-kicker">
                {listing.productType === "package"
                  ? "NATURE HOLIDAYS TOUR PACKAGE"
                  : "DISCOVER SOMETHING EXTRAORDINARY"}
              </span>

              <h1>{name}</h1>

              <p className="ed-location">
                <span>⌖</span> {location}
                {listing.state ? `, ${listing.state}` : ""}
              </p>
            </div>

            <span className="ed-duration">
              ◷ {getDuration(listing)}
            </span>
          </header>

          <section className="ed-gallery">
            <div className="ed-gallery-main">
              {images.length ? (
                <img
                  src={images[activeImage]}
                  alt={`${name} — photo ${activeImage + 1}`}
                />
              ) : (
                <div className="ed-no-image">
                  <span>✦</span>
                  <p>Experience photography coming soon</p>
                </div>
              )}
            </div>

            <div className="ed-gallery-side">
              {images.slice(1, 3).map((image, index) => (
                <button
                  key={image}
                  type="button"
                  onClick={() => setActiveImage(index + 1)}
                  className={
                    activeImage === index + 1 ? "active" : ""
                  }
                  aria-label={`View photo ${index + 2}`}
                >
                  <img
                    src={image}
                    alt={`${name} view ${index + 2}`}
                  />
                </button>
              ))}

              {!images.length && (
                <div className="ed-gallery-empty">
                  More photographs will be added soon.
                </div>
              )}

              {images.length > 3 && (
                <button
                  type="button"
                  className="ed-more-photos"
                  onClick={() =>
                    setActiveImage(
                      (activeImage + 1) % images.length
                    )
                  }
                >
                  View more photos ↗
                </button>
              )}
            </div>
          </section>

          <div className="ed-layout">
            <div className="ed-information">
              <DetailSection title="About this experience">
                <p className="ed-description">{description}</p>
              </DetailSection>

              {highlights.length > 0 && (
                <DetailSection title="What you'll experience">
                  <ul className="ed-highlight-list">
                    {(showAllHighlights
                      ? highlights
                      : highlights.slice(0, 5)
                    ).map((highlight, index) => (
                      <li key={`${index}-${highlight}`}>
                        <span>✦</span>
                        {highlight}
                      </li>
                    ))}
                  </ul>

                  {highlights.length > 5 && (
                    <button
                      className="ed-expand-button"
                      onClick={() =>
                        setShowAllHighlights((value) => !value)
                      }
                    >
                      {showAllHighlights ? "Show less" : "See more"}
                    </button>
                  )}
                </DetailSection>
              )}

              <DetailSection title="Package details">
                <div className="ed-facts">
                  <div>
                    <span>◷</span>
                    <div>
                      <strong>Duration</strong>
                      <small>{getDuration(listing)}</small>
                    </div>
                  </div>

                  <div>
                    <span>⌖</span>
                    <div>
                      <strong>Location</strong>
                      <small>{location}</small>
                    </div>
                  </div>

                  <div>
                    <span>✓</span>
                    <div>
                      <strong>Confirmation</strong>
                      <small>
                        {listing.booking?.confirmation ||
                          "Confirmed by our team after enquiry"}
                      </small>
                    </div>
                  </div>

                  <div>
                    <span>₹</span>
                    <div>
                      <strong>Pricing</strong>
                      <small>
                        {canShowPrice
                          ? "Listed starting price"
                          : "Request a quote"}
                      </small>
                    </div>
                  </div>
                </div>
              </DetailSection>

              <DetailSection title="Itinerary">
                {itinerary.length ? (
                  <div className="ed-itinerary">
                    {itinerary.map((step, index) => (
                      <div
                        className="ed-itinerary-step"
                        key={index}
                      >
                        <span className="ed-step-number">
                          {String(index + 1).padStart(2, "0")}
                        </span>
                        <p>{step}</p>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="ed-muted">
                    The detailed itinerary, timings and activity
                    sequence will be shared by our team after your
                    enquiry.
                  </p>
                )}
              </DetailSection>

              <div className="ed-two-columns">
                <DetailSection title="What's included">
                  {inclusions.length ? (
                    <ul className="ed-check-list">
                      {inclusions.map((item, index) => (
                        <li key={index}>
                          <span>✓</span>
                          {item}
                        </li>
                      ))}
                    </ul>
                  ) : (
                    <p className="ed-muted">
                      Inclusions will be confirmed with your
                      selected package.
                    </p>
                  )}
                </DetailSection>

                <DetailSection title="What's not included">
                  {exclusions.length ? (
                    <ul className="ed-cross-list">
                      {exclusions.map((item, index) => (
                        <li key={index}>
                          <span>−</span>
                          {item}
                        </li>
                      ))}
                    </ul>
                  ) : (
                    <p className="ed-muted">
                      Any exclusions will be clarified before
                      confirmation.
                    </p>
                  )}
                </DetailSection>
              </div>

              <DetailSection title="Before you book">
                {information.length ? (
                  <ul className="ed-bullet-list">
                    {information.map((item, index) => (
                      <li key={index}>{item}</li>
                    ))}
                  </ul>
                ) : (
                  <p className="ed-muted">
                    Please contact Nature Holidays to confirm
                    eligibility, accessibility, meeting-point
                    details, weather conditions and cancellation
                    terms before booking.
                  </p>
                )}
              </DetailSection>

              {listing.cancellationPolicy && (
                <DetailSection title="Cancellation policy">
                  <p className="ed-description">
                    {typeof listing.cancellationPolicy === "string"
                      ? listing.cancellationPolicy
                      : listing.cancellationPolicy.description ||
                        "Cancellation terms will be shared during enquiry."}
                  </p>
                </DetailSection>
              )}
            </div>

            <aside className="ed-booking-card">
              <div className="ed-booking-top">
                <span>MAKE IT YOURS</span>
                <h2>Choose your experience</h2>
                <p>
                  Select your preferred date and package details.
                </p>
              </div>

              <label className="ed-field">
                <span>Travel date *</span>
                <input
                  id="ed-travel-date"
                  type="date"
                  min={minimumDate}
                  value={travelDate}
                  onChange={(event) => {
                    setTravelDate(event.target.value);
                    setAdded(false);
                  }}
                />
              </label>

              <label className="ed-field">
                <span>Package option</span>
                <select
                  value={selectedOption}
                  onChange={(event) => {
                    setSelectedOption(event.target.value);
                    setAdded(false);
                  }}
                >
                  {options.map((option) => (
                    <option key={option.id} value={option.id}>
                      {option.name}
                    </option>
                  ))}
                </select>
              </label>

              {selectedPackage?.description && (
                <p className="ed-option-description">
                  {selectedPackage.description}
                </p>
              )}

              <div className="ed-quantity-row">
                <div>
                  <strong>Travellers</strong>
                  <small>Number of people</small>
                </div>

                <div className="ed-quantity-control">
                  <button
                    type="button"
                    onClick={() => {
                      setQuantity((value) =>
                        Math.max(1, value - 1)
                      );
                      setAdded(false);
                    }}
                    aria-label="Decrease number of travellers"
                    disabled={quantity <= 1}
                  >
                    −
                  </button>

                  <strong>{quantity}</strong>

                  <button
                    type="button"
                    onClick={() => {
                      setQuantity((value) =>
                        Math.min(20, value + 1)
                      );
                      setAdded(false);
                    }}
                    aria-label="Increase number of travellers"
                    disabled={quantity >= 20}
                  >
                    +
                  </button>
                </div>
              </div>

              <div className="ed-price-block">
                <span>
                  {canShowPrice ? "Price per person" : "Pricing"}
                </span>

                <strong>
                  {canShowPrice
                    ? formatINR(Number(actualPrice) * quantity)
                    : "Enquire for price"}
                </strong>

                <small>
                  {canShowPrice
                    ? "Estimated total based on the listed per-person price. Final pricing is subject to operator confirmation."
                    : "The final price will be provided by our team."}
                </small>
              </div>

              <button
                type="button"
                className="ed-add-button"
                onClick={addToCart}
              >
                Add to cart <span>→</span>
              </button>

              {added && (
                <div className="ed-added-message">
                  <strong>Added to your cart.</strong>
                  <button
                    type="button"
                    onClick={() => navigate("/cart")}
                  >
                    View cart →
                  </button>
                </div>
              )}

              <p className="ed-booking-note">
                Adding to cart does not confirm a reservation or
                charge a payment. Our team will verify availability
                and pricing.
              </p>

              <Link to="/contact" className="ed-help-link">
                Need help planning? Talk to us ↗
              </Link>
            </aside>
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
