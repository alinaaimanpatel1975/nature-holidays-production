
import { useState } from "react";
import { Link } from "react-router-dom";

import { addCartItem } from "../utils/cart";

import "./BrochureTours.css";

const collections = [
  {
    id: "one-day-fun-tours",
    title: "One Day Fun Tours",
    description: "Parks, resorts and recreational experiences.",
    destination: "To be confirmed",
    tours: [
      "Wonderla Parks & Resort",
      "GRS Fantasy Park",
      "Snow City",
      "Fun World",
      "Jollywood Studios",
      "Elim Resort",
      "Guhantara Resort",
      "Dharamshi Resort",
      "Greenwood Resort",
      "Dreamland Resort",
      "Avaniya Adventures Resort",
    ],
  },
  {
    id: "one-day-historical-fun-tours",
    title: "One Day Historical & Fun Tours",
    description: "Heritage visits, sightseeing and day outings.",
    destination: "Karnataka & nearby destinations",
    tours: [
      "Bluff, Talakadu, Somanathpura",
      "Nandi Hills and Isha Temple",
      "Talakadu, Mysore, KRS Garden",
      "Shravanabelagola, Belur, Halebidu",
      "Mysore, KRS Garden",
      "Wonderla & Big Banyan Tree",
      "Chitradurga",
      "Nandi Hills and Airport",
      "GRS Fantasy Park & DJ with Campfire",
      "Madikeri — Day / Night Trip",
    ],
  },
  {
    id: "namma-bengaluru-rounds",
    title: "Namma Bengaluru Rounds",
    description: "Discover Bengaluru's landmarks, gardens and attractions.",
    destination: "Bengaluru",
    isCityCollection: true,
    tours: [
      "Lalbagh Botanical Garden",
      "Bangalore Palace",
      "National Park",
      "Butterfly Park",
      "Government Aquarium",
      "Vidhana Soudha",
      "High Court",
      "Cubbon Park",
      "Meenakshi Temple",
      "Bal Bhavan",
      "HAL Museum",
      "Visvesvaraya Museum",
      "Shiva Temple",
      "Toy Train",
      "Tipu Palace",
      "Nehru Planetarium",
      "ISKCON Temple",
      "Indira Gandhi Musical Fountain",
    ],
  },
  {
    id: "north-india-tours",
    title: "Explore North India Tours",
    description: "Explore destinations and journeys across North India.",
    destination: "North India",
    tours: [
      "Chalo Dilli",
      "Bagh e Jannat, Kashmir",
      "Raj se Taj Tak",
      "Husn e Himachal",
      "Versatile Gujarat",
      "Punjab with Delhi",
      "Delhi with Lucknow",
      "Gujarat & Diu",
    ],
  },
  {
    id: "south-india-tours",
    title: "Explore South India Tours",
    description: "Explore the landscapes, heritage and culture of South India.",
    destination: "South India",
    tours: [
      "Mangaluru, Sakleshpur, Belur, Halebidu",
      "Hampi, Chitradurga",
      "Madikeri, Mysore",
      "Mysore & Somanathpura",
      "Udupi & Mangaluru",
      "Bijapur, Aihole",
      "Wayanad",
      "Dandeli",
      "Udupi, Horanadu, Shringeri, Dharmasthala",
      "Hampi, Badami, Aihole, Pattadakallu",
      "Hyderabad with Wonderla Trip",
      "Ooty & Black Thunder",
      "Mysore, Ooty",
      "Chennai, Mahabalipuram, Golden Temple",
      "Kodaikanal, Kutralam, Kanyakumari",
      "Pondicherry, Mahabalipuram",
      "Mangaluru, Udupi and Murudeshwara",
      "Munnar, Alleppey, Kochi",
      "Goa, Dandeli",
      "Mumbai, Pune with Adlabs Imagica",
    ],
  },
];

function createSlug(value) {
  return value
    .toLowerCase()
    .replace(/&/g, "and")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
}

