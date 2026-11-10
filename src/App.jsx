
import { useEffect, useState } from "react";
import "./App.css";
import useHeroTransition from "./useHeroTransition";
import PackageCategories from "./components/PackageCategories";
import FeaturedPackages from "./components/FeaturedPackages";
import ExploreServices from "./components/ExploreServices";
import PopularTours from "./components/PopularTours";
import WhyUs from "./components/WhyUs";
import Footer from "./components/Footer";
import Navbar from "./components/Navbar";
import { useNavigate } from "react-router-dom";

const destinations = [
  {
    name: "Karnataka",
    image: import.meta.env.BASE_URL + "images/karnataka.jpg",
    caption: "Coffee trails, ancient cities and greener weekends.",
    tag: "Culture & nature",
  },
  {
    name: "Kerala",
    image: import.meta.env.BASE_URL + "images/kerala.jpg",
    caption: "Slow mornings, backwaters and a little coastal calm.",
    tag: "Backwaters & stays",
  },
  {
    name: "Rajasthan",
    image: import.meta.env.BASE_URL + "images/rajasthan.jpg",
    caption: "Royal cities, desert skies and stories around every corner.",
    tag: "Heritage escapes",
  },
  {
    name: "Goa",
    image: import.meta.env.BASE_URL + "images/goa.jpg",
    caption: "Salty air, sunshine and days without a rush.",
    tag: "Coastal escapes",
  },
  {
    name: "Tamil Nadu",
    image: import.meta.env.BASE_URL + "images/tamil-nadu.jpg",
    caption: "Temple towns, hill escapes and beautiful coastlines.",
    tag: "Temples & hills",
  },
  {
    name: "Uttarakhand",
    image: import.meta.env.BASE_URL + "images/uttarakhand.jpg",
    caption: "Mountain mornings and trails worth waking up for.",
    tag: "Mountain trails",
  },
  {
    name: "Uttar Pradesh",
    image: import.meta.env.BASE_URL + "images/uttar-pradesh.jpg",
    caption: "Timeless monuments and journeys through living history.",
    tag: "History & heritage",
  },
  {
    name: "Maharashtra",
    image: import.meta.env.BASE_URL + "images/maharashtra.jpg",
    caption: "Fort trails, city breaks and monsoon-green hills.",
    tag: "Forts & road trips",
  },
];

const experienceCategories = [
  {
    name: "Day trips",
    description: "A whole adventure in one day.",
    icon: "☀",
    category: "tours",
  },
  {
    name: "Attractions",
    description: "Landmarks, tickets and must-sees.",
    icon: "⌖",
    category: "attractions",
  },
  {
    name: "Adventure",
    description: "Take the scenic route. Then some.",
    icon: "↗",
    category: "adventure",
  },
  {
    name: "Culture",
    description: "Local stories, crafts and heritage.",
    icon: "✳",
    category: "culture",
  },
  {
    name: "Food experiences",
    description: "Taste your way around India.",
    icon: "◒",
    category: "food",
  },
  {
    name: "Nature escapes",
    description: "Find your little patch of green.",
    icon: "❋",
    category: "nature",
  },
  {
    name: "Water activities",
    description: "Make a splash somewhere new.",
    icon: "≈",
    category: "water",
  },
  {
    name: "Stays & retreats",
    description: "Somewhere worth staying longer.",
    icon: "⌂",
    category: "stays",
  },
];

const TRANSITION_DURATION = 1100;
const DISPLAY_DURATION = 4500;

