
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import Navbar from "../components/Navbar";
import { destinations as localDestinations } from "../data/destinations";
import Footer from "../components/Footer";
import "./DestinationsPage.css";

const API_BASE = "http://localhost/nature-holidays-php/public";
const API_URL = `${API_BASE}/api/destinations`;

export function DestinationNavigation() {
  return <Navbar />;
}

export default function DestinationsPage() {
  const [destinations, setDestinations] = useState(localDestinations);
  const [query, setQuery] = useState("");
  const [selectedState, setSelectedState] = useState("All states");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let cancelled = false;

    async function loadDestinations() {
      try {
        const response = await fetch(API_URL);

        if (!response.ok) {
          throw new Error("Could not load destinations.");
        }

        const result = await response.json();

        if (!result.success || !Array.isArray(result.data)) {
          throw new Error("Invalid destinations response.");
        }

        const places = result.data.map((place) => {
          const localPlace = localDestinations.find(
            (item) =>
              (place.slug && item.slug === place.slug) ||
              item.name.toLowerCase() === place.name.toLowerCase()
          );

          const slug =
            place.slug ||
            place.name
              .toLowerCase()
              .trim()
              .replace(/[^a-z0-9]+/g, "-")
              .replace(/^-|-$/g, "");

          const apiImage = place.image
  ? place.image.startsWith("http")
    ? place.image
    : place.image.startsWith("/images/")
      ? `${import.meta.env.BASE_URL}${place.image.slice(1)}`
      : `${API_BASE}${place.image.startsWith("/") ? "" : "/"}${place.image}`
  : "";

const image =
  localPlace?.image ||
  apiImage ||
  (place.slug === "nandi-hills" || place.slug === "isha-yoga-center"
    ? `${import.meta.env.BASE_URL}images/package-nandi-hills.jpg`
    : place.slug === "pattadakal"
      ? `${import.meta.env.BASE_URL}images/tour-hampi.jpg`
      : "");

          return {
            ...localPlace,
            ...place,
            slug,
            image,
            theme:
              place.description ||
              localPlace?.theme ||
              place.state ||
              place.country ||
              "Explore India",
          };
        });

        if (!cancelled) {
  const apiNames = new Set(
    places.map((place) => place.name.trim().toLowerCase())
  );

  const mergedPlaces = [
    ...places,
    ...localDestinations.filter(
      (place) => !apiNames.has(place.name.trim().toLowerCase())
    ),
  ];

  setDestinations(mergedPlaces);
  setError("");
}
      } catch (err) {
        if (!cancelled) {
          setError(
            "Couldn't connect to the destinations server. Showing saved destinations instead."
          );
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    loadDestinations();

    return () => {
      cancelled = true;
    };
  }, []);

  const states = [
    "All states",
    ...new Set(
      destinations.map((place) => place.state).filter(Boolean)
    ),
  ];

  const visibleDestinations = destinations.filter((place) => {
    const matchesState =
      selectedState === "All states" ||
      place.state === selectedState;

    const text =
      `${place.name} ${place.state || ""} ${place.theme || ""}`.toLowerCase();

    return matchesState && text.includes(query.trim().toLowerCase());
  });

  return (
    <>
      <DestinationNavigation />

      <main className="destination-directory">
        <nav className="destination-breadcrumb" aria-label="Breadcrumb">
          <Link to="/">Home</Link>
          <span aria-hidden="true">/</span>
          <span aria-current="page">Destinations</span>
        </nav>

        <div className="destination-directory-heading">
          <span className="destination-eyebrow-label">
            YOUR NEXT STOP STARTS HERE
          </span>

          <h1>Where will you go next?</h1>

          <p>
            Discover famous sights, local highlights and ideas
            for your next trip across India.
          </p>
        </div>

        <div className="destination-directory-filters">
          <label className="destination-search-box">
            <span>Search destinations</span>
            <input
              type="search"
              placeholder="Try Mysore, Kerala or beaches"
              value={query}
              onChange={(event) => setQuery(event.target.value)}
            />
          </label>

          <label className="destination-state-filter">
            <span>Filter by state</span>
            <select
              value={selectedState}
              onChange={(event) => setSelectedState(event.target.value)}
            >
              {states.map((state) => (
                <option key={state} value={state}>
                  {state}
                </option>
              ))}
            </select>
          </label>
        </div>

        {error && (
          <p role="alert">{error}</p>
        )}

        <p className="destination-result-count" role="status">
          {loading
            ? "Loading destinations..."
            : `${visibleDestinations.length} destinations to explore`}
        </p>

        <div className="destination-directory-grid">
          {visibleDestinations.map((place) => (
            <article
              className="destination-directory-card"
              key={place.id ?? place.slug}
            >
              <Link
                className="destination-card-link"
                to={`/destinations/${place.slug}`}
              >
                <div className="destination-directory-photo">
                  {place.image && (
                    <img
                      src={place.image}
                      alt={place.name}
                      loading="lazy"
                      decoding="async"
                    />
                  )}

                  <span className="destination-state-badge">
                    {place.state || place.country}
                  </span>
                </div>

                <div className="destination-directory-card-body">
                  <div>
                    <h2>{place.name}</h2>
                    <span aria-hidden="true">↗</span>
                  </div>
                  <p>{place.theme}</p>
                </div>
              </Link>
            </article>
          ))}
        </div>

        {!loading && visibleDestinations.length === 0 && (
          <div className="destination-no-results">
            <h2>No matching destinations.</h2>
            <p>Try another name or choose a different state.</p>
            <button
              type="button"
              onClick={() => {
                setQuery("");
                setSelectedState("All states");
              }}
            >
              Clear filters
            </button>
          </div>
        )}

        <a className="destination-credit-link" href="/image-credits.html">
          Photography credits
        </a>
      </main>

      <Footer />
    </>
  );
}
