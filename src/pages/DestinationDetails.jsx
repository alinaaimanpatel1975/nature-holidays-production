
import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";

import { destinations as localDestinations } from "../data/destinations";
import { DestinationNavigation } from "./DestinationsPage";
import Footer from "../components/Footer";

import "./DestinationDetails.css";

const API_BASE = "http://localhost/nature-holidays-php/public";
const API_URL = `${API_BASE}/api/destinations`;

function makeSlug(name = "") {
  return name
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
}

function getImageUrl(image) {
  if (!image) return "";

  if (/^https?:\/\//i.test(image)) return image;

  if (image.startsWith("/images/")) {
    return `${import.meta.env.BASE_URL}${image.slice(1)}`;
  }

  if (image.startsWith("/uploads/")) {
    return `${API_BASE}${image}`;
  }

  if (image.startsWith("/")) {
    return `${API_BASE}${image}`;
  }

  return `${import.meta.env.BASE_URL}${image}`;
}

function getMapLink(attraction, destination) {
  if (attraction.map_url) return attraction.map_url;

  const search = `${attraction.name || attraction}, ${destination}, India`;

  return (
    "https://www.google.com/maps/search/?api=1&query=" +
    encodeURIComponent(search)
  );
}

export default function DestinationDetails() {
  const { slug } = useParams();

  const [destination, setDestination] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    async function loadDestination() {
      const localPlace = localDestinations.find(
        (place) => place.slug === slug
      );

      try {
        const response = await fetch(API_URL);

        if (!response.ok) {
          throw new Error("Could not load destinations.");
        }

        const result = await response.json();

        if (!result.success || !Array.isArray(result.data)) {
          throw new Error("Invalid destinations response.");
        }

        const apiPlace = result.data.find(
          (place) => (place.slug || makeSlug(place.name)) === slug
        );

        if (!apiPlace && !localPlace) {
          if (!cancelled) setDestination(null);
          return;
        }

        const merged = {
          ...(localPlace || {}),
          ...(apiPlace || {}),
        };

        const attractionSource =
  Array.isArray(apiPlace?.attractions) && apiPlace.attractions.length > 0
    ? apiPlace.attractions
    : localPlace?.attractions || [];

const activitySource =
  Array.isArray(apiPlace?.activities) && apiPlace.activities.length > 0
    ? apiPlace.activities
    : localPlace?.experiences || [];

const attractions = attractionSource.map((item) => ({
  ...item,
  name: item.name || item.title,
  image: getImageUrl(item.image),
  alt: item.image_alt || item.alt || item.name || item.title,
  author: item.photo_author || item.author || "",
  creditUrl: item.credit_url || item.creditUrl || "",
}));

const activities = activitySource.map((item) => ({
  ...item,
  title: item.title || item.name,
  description:
    item.description ||
    item.overview ||
    `Explore this experience in ${localPlace?.name || apiPlace?.name}.`,
}));

        if (!cancelled) {
          setDestination({
            ...merged,

            slug: apiPlace?.slug || localPlace?.slug || slug,

            image: localPlace?.image || getImageUrl(apiPlace?.image) || "",

            state: apiPlace?.state || localPlace?.state || "",

            theme:
              apiPlace?.subtitle ||
              localPlace?.theme ||
              apiPlace?.description ||
              apiPlace?.state ||
              apiPlace?.country ||
              "Explore India",

            introduction:
              apiPlace?.description ||
              localPlace?.introduction ||
              `Discover the highlights and experiences of ${merged.name}.`,

            subtitle:
              apiPlace?.subtitle ||
              localPlace?.theme ||
              "",

            about:
              apiPlace?.about ||
              localPlace?.introduction ||
              `Discover ${merged.name} and start planning your next trip with Nature Holidays.`,

            attractions: attractions.map((item) => ({
              ...item,
              image: getImageUrl(item.image),
              alt: item.image_alt || item.name,
              author: item.photo_author || "",
              creditUrl: item.credit_url || "",
            })),

            activities,

            planning: apiPlace?.planning || "",
            sourceUrl: apiPlace?.source_url || "",
            sourceName: apiPlace?.source_name || "",
          });
        }
      } catch (error) {
        console.error("Destination loading error:", error);

        if (!cancelled) {
          setDestination(localPlace || null);
        }
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    setLoading(true);
    loadDestination();

    return () => {
      cancelled = true;
    };
  }, [slug]);

  if (loading && !destination) {
    return (
      <>
        <DestinationNavigation />
        <main className="destination-guide-missing">
          <h1>Loading destination...</h1>
        </main>
        <Footer />
      </>
    );
  }

  if (!destination) {
    return (
      <>
        <DestinationNavigation />
        <main className="destination-guide-missing">
          <h1>Destination not found.</h1>
          <p>Let's find somewhere else to explore.</p>
          <Link to="/destinations">Browse destinations ↗</Link>
        </main>
        <Footer />
      </>
    );
  }

  const attractions = destination.attractions || [];
  const activities = destination.activities || [];

  return (
    <>
      <DestinationNavigation />

      <main className="destination-guide">
        <div className="destination-guide-top">
          <nav className="destination-breadcrumb" aria-label="Breadcrumb">
            <Link to="/">Home</Link>
            <span aria-hidden="true">/</span>
            <Link to="/destinations">Destinations</Link>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{destination.name}</span>
          </nav>
        </div>

        <section
          className="destination-guide-hero"
          aria-labelledby="destination-guide-title"
        >
          {destination.image && (
            <img src={destination.image} alt={destination.name} />
          )}

          <div className="destination-guide-hero-shade" />

          <div className="destination-guide-hero-content">
            <span>{destination.state || destination.country}</span>

            <h1 id="destination-guide-title">
              Explore {destination.name}.
            </h1>

            <p>{destination.subtitle || destination.theme}</p>

            <a href="#famous-places">
              Discover the highlights
              <span aria-hidden="true">↓</span>
            </a>
          </div>

          {destination.imageCredit && (
            <a
              className="destination-hero-credit"
              href={destination.imageCredit}
            >
              Photo credit
            </a>
          )}
        </section>

        <section
          className="destination-snapshot"
          aria-label={`${destination.name} at a glance`}
        >
          <div className="destination-snapshot-inner">
            <div className="destination-snapshot-item">
              <span>STATE / REGION</span>
              <strong>{destination.state || destination.country}</strong>
            </div>

            <div className="destination-snapshot-item">
              <span>FEATURED PLACES</span>
              <strong>{attractions.length}</strong>
            </div>

            <div className="destination-snapshot-item">
              <span>EXPERIENCE IDEAS</span>
              <strong>{activities.length}</strong>
            </div>
          </div>
        </section>

        <div className="destination-guide-body">
          <nav
            className="destination-guide-jump-links"
            aria-label="On this destination page"
          >
            <a href="#about-destination">Overview</a>
            <a href="#famous-places">Famous places</a>
            <a href="#things-to-do">Things to do</a>
            <a href="#plan-your-visit">Plan your visit</a>
          </nav>

          <section
            className="destination-guide-introduction"
            id="about-destination"
          >
            <span className="guide-section-eyebrow">
              GET TO KNOW THE DESTINATION
            </span>

            <h2>A little about {destination.name}.</h2>

            <p>{destination.about}</p>
          </section>

          <section
            className="destination-guide-section"
            id="famous-places"
            aria-labelledby="famous-places-title"
          >
            <div className="destination-guide-section-heading">
              <div>
                <span className="guide-section-eyebrow">
                  START WITH THE HIGHLIGHTS
                </span>

                <h2 id="famous-places-title">
                  Famous places & experiences.
                </h2>
              </div>

              <p>
                A first look at some of the highlights around{" "}
                {destination.name}.
              </p>
            </div>

            {attractions.length > 0 ? (
              <div className="destination-attractions-grid">
                {attractions.map((attraction) => (
                  <article
                    className="destination-attraction"
                    key={attraction.id || attraction.slug}
                  >
                    <div className="destination-attraction-photo">
                      {attraction.image ? (
                        <img
                          src={attraction.image}
                          alt={attraction.alt || attraction.name}
                          loading="lazy"
                          decoding="async"
                        />
                      ) : (
                        <div
                          className="destination-attraction-image-placeholder"
                          role="img"
                          aria-label={`Image coming soon for ${attraction.name}`}
                        >
                          {attraction.name}
                        </div>
                      )}

                      {attraction.creditUrl && (
                        <a
                          className="destination-attraction-credit"
                          href={attraction.creditUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          aria-label={`Photo credit for ${attraction.name}`}
                        >
                          Photo credit
                        </a>
                      )}
                    </div>

                    <div className="destination-attraction-content">
                      <h3>{attraction.name}</h3>

                      <p>
                        {attraction.description ||
                          `Discover ${attraction.name} in ${destination.name}.`}
                      </p>

                      <a
                        className="destination-map-link"
                        href={getMapLink(attraction, destination.name)}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        Find on map
                        <span aria-hidden="true">↗</span>
                      </a>
                    </div>
                  </article>
                ))}
              </div>
            ) : (
              <p>
                Ask our team about sightseeing highlights and local
                arrangements in {destination.name}.
              </p>
            )}
          </section>

          <section
            className="destination-guide-section"
            id="things-to-do"
            aria-labelledby="destination-activities-title"
          >
            <span className="guide-section-eyebrow">
              MAKE MORE OF YOUR VISIT
            </span>

            <h2 id="destination-activities-title">
              Things to do in {destination.name}.
            </h2>

            {activities.length > 0 ? (
              <div className="destination-activities-grid">
                {activities.map((activity, index) => (
                  <article
                    className="destination-activity"
                    key={activity.id || activity.title}
                  >
                    <span className="destination-activity-number">
                      {String(index + 1).padStart(2, "0")}
                    </span>

                    <h3>{activity.title}</h3>
                    <p>{activity.description}</p>
                  </article>
                ))}
              </div>
            ) : (
              <p>
                More experience details for {destination.name} will
                be added soon. Contact our team to discuss your trip.
              </p>
            )}

            <p className="destination-guide-small-note">
              These are travel ideas, not confirmed bookable activities.
              Ask our team about available arrangements.
            </p>
          </section>

          <section
            className="destination-visit-planning"
            id="plan-your-visit"
            aria-labelledby="destination-planning-title"
          >
            <div>
              <span className="guide-section-eyebrow">
                BEFORE YOU GO
              </span>

              <h2 id="destination-planning-title">
                Plan your visit.
              </h2>

              <p>
                {destination.planning ||
                  `Contact Nature Holidays for travel guidance and information before visiting ${destination.name}.`}
              </p>
            </div>

            {destination.sourceUrl && (
              <a
                href={destination.sourceUrl}
                target="_blank"
                rel="noopener noreferrer"
              >
                Official visitor information
                <span>{destination.sourceName || "Visitor information"} ↗</span>
              </a>
            )}
          </section>

          <section className="destination-package-banner">
            <div>
              <h2>Let's plan your {destination.name} trip.</h2>

              <p>
                Tell Nature Holidays your dates, departure city
                and group size.
              </p>
            </div>

            <div className="destination-package-banner-actions">
              {destination.packageId && (
                <Link
                  className="destination-guide-primary-button"
                  to={`/packages#${destination.packageId}`}
                >
                  Explore a related package
                  <span aria-hidden="true">↗</span>
                </Link>
              )}

              <Link
                className="destination-guide-secondary-button"
                to={`/contact?package=${encodeURIComponent(
                  `${destination.name} — Custom trip`
                )}`}
              >
                Enquire about this destination
                <span aria-hidden="true">↗</span>
              </Link>
            </div>
          </section>

          <div className="destination-guide-bottom">
            <Link to="/destinations">← All destinations</Link>
            <a href="/image-credits.html">Photography credits</a>
          </div>
        </div>
      </main>

      <Footer />
    </>
  );
}
