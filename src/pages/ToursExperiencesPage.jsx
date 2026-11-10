
import { useMemo, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

import {
  marketplacePackages,
  marketplaceExperiences,
  marketplaceDestinations,
} from "../data/marketplace";

import { destinationImages } from "../data/destination-images";

import "./ToursExperiencesPage.css";

const categories = [
  { id: "all", label: "All experiences", icon: "✦" },
  { id: "tours", label: "Tours & day trips", icon: "◉" },
  { id: "attractions", label: "Attraction tickets", icon: "⌑" },
  { id: "adventure", label: "Adventure & outdoors", icon: "⌁" },
  { id: "culture", label: "Culture & heritage", icon: "◇" },
  { id: "nature", label: "Nature & wildlife", icon: "♧" },
  { id: "water", label: "Water experiences", icon: "≈" },
  { id: "food", label: "Food experiences", icon: "♨" },
  { id: "stays", label: "Hotels & stays", icon: "⌂" },
  { id: "transport", label: "Transport", icon: "↗" },
  { id: "groups", label: "Group travel", icon: "◎" },
];

const sortOptions = [
  { value: "recommended", label: "Recommended" },
  { value: "name-asc", label: "Name: A to Z" },
  { value: "name-desc", label: "Name: Z to A" },
  { value: "price-low", label: "Price: Low to high" },
  { value: "price-high", label: "Price: High to low" },
];

const indicativePrices = {
  attractions: 500,
  tours: 1500,
  adventure: 1800,
  culture: 1200,
  nature: 1200,
  water: 1800,
  food: 1400,
  stays: 2500,
  transport: 800,
  groups: 1800,
};

/*
  Matching service photography.
  These are remote Unsplash image URLs, so no local image
  files need to be created or imported.
*/

const serviceImages = {
  stays:
    "https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=85",

  transport:
    "https://images.unsplash.com/photo-1570125909232-eb263c188f7e?auto=format&fit=crop&w=1200&q=85",

  groups:
    "https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=85",
};

/*
  Service listings are enquiry-based.
  They do not represent confirmed hotel inventory,
  vehicle availability or fixed prices.
*/

const serviceListings = [
  // HOTELS & STAYS
  {
    id: "service-hotel-stays",
    name: "Hotels & Comfortable Stays",
    category: "stays",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.stays,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Find suitable hotels and comfortable accommodation for your holiday, weekend escape or multi-day tour.",
    overview:
      "Enquire about accommodation options based on your destination, group size and travel dates.",
    duration: "Based on your trip",
    highlights: ["Hotels", "Family-friendly stays", "Multi-day accommodation"],
  },
  {
    id: "service-resorts",
    name: "Resorts & Nature Retreats",
    category: "stays",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.stays,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Explore resort and nature-retreat accommodation for relaxing holidays and special getaways.",
    overview:
      "Tell us your preferred destination, dates and requirements to enquire about suitable stays.",
    duration: "Based on your trip",
    highlights: ["Resorts", "Nature retreats", "Leisure getaways"],
  },
  {
    id: "service-group-accommodation",
    name: "Group Accommodation",
    category: "stays",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.stays,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Accommodation planning for school trips, college tours, corporate outings and larger travel groups.",
    overview:
      "Enquire about accommodation arrangements suited to your group size and itinerary.",
    duration: "Based on your trip",
    highlights: ["Group stays", "School and college groups", "Corporate groups"],
  },

  // TRANSPORT
  {
    id: "service-private-transfers",
    name: "Private Cab & Transfers",
    category: "transport",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.transport,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Enquire about private vehicles for airport transfers, sightseeing and point-to-point journeys.",
    overview:
      "Share your route, dates and passenger count to enquire about suitable private transport.",
    duration: "Route dependent",
    highlights: ["Private cabs", "Airport transfers", "Local sightseeing"],
  },
  {
    id: "service-tempo-traveller",
    name: "Tempo Traveller",
    category: "transport",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.transport,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Explore group-vehicle arrangements for family holidays, educational trips and organised tours.",
    overview:
      "Enquire about vehicle arrangements based on your group size, route and travel dates.",
    duration: "Route dependent",
    highlights: ["Group transportation", "Multi-day journeys", "Custom routes"],
  },
  {
    id: "service-tourist-bus",
    name: "Tourist Bus & Group Transfers",
    category: "transport",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.transport,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Transport planning for larger groups travelling together across destinations.",
    overview:
      "Tell us your passenger count and itinerary to enquire about group transport.",
    duration: "Route dependent",
    highlights: ["Large groups", "Educational tours", "Corporate travel"],
  },
  {
    id: "service-travel-assistance",
    name: "Travel & Ticket Assistance",
    category: "transport",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.transport,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Get assistance planning your journey and enquiring about travel ticket arrangements.",
    overview:
      "Share your travel route and dates with Nature Holidays to discuss possible arrangements.",
    duration: "Based on your journey",
    highlights: ["Journey planning", "Ticket assistance", "Travel coordination"],
  },

  // GROUP TRAVEL
  {
    id: "service-school-trips",
    name: "School Educational Trips",
    category: "groups",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.groups,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Plan educational excursions and memorable learning experiences for school students.",
    overview:
      "Enquire about destinations, itineraries, accommodation and transport for your school group.",
    duration: "Custom itinerary",
    highlights: ["Educational excursions", "Sightseeing", "Group coordination"],
  },
  {
    id: "service-college-tours",
    name: "College Tours & Student Trips",
    category: "groups",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.groups,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Organise college outings, student tours and multi-day trips with your classmates.",
    overview:
      "Discuss your preferred destination, group size, dates and travel requirements with us.",
    duration: "Custom itinerary",
    highlights: ["College outings", "Student group tours", "Multi-day trips"],
  },
  {
    id: "service-corporate-travel",
    name: "Corporate Outings & Team Trips",
    category: "groups",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.groups,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Plan team outings, corporate getaways and group travel experiences.",
    overview:
      "Enquire about customised itineraries, transport and accommodation for your organisation.",
    duration: "Custom itinerary",
    highlights: ["Team outings", "Corporate getaways", "Group arrangements"],
  },
  {
    id: "service-custom-group-tours",
    name: "Customised Group Holidays",
    category: "groups",
    productType: "experience",
    listingKind: "service",
    image: serviceImages.groups,
    location: "Across India",
    destination: "Across India",
    state: "India",
    description:
      "Bring your family, friends or travel group together for a customised holiday.",
    overview:
      "Tell us where you want to go and what your group needs. We can discuss a suitable itinerary.",
    duration: "Custom itinerary",
    highlights: ["Family groups", "Friends and communities", "Custom itineraries"],
  },
];

const formatINR = (amount) =>
  new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(amount);

function getName(item) {
  return item.name || item.title || "Explore this experience";
}

function getLocation(item) {
  return (
    item.location ||
    item.destination ||
    item.city ||
    item.state ||
    "India"
  );
}

function getDescription(item) {
  return (
    item.overview ||
    item.description ||
    item.summary ||
    "Discover more about this experience with Nature Holidays."
  );
}

function getDuration(item) {
  const duration = item.duration;

  if (!duration) return "Duration varies";

  if (typeof duration === "string" || typeof duration === "number") {
    return String(duration);
  }

  if (typeof duration === "object") {
    if (duration.label) return duration.label;
    if (duration.hours) return `Approx. ${duration.hours} hours`;

    if (duration.days && duration.nights !== undefined) {
      return `${duration.days} days · ${duration.nights} nights`;
    }

    if (duration.days) return `${duration.days} days`;
    if (duration.nights !== undefined) return `${duration.nights} nights`;
  }

  return "Duration varies";
}

function normaliseCategory(value) {
  const category = String(value || "").toLowerCase().trim();

  const aliases = {
    attraction: "attractions",
    ticket: "attractions",
    sightseeing: "attractions",
    tour: "tours",
    "day-trip": "tours",
    "day trip": "tours",
    outdoor: "adventure",
    wildlife: "nature",
    heritage: "culture",
    cruise: "water",
    boating: "water",
    culinary: "food",
    dining: "food",
    accommodation: "stays",
    hotel: "stays",
    transfer: "transport",
    transfers: "transport",
    educational: "groups",
    group: "groups",
  };

  return aliases[category] || category;
}

function getCategory(item) {
  const directCategory = normaliseCategory(item.category);

  if (categories.some((category) => category.id === directCategory)) {
    return directCategory;
  }

  const text = [
    item.type,
    item.name,
    item.title,
    item.overview,
    item.description,
  ]
    .filter(Boolean)
    .join(" ")
    .toLowerCase();

  if (/food|cuisine|tasting|restaurant|culinary|food walk/.test(text)) return "food";
  if (/hotel|resort|stay|accommodation|homestay/.test(text)) return "stays";
  if (/transfer|airport|cab|taxi|car rental|bus/.test(text)) return "transport";
  if (/school|college|corporate|group travel/.test(text)) return "groups";
  if (/rafting|trek|camping|paragliding|adventure|safari/.test(text)) return "adventure";
  if (/cruise|boating|beach|waterfall|kayak/.test(text)) return "water";
  if (/forest|garden|wildlife|nature|national park/.test(text)) return "nature";
  if (/heritage|historical|temple|palace|culture|fort/.test(text)) return "culture";
  if (/ticket|museum|attraction|entry/.test(text)) return "attractions";

  return "tours";
}

function findDestination(item, destinations) {
  return destinations.find(
    (destination) =>
      destination.slug === item.destinationSlug ||
      destination.name?.toLowerCase() ===
        String(item.destination || "").toLowerCase()
  );
}

function getImage(item, destinations) {
  const directImage =
    item.image ||
    item.coverImage ||
    item.thumbnail ||
    item.images?.[0];

  if (directImage) return directImage;

  const destination = findDestination(item, destinations);

  if (destination?.image) return destination.image;

  const destinationName = (
    item.destination ||
    item.name ||
    ""
  ).toLowerCase();

  const matchingPhoto = destinationImages.find((photo) => {
    const photoDestination = photo.destination?.toLowerCase() || "";

    return (
      photoDestination === destinationName ||
      photo.id === item.heroId
    );
  });

  if (matchingPhoto?.image) return matchingPhoto.image;

  const packageText = `${item.name || ""} ${item.title || ""}`.toLowerCase();

  const packagePhoto = destinationImages.find((photo) =>
    packageText.includes(photo.destination?.toLowerCase() || "___")
  );

  return packagePhoto?.image || "";
}

function getPrice(item) {
  const suppliedPrice =
    item.pricing?.startingPrice ??
    item.startingPrice ??
    (typeof item.price === "number" ? item.price : null);

  if (Number.isFinite(Number(suppliedPrice)) && Number(suppliedPrice) > 0) {
    return {
      amount: Number(suppliedPrice),
      caption: "Listed starting price",
      estimated: false,
    };
  }

  if (item.listingKind === "service") {
    return {
      amount: null,
      caption: "Custom arrangements",
      estimated: false,
    };
  }

  if (item.productType === "package") {
    return {
      amount: null,
      caption: "Pricing",
      estimated: false,
    };
  }

  const category = getCategory(item);

  return {
    amount: indicativePrices[category] || indicativePrices.tours,
    caption: "Indicative estimate · from",
    estimated: true,
  };
}

function getListingLink(item) {
  if (item.listingKind === "service") {
    return "/contact";
  }

  return `/experiences/${encodeURIComponent(item.id)}`;
}

function ExperienceCard({ item, destinations }) {
  const image = getImage(item, destinations);
  const name = getName(item);
  const location = getLocation(item);
  const description = getDescription(item);
  const duration = getDuration(item);
  const price = getPrice(item);
  const link = getListingLink(item);

  return (
    <article className="te-card">
      <Link
        className="te-card-image"
        to={link}
        aria-label={`Explore ${name}`}
      >
        {image ? (
          <img
            src={image}
            alt={`${name} in ${location}`}
            loading="lazy"
            onError={(event) => {
              event.currentTarget.style.display = "none";
            }}
          />
        ) : (
          <div className="te-image-placeholder">
            <span>{location}</span>
          </div>
        )}

        <span className="te-card-tag">
          {item.listingKind === "service"
            ? "Travel service"
            : item.productType === "package"
              ? "Tour package"
              : "Experience"}
        </span>

        <span className="te-card-heart" aria-hidden="true">♡</span>
      </Link>

      <div className="te-card-content">
        <p className="te-card-location">
          <span aria-hidden="true">⌖</span> {location}
        </p>

        <Link to={link} className="te-card-title">
          {name}
        </Link>

        <p className="te-card-description">{description}</p>

        <div className="te-card-meta">
          <span>◷ {duration}</span>
          <span>
            {item.listingKind === "service"
              ? "Enquiry-based service"
              : item.productType === "package"
                ? "Multi-stop tour"
                : "Local experience"}
          </span>
        </div>

        <div className="te-card-bottom">
          <div>
            <span className="te-price-caption">{price.caption}</span>

            <strong className="te-price">
              {price.amount === null
                ? "Request a quote"
                : formatINR(price.amount)}
            </strong>

            {price.estimated && (
              <span
                style={{
                  display: "block",
                  marginTop: "4px",
                  color: "#7b817b",
                  fontSize: "9px",
                }}
              >
                Planning estimate, not a confirmed offer
              </span>
            )}
          </div>

          <Link
            to={link}
            className="te-card-arrow"
            aria-label={`View ${name}`}
          >
            →
          </Link>
        </div>
      </div>
    </article>
  );
}

function DestinationCard({ destination }) {
  const image =
    destination.image ||
    destinationImages.find(
      (photo) =>
        photo.destination?.toLowerCase() ===
        destination.name?.toLowerCase()
    )?.image ||
    "";

  return (
    <Link
      to={`/destinations/${destination.slug}`}
      className="te-destination-card"
    >
      {image ? (
        <img src={image} alt={destination.name} loading="lazy" />
      ) : (
        <div className="te-destination-placeholder">
          <span>{destination.name}</span>
        </div>
      )}

      <span className="te-destination-shade" />

      <span className="te-destination-info">
        <strong>{destination.name}</strong>
        <small>{destination.state || "Explore India"} <span>↗</span></small>
      </span>
    </Link>
  );
}

export default function ToursExperiencesPage() {
  const [searchParams] = useSearchParams();

  const initialCategory = categories.some(
    (category) => category.id === searchParams.get("category")
  )
    ? searchParams.get("category")
    : "all";

  const [search, setSearch] = useState("");
  const [destination, setDestination] = useState("all");
  const [activeCategory, setActiveCategory] = useState(initialCategory);
  const [sortBy, setSortBy] = useState("recommended");
  const [showFilters, setShowFilters] = useState(false);
  const [productType, setProductType] = useState("all");

  const listings = useMemo(() => {
    const combined = [
      ...marketplacePackages.map((item) => ({
        ...item,
        productType: "package",
      })),
      ...marketplaceExperiences.map((item) => ({
        ...item,
        productType: "experience",
      })),
      ...serviceListings,
    ];

    const seen = new Set();

    return combined.filter((item) => {
      if (!item.id || seen.has(item.id)) return false;
      seen.add(item.id);
      return true;
    });
  }, []);

  const destinations = useMemo(() => {
    const seen = new Set();

    return (marketplaceDestinations || []).filter((item) => {
      if (!item.slug || seen.has(item.slug)) return false;
      seen.add(item.slug);
      return true;
    });
  }, []);

  const filteredListings = useMemo(() => {
    let result = [...listings];
    const query = search.trim().toLowerCase();

    if (query) {
      result = result.filter((item) => {
        const searchable = [
          getName(item),
          getLocation(item),
          getDescription(item),
          item.state,
          item.category,
          ...(item.highlights || []),
        ]
          .filter(Boolean)
          .join(" ")
          .toLowerCase();

        return searchable.includes(query);
      });
    }

    if (destination !== "all") {
      result = result.filter((item) => {
        const selected = destination.toLowerCase();

        return (
          String(item.destination || "").toLowerCase() === selected ||
          String(item.location || "").toLowerCase().includes(selected) ||
          String(item.state || "").toLowerCase() === selected
        );
      });
    }

    if (activeCategory !== "all") {
      result = result.filter(
        (item) => getCategory(item) === activeCategory
      );
    }

    if (productType !== "all") {
      result = result.filter((item) => item.productType === productType);
    }

    if (sortBy === "name-asc") {
      result.sort((a, b) => getName(a).localeCompare(getName(b)));
    }

    if (sortBy === "name-desc") {
      result.sort((a, b) => getName(b).localeCompare(getName(a)));
    }

    if (sortBy === "price-low") {
      result.sort(
        (a, b) =>
          (getPrice(a).amount ?? Infinity) -
          (getPrice(b).amount ?? Infinity)
      );
    }

    if (sortBy === "price-high") {
      result.sort(
        (a, b) =>
          (getPrice(b).amount ?? -Infinity) -
          (getPrice(a).amount ?? -Infinity)
      );
    }

    return result;
  }, [
    listings,
    search,
    destination,
    activeCategory,
    productType,
    sortBy,
  ]);

  const featuredListings = listings.slice(0, 4);

  function clearFilters() {
    setSearch("");
    setDestination("all");
    setActiveCategory("all");
    setProductType("all");
    setSortBy("recommended");
  }

  return (
    <div className="te-page">
      <Navbar />

      <main>
        <section className="te-hero">
          <div className="te-hero-inner">
            <div className="te-breadcrumb">
              <Link to="/">Home</Link>
              <span>/</span>
              <span>Tours & Experiences</span>
            </div>

            <div className="te-hero-copy">
              <span className="te-eyebrow">YOUR NEXT STORY STARTS HERE</span>
              <h1>
                Find things to do.<br />
                <em>Make memories.</em>
              </h1>
              <p>
                Discover unforgettable tours, local experiences and
                extraordinary places across India.
              </p>
            </div>

            <form
              className="te-search-panel"
              onSubmit={(event) => {
                event.preventDefault();
                document
                  .getElementById("te-results")
                  ?.scrollIntoView({ behavior: "smooth" });
              }}
            >
              <label className="te-search-field te-search-keyword">
                <span className="te-search-icon">⌕</span>
                <span className="te-search-input-wrap">
                  <small>What are you looking for?</small>
                  <input
                    type="search"
                    placeholder="Search tours, places, experiences..."
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                  />
                </span>
              </label>

              <span className="te-search-divider" />

              <label className="te-search-field te-search-destination">
                <span className="te-search-icon">⌖</span>
                <span className="te-search-input-wrap">
                  <small>Destination</small>
                  <select
                    value={destination}
                    onChange={(event) => setDestination(event.target.value)}
                  >
                    <option value="all">Anywhere in India</option>
                    {destinations.map((item) => (
                      <option key={item.slug} value={item.name}>
                        {item.name}
                      </option>
                    ))}
                  </select>
                </span>
              </label>

              <button className="te-search-button" type="submit">
                Search experiences <span>→</span>
              </button>
            </form>

            <div className="te-hero-footnote">
              <span>✦</span> Explore more. Experience more.
            </div>
          </div>
        </section>

        <section className="te-category-section">
          <div className="te-container">
            <div className="te-category-heading">
              <div>
                <span className="te-section-kicker">EXPLORE YOUR WAY</span>
                <h2>What are you in the mood for?</h2>
              </div>

              <button
                className="te-text-link"
                onClick={() => {
                  setActiveCategory("all");
                  document
                    .getElementById("te-results")
                    ?.scrollIntoView({ behavior: "smooth" });
                }}
              >
                View all experiences <span>→</span>
              </button>
            </div>

            <div className="te-category-list">
              {categories.map((category) => (
                <button
                  key={category.id}
                  className={`te-category-item ${
                    activeCategory === category.id
                      ? "te-category-item-active"
                      : ""
                  }`}
                  onClick={() => {
                    setActiveCategory(category.id);
                    document
                      .getElementById("te-results")
                      ?.scrollIntoView({ behavior: "smooth" });
                  }}
                >
                  <span className="te-category-icon">{category.icon}</span>
                  <span>{category.label}</span>
                </button>
              ))}
            </div>
          </div>
        </section>

        <section className="te-destinations-section">
          <div className="te-container">
            <div className="te-section-heading">
              <div>
                <span className="te-section-kicker">
                  PLACES TO FALL IN LOVE WITH
                </span>
                <h2>Where will you go next?</h2>
                <p>Explore India's destinations.</p>
              </div>
              <Link to="/destinations" className="te-text-link">
                All destinations <span>→</span>
              </Link>
            </div>

            <div className="te-destination-grid">
              {destinations.slice(0, 6).map((item) => (
                <DestinationCard key={item.slug} destination={item} />
              ))}
            </div>
          </div>
        </section>

        <section className="te-featured-section">
          <div className="te-container">
            <div className="te-section-heading">
              <div>
                <span className="te-section-kicker">HANDPICKED FOR YOU</span>
                <h2>Experiences worth the trip</h2>
                <p>Start with a little inspiration.</p>
              </div>
              <button
                className="te-text-link"
                onClick={() =>
                  document
                    .getElementById("te-results")
                    ?.scrollIntoView({ behavior: "smooth" })
                }
              >
                Explore all <span>→</span>
              </button>
            </div>

            <div className="te-featured-grid">
              {featuredListings.map((item) => (
                <ExperienceCard
                  key={item.id}
                  item={item}
                  destinations={destinations}
                />
              ))}
            </div>
          </div>
        </section>

        <section className="te-results-section" id="te-results">
          <div className="te-container">
            <div className="te-results-heading">
              <div>
                <span className="te-section-kicker">THE GOOD STUFF</span>
                <h2>Explore tours & experiences</h2>
                <p>Find the right experience for your next adventure.</p>
              </div>

              <button
                className="te-mobile-filter-button"
                onClick={() => setShowFilters((current) => !current)}
              >
                ☷ Filters
              </button>
            </div>

            <div className="te-results-toolbar">
              <div className="te-result-count">
                <strong>{filteredListings.length}</strong> experiences to explore
              </div>

              <div className="te-toolbar-actions">
                <div className="te-view-tabs">
                  {[
                    ["all", "All"],
                    ["package", "Tours"],
                    ["experience", "Experiences"],
                  ].map(([value, label]) => (
                    <button
                      key={value}
                      className={productType === value ? "active" : ""}
                      onClick={() => setProductType(value)}
                    >
                      {label}
                    </button>
                  ))}
                </div>

                <label className="te-sort-control">
                  <span>Sort by</span>
                  <select
                    value={sortBy}
                    onChange={(event) => setSortBy(event.target.value)}
                  >
                    {sortOptions.map((option) => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </select>
                </label>
              </div>
            </div>

            <div className="te-results-layout">
              <aside
                className={`te-filter-sidebar ${
                  showFilters ? "te-filter-sidebar-open" : ""
                }`}
              >
                <div className="te-filter-heading">
                  <h3>Filters</h3>
                  <button onClick={clearFilters}>Clear all</button>
                </div>

                <div className="te-filter-group">
                  <h4>Experience type</h4>
                  {[
                    ["all", "All experiences"],
                    ["package", "Tour packages"],
                    ["experience", "Attractions & activities"],
                  ].map(([value, label]) => (
                    <label className="te-filter-option" key={value}>
                      <input
                        type="radio"
                        name="productType"
                        checked={productType === value}
                        onChange={() => setProductType(value)}
                      />
                      <span>{label}</span>
                    </label>
                  ))}
                </div>

                <div className="te-filter-group">
                  <h4>Categories</h4>
                  {categories.map((category) => (
                    <label className="te-filter-option" key={category.id}>
                      <input
                        type="radio"
                        name="category"
                        checked={activeCategory === category.id}
                        onChange={() => setActiveCategory(category.id)}
                      />
                      <span>{category.label}</span>
                    </label>
                  ))}
                </div>

                <div className="te-filter-group">
                  <h4>Destination</h4>
                  <select
                    className="te-filter-select"
                    value={destination}
                    onChange={(event) => setDestination(event.target.value)}
                  >
                    <option value="all">All destinations</option>
                    {destinations.map((item) => (
                      <option key={item.slug} value={item.name}>
                        {item.name}
                      </option>
                    ))}
                  </select>
                </div>
              </aside>

              <div className="te-results-content">
                {filteredListings.length > 0 ? (
                  <div className="te-listing-grid">
                    {filteredListings.map((item) => (
                      <ExperienceCard
                        key={`${item.productType}-${item.id}`}
                        item={item}
                        destinations={destinations}
                      />
                    ))}
                  </div>
                ) : (
                  <div className="te-empty-state">
                    <span>⌕</span>
                    <h3>No experiences found</h3>
                    <p>
                      Try another search or clear your filters to see
                      everything available.
                    </p>
                    <button onClick={clearFilters}>Clear filters</button>
                  </div>
                )}
              </div>
            </div>
          </div>
        </section>

        <section className="te-bottom-banner">
          <div className="te-bottom-banner-inner">
            <div>
              <span>YOUR NEXT ADVENTURE IS CLOSER THAN YOU THINK</span>
              <h2>Have a trip in mind?</h2>
              <p>
                Tell us what you're dreaming of. We'll help you plan it.
              </p>
            </div>
            <Link to="/contact" className="te-bottom-cta">
              Plan your trip <span>↗</span>
            </Link>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