function getLocalDate(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

export default function App() {
  const navigate = useNavigate();

  const [homeCity, setHomeCity] = useState("Bengaluru");
  const [destination, setDestination] = useState("");
  const [travelDate, setTravelDate] = useState("");
  const [searchMessage, setSearchMessage] = useState("");

  const [activeIndex, setActiveIndex] = useState(0);
  const [incomingIndex, setIncomingIndex] = useState(null);
  const [paused, setPaused] = useState(false);
  const [reducedMotion, setReducedMotion] = useState(false);

  useHeroTransition(incomingIndex, reducedMotion);

  const activeDestination = destinations[activeIndex];
  const isTransitioning = incomingIndex !== null;
  const today = getLocalDate(new Date());

  const queue = Array.from(
    { length: destinations.length - 1 },
    (_, position) => {
      const index = (activeIndex + position + 1) % destinations.length;

      return {
        ...destinations[index],
        index,
      };
    }
  );

  useEffect(() => {
    const media = window.matchMedia("(prefers-reduced-motion: reduce)");

    function updatePreference() {
      setReducedMotion(media.matches);
    }

    updatePreference();
    media.addEventListener("change", updatePreference);

    return () => media.removeEventListener("change", updatePreference);
  }, []);

  useEffect(() => {
    destinations.forEach((item) => {
      const image = new Image();
      image.src = item.image;
    });
  }, []);

  useEffect(() => {
    if (paused || reducedMotion || incomingIndex !== null) return;

    const timer = window.setTimeout(() => {
      setIncomingIndex((activeIndex + 1) % destinations.length);
    }, DISPLAY_DURATION);

    return () => window.clearTimeout(timer);
  }, [activeIndex, incomingIndex, paused, reducedMotion]);

  useEffect(() => {
    if (incomingIndex === null) return;

    const timer = window.setTimeout(
      () => {
        setActiveIndex(incomingIndex);
        setIncomingIndex(null);
      },
      reducedMotion ? 0 : TRANSITION_DURATION
    );

    return () => window.clearTimeout(timer);
  }, [incomingIndex, reducedMotion]);

  // Reveal sections as they enter the viewport.
  useEffect(() => {
    const elements = document.querySelectorAll("[data-reveal]");

    if (!("IntersectionObserver" in window)) {
      elements.forEach((element) => element.classList.add("is-visible"));
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.12,
        rootMargin: "0px 0px -40px 0px",
      }
    );

    elements.forEach((element) => observer.observe(element));

    return () => observer.disconnect();
  }, []);

  function showNextDestination() {
    if (isTransitioning) return;
    setIncomingIndex((activeIndex + 1) % destinations.length);
  }

  function showPreviousDestination() {
    if (isTransitioning) return;

    setIncomingIndex(
      (activeIndex - 1 + destinations.length) % destinations.length
    );
  }

  function selectNextWeekend() {
    const nextSaturday = new Date();
    const daysUntilSaturday = (6 - nextSaturday.getDay() + 7) % 7;

    nextSaturday.setDate(
      nextSaturday.getDate() + (daysUntilSaturday || 7)
    );

    setTravelDate(getLocalDate(nextSaturday));
    setSearchMessage("");
  }

  function handleSearch(event) {
    event.preventDefault();

    const selectedDestination = destination.trim();

    if (!selectedDestination) {
      setSearchMessage("Enter a destination to get started.");
      return;
    }

    const params = new URLSearchParams({ q: selectedDestination });

    if (travelDate) {
      params.set("date", travelDate);
    }

    navigate(`/packages?${params.toString()}`);
  }

  function openExperiences(category) {
    navigate(`/tours-experiences?category=${category}`);
  }

  return (
    <>
      <Navbar
        showCityPicker
        homeCity={homeCity}
        onHomeCityChange={setHomeCity}
      />

      <main>
        {/* HERO */}
        <section
          className="hero"
          aria-labelledby="hero-title"
          aria-roledescription="carousel"
          aria-label="Discover Indian destinations"
        >
          <img
            className="hero-background"
            src={activeDestination.image}
            alt=""
            fetchPriority="high"
          />

          {isTransitioning && (
            <div className="hero-incoming" aria-hidden="true">
              <img src={destinations[incomingIndex].image} alt="" />
            </div>
          )}

          <div className="hero-shade" aria-hidden="true" />

          <div className="hero-topline">
            <span>CURATED JOURNEYS · ACROSS INDIA</span>
            <span className="hero-topline-right">
              {String(activeIndex + 1).padStart(2, "0")} — INDIA
            </span>
          </div>

          <div className="hero-content">
            <span className="hero-label">
              YOUR NEXT STORY STARTS HERE
            </span>

            <h1 id="hero-title">
              Out there is
              <br />
              <em>closer than ever.</em>
            </h1>

            <p>
              From spontaneous day trips to the journeys you've
              been dreaming about. Find your next somewhere.
            </p>

            <a className="hero-link" href="#discovery">
              Explore experiences
              <span aria-hidden="true">↗</span>
            </a>
          </div>

          <div className="hero-bottom">
            <div className="destination-caption" id="destination-preview">
              <span className="destination-eyebrow">
                CURRENTLY EXPLORING
              </span>

              <h2>{activeDestination.name}</h2>
              <p>{activeDestination.caption}</p>

              <div className="slider-controls">
                <span className="slide-number">
                  {String(activeIndex + 1).padStart(2, "0")}
                  <span> / {String(destinations.length).padStart(2, "0")}</span>
                </span>

                <button
                  type="button"
                  className="slider-control"
                  onClick={() => setPaused((current) => !current)}
                  disabled={reducedMotion}
                  aria-label={
                    reducedMotion
                      ? "Automatic rotation disabled by reduced motion preference"
                      : paused
                        ? "Resume automatic destination rotation"
                        : "Pause automatic destination rotation"
                  }
                >
                  {paused || reducedMotion ? "Play" : "Pause"}
                </button>

                <button
                  type="button"
                  className="slider-control"
                  onClick={showPreviousDestination}
                  disabled={isTransitioning}
                  aria-label="Previous destination"
                >
                  ←
                </button>

                <button
                  type="button"
                  className="slider-control next-control"
                  onClick={showNextDestination}
                  disabled={isTransitioning}
                  aria-label="Next destination"
                >
                  →
                </button>
              </div>
            </div>

            <div
              className="destination-queue"
              aria-label="Upcoming destinations"
            >
              {queue.map((item, position) => (
                <button
                  type="button"
                  className={`destination-card ${
                    position === 0 && isTransitioning
                      ? "destination-card--departing"
                      : ""
                  }`}
                  key={item.name}
                  style={{
                    "--position": position,
                    zIndex: queue.length - position,
                  }}
                  onClick={() => {
                    if (!isTransitioning) setIncomingIndex(item.index);
                  }}
                  aria-label={`Explore ${item.name}`}
                >
                  <img src={item.image} alt="" />
                  <div className="destination-card-shade" />
                  <div className="destination-card-text">
                    <span>{String(item.index + 1).padStart(2, "0")}</span>
                    <h3>{item.name}</h3>
                  </div>
                </button>
              ))}
            </div>
          </div>

          <div className="hero-scroll-cue" aria-hidden="true">
            <span>SCROLL TO DISCOVER</span>
            <span className="scroll-line" />
          </div>
        </section>

        {/* SEARCH */}
        <section className="discovery" id="discovery" aria-label="Find an experience">
          <div className="discovery-heading" data-reveal>
            <span className="section-kicker">THE GOOD PART STARTS HERE</span>
            <h2>Where are we going?</h2>
            <p>Pick a place. Pick a date. We'll help you take it from there.</p>
          </div>

          <form className="search-panel" onSubmit={handleSearch}>
            <label className="search-field">
              <span className="search-field-icon">⌖</span>
              <span>DESTINATION</span>
              <input
                type="text"
                list="destination-options"
                placeholder={`Try places near ${homeCity}`}
                value={destination}
                onChange={(event) => {
                  setDestination(event.target.value);
                  setSearchMessage("");
                }}
                required
              />

              <datalist id="destination-options">
                {destinations.map((item) => (
                  <option key={item.name} value={item.name} />
                ))}
                <option value="Bengaluru" />
                <option value="Coorg" />
                <option value="Mysuru" />
                <option value="Nandi Hills" />
                <option value="Lonavala" />
                <option value="Munnar" />
              </datalist>
            </label>

            <label className="search-field date-field">
              <span className="search-field-icon">◷</span>
              <span>TRAVEL DATE</span>
              <input
                type="date"
                aria-label="Travel date"
                min={today}
                value={travelDate}
                onChange={(event) => {
                  setTravelDate(event.target.value);
                  setSearchMessage("");
                }}
              />
            </label>

            <button
              type="button"
              className="weekend-button"
              onClick={selectNextWeekend}
            >
              <span>✳</span>
              Next weekend
            </button>

            <button type="submit" className="search-button">
              Search
              <span aria-hidden="true">↗</span>
            </button>
          </form>

          <p className="search-feedback" role="status">
            {searchMessage}
          </p>
        </section>

        {/* EXPERIENCE CATEGORIES */}
        <section className="experience-discovery" id="experiences">
          <div className="section-heading-row" data-reveal>
            <div>
              <span className="section-kicker">MORE THAN JUST GETAWAYS</span>
              <h2>Find your kind of <em>somewhere.</em></h2>
              <p>Little adventures, big memories. Pick what feels like you.</p>
            </div>

            <button
              type="button"
              className="text-link"
              onClick={() => navigate("/tours-experiences")}
            >
              Explore everything <span>↗</span>
            </button>
          </div>

          <div className="experience-category-grid">
            {experienceCategories.map((category, index) => (
              <button
                type="button"
                className="experience-category-card"
                key={category.category}
                style={{ "--card-delay": `${index * 70}ms` }}
                onClick={() => openExperiences(category.category)}
                data-reveal
              >
                <span className="category-icon" aria-hidden="true">
                  {category.icon}
                </span>
                <span className="category-card-copy">
                  <strong>{category.name}</strong>
                  <small>{category.description}</small>
                </span>
                <span className="category-arrow" aria-hidden="true">↗</span>
              </button>
            ))}
          </div>
        </section>

        {/* DESTINATION DISCOVERY */}
        <section className="destination-discovery" data-reveal>
          <div className="section-heading-row">
            <div>
              <span className="section-kicker">A COUNTRY FULL OF STORIES</span>
              <h2>India, in every <em>direction.</em></h2>
              <p>From mountain air to saltwater days. Where will you go?</p>
            </div>

            <button
              type="button"
              className="text-link"
              onClick={() => navigate("/destinations")}
            >
              All destinations <span>↗</span>
            </button>
          </div>

          <div className="destination-strip">
            {destinations.map((item, index) => (
              <button
                type="button"
                className="destination-tile"
                key={item.name}
                onClick={() => navigate("/destinations")}
                style={{ "--tile-index": index }}
              >
                <img src={item.image} alt="" loading="lazy" />
                <span className="destination-tile-shade" />
                <span className="destination-tile-content">
                  <small>{item.tag}</small>
                  <strong>{item.name}</strong>
                  <span className="destination-tile-arrow">↗</span>
                </span>
              </button>
            ))}
          </div>
        </section>

        {/* EXISTING HOMEPAGE SECTIONS */}
        <div data-reveal>
          <PackageCategories />
        </div>

        <div data-reveal>
          <FeaturedPackages />
        </div>

        <div data-reveal>
          <ExploreServices />
        </div>

        <div data-reveal>
          <PopularTours />
        </div>

        <div data-reveal>
          <WhyUs />
        </div>
      </main>

      <Footer />
    </>
  );
}