function TourCard({ tour, collection }) {
  const [travelDate, setTravelDate] = useState("");
  const [quantity, setQuantity] = useState(0);
  const [added, setAdded] = useState(false);

  const listingId = `brochure-${createSlug(tour)}`;

  const minimumDate = (() => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
  })();

  function handleAddToCart() {
    addCartItem({
      cartId: `${listingId}-${Date.now()}`,
      listingId,
      name: tour,
      destination: collection.destination,
      productType: "package",
      packageName: collection.title,
      date: travelDate,
      quantity,
      people: quantity,
      unitPrice: null,
      pricingStatus: "quote-required",
      image: "",
    });

    setAdded(true);
  }

  return (
    <details className="brochure-tour-detail">
      <summary>
        <span>{tour}</span>
        <span className="brochure-tour-toggle" aria-hidden="true">
          +
        </span>
      </summary>

      <div className="brochure-tour-information">
        <span className="brochure-tour-type">
          {collection.title.toUpperCase()}
        </span>

        <h3>{tour}</h3>

        <p className="brochure-tour-introduction">
          Explore this Nature Holidays tour option. Our team can
          help you plan the experience, confirm the route and
          share the available travel arrangements.
        </p>

        <dl className="brochure-tour-facts">
          <div>
            <dt>Tour category</dt>
            <dd>{collection.title}</dd>
          </div>

          <div>
            <dt>Destination</dt>
            <dd>{collection.destination}</dd>
          </div>

          <div>
            <dt>Duration</dt>
            <dd>To be confirmed</dd>
          </div>

          <div>
            <dt>Pricing</dt>
            <dd>Custom quote required</dd>
          </div>
        </dl>

        <div className="brochure-tour-confirmation">
          <h4>Plan your visit</h4>
          <p>
            Detailed itinerary, departure timings, transport,
            inclusions, accommodation (where applicable) and
            final pricing will be confirmed by Nature Holidays.
          </p>
        </div>

        <div className="brochure-tour-booking-controls">
          <label className="brochure-tour-date">
            <span>Preferred travel date (optional)</span>
            <input
              type="date"
              min={minimumDate}
              value={travelDate}
              onChange={(event) => {
                setTravelDate(event.target.value);
                setAdded(false);
              }}
            />
          </label>

          <div className="brochure-tour-quantity">
            <span>Travellers</span>

            <div>
              <button
                type="button"
                onClick={() => {
                  setQuantity((current) => Math.max(1, current - 1));
                  setAdded(false);
                }}
                disabled={quantity <= 1}
                aria-label={`Remove one traveller from ${tour}`}
              >
                −
              </button>

              <strong>{quantity}</strong>

              <button
                type="button"
                onClick={() => {
                  setQuantity((current) => Math.min(20, current + 1));
                  setAdded(false);
                }}
                disabled={quantity >= 20}
                aria-label={`Add one traveller to ${tour}`}
              >
                +
              </button>
            </div>
          </div>
        </div>

        <div className="brochure-tour-actions">
          <button
            type="button"
            className="brochure-tour-enquire"
            disabled={quantity === 0}
            onClick={handleAddToCart}
          >
            Add to cart <span>→</span>
          </button>

          {added && (
            <div className="brochure-tour-added" role="status">
              <span>Added to your trip collection.</span>
              <Link to="/cart">View cart →</Link>
            </div>
          )}
        </div>

        <p className="brochure-tour-enquiry-note">
          Saving this tour does not confirm a booking or payment.
          Availability and final pricing are subject to confirmation.
        </p>
      </div>
    </details>
  );
}

export default function BrochureTours() {
  const totalTours = collections.reduce(
    (total, collection) => total + collection.tours.length,
    0
  );

  return (
    <section
      className="brochure-tours"
      aria-labelledby="brochure-tours-title"
    >
      <div className="brochure-tours-heading">
        <span className="brochure-eyebrow">
          THE NATURE HOLIDAYS COLLECTION
        </span>

        <h2 id="brochure-tours-title">
          More ways to explore.
        </h2>

        <p>
          From one-day adventures to journeys across India,
          discover the tours available in our brochure.
          Select the experiences you would like to enquire about.
        </p>
      </div>

      <div className="brochure-collections">
        {collections.map((collection, index) => (
          <details
            className="brochure-collection"
            id={collection.id}
            key={collection.id}
          >
            <summary>
              <span className="brochure-collection-number">
                {String(index + 1).padStart(2, "0")}
              </span>

              <span className="brochure-collection-heading">
                <span className="brochure-collection-title">
                  {collection.title}
                </span>

                <span className="brochure-collection-description">
                  {collection.description}
                </span>
              </span>

              <span className="brochure-collection-count">
                {collection.tours.length}{" "}
                {collection.isCityCollection ? "stops" : "tours"}
              </span>

              <span
                className="brochure-expand-icon"
                aria-hidden="true"
              />
            </summary>

            <div className="brochure-collection-content">
              <p className="brochure-collection-note">
                Select a tour to explore its details and add it to
                your trip collection.
              </p>

              <div className="brochure-tour-detail-list">
                {collection.tours.map((tour) => (
                  <TourCard
                    key={tour}
                    tour={tour}
                    collection={collection}
                  />
                ))}
              </div>
            </div>
          </details>
        ))}
      </div>

      <p className="brochure-enquiry-note">
        Showing {totalTours} tour options from the Nature Holidays
        brochure. Routes, dates, availability and prices are
        confirmed individually with the team.
      </p>
    </section>
  );
}
