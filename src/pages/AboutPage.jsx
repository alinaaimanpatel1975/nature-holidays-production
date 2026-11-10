
import { Link } from "react-router-dom";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

import "./AboutPage.css";

const collections = [
  {
    number: "01",
    title: "One Day Fun Tours",
    description:
      "Enjoy a refreshing day away with amusement parks, adventure resorts and entertainment destinations.",
    examples: "Wonderla · GRS Fantasy Park · Snow City · Fun World",
    category: "DAY EXPERIENCES",
  },
  {
    number: "02",
    title: "Historical & Fun Tours",
    description:
      "Discover heritage destinations, historic landmarks, temples and memorable places through day excursions.",
    examples: "Mysore · Talakadu · Nandi Hills · Chitradurga",
    category: "HERITAGE & EXPLORATION",
  },
  {
    number: "03",
    title: "Namma Bengaluru Rounds",
    description:
      "Explore the city's gardens, architecture, museums, monuments and cultural attractions.",
    examples:
      "Lalbagh · Bangalore Palace · Cubbon Park · Visvesvaraya Museum",
    category: "CITY EXPLORATION",
  },
  {
    number: "04",
    title: "Explore North India",
    description:
      "Discover India's northern landscapes, destinations and cultural heritage through holiday journeys.",
    examples: "Kashmir · Himachal Pradesh · Rajasthan · Uttar Pradesh",
    category: "NORTH INDIA",
  },
  {
    number: "05",
    title: "Explore South India",
    description:
      "Experience the diversity of South India, from hill stations and coastlines to heritage destinations.",
    examples: "Kerala · Karnataka · Tamil Nadu · Andhra Pradesh",
    category: "SOUTH INDIA",
  },
];

const travellerTypes = [
  "School & college excursions",
  "Family holidays",
  "Honeymoon journeys",
  "Corporate & group travel",
  "Weekend getaways",
  "One-day outings",
];

const services = [
  {
    number: "01",
    title: "Travel planning",
    description:
      "Explore destinations and travel collections suited to different occasions and group requirements.",
  },
  {
    number: "02",
    title: "Transport arrangements",
    description:
      "Travel arrangements that help bring different parts of a journey together.",
  },
  {
    number: "03",
    title: "Hotel arrangements",
    description:
      "Accommodation arrangements as part of planned holidays and tours.",
  },
  {
    number: "04",
    title: "Ticket arrangements",
    description:
      "Ticketing support for travel and selected experiences.",
  },
];

export default function AboutPage() {
  return (
    <>
      <Navbar />

      <main className="about-page">

        {/* HERO */}

        <section className="about-hero">
          <div className="about-hero__copy">
            <span className="about-eyebrow">
              THE NATURE HOLIDAYS STORY
            </span>

            <h1>
              Turning dreams
              <span> into destinations.</span>
            </h1>

            <p>
              Since 2011, Nature Holidays has been bringing together
              travel ideas, destinations and experiences for
              different kinds of travellers. From a day exploring
              Bengaluru to holidays across India, every journey
              begins with somewhere to go.
            </p>

            <div className="about-hero__actions">
              <Link to="/packages" className="about-button">
                Discover our journeys <span>↗</span>
              </Link>

              <Link to="/contact" className="about-text-link">
                Speak to our team
              </Link>
            </div>

            <div className="about-hero__since">
              <span>ESTABLISHED</span>
              <strong>2011</strong>
              <span className="about-hero__since-line" />
              <span>MADE FOR THE LOVE OF TRAVEL</span>
            </div>
          </div>

          <div className="about-hero__visual">
            <div className="about-visual__image" />

            <div className="about-visual__overlay" />

            <div className="about-visual__top">
              <span>NATURE HOLIDAYS</span>
              <span>EST. 2011</span>
            </div>

            <div className="about-visual__label">
              <span>YOUR NEXT JOURNEY</span>
              <strong>Begins here.</strong>
            </div>

            <span className="about-visual__coordinates">
              INDIA · ENDLESS POSSIBILITIES
            </span>
          </div>
        </section>

        {/* COMPANY INTRODUCTION */}

        <section className="about-intro">
          <div className="about-intro__side">
            <span className="about-eyebrow">01 / OUR BEGINNINGS</span>

            <p>
              A journey that began in 2011.
            </p>
          </div>

          <div className="about-intro__main">
            <h2>
              Travel has many forms. We bring them together.
            </h2>

            <p>
              Established in 2011, Nature Holidays is a travel
              company built around the idea of making different
              kinds of journeys accessible to different kinds
              of travellers.
            </p>

            <p>
              Our travel collections bring together local
              sightseeing, one-day outings, historical
              excursions and holiday journeys across North
              and South India.
            </p>

            <p>
              From school and college excursions to family
              holidays, honeymoon journeys and group travel,
              our brochure reflects a variety of ways to
              discover new places.
            </p>

            <div className="about-tagline">
              <span>OUR TAGLINE</span>
              <strong>Turning Dreams Into Destinations.</strong>
            </div>
          </div>
        </section>

        {/* TRAVEL COLLECTIONS */}

        <section className="about-collections">
          <div className="about-section-heading">
            <div>
              <span className="about-eyebrow">02 / OUR TRAVEL COLLECTIONS</span>
              <h2>
                A journey for every reason.
              </h2>
            </div>

            <p>
              Explore the travel categories featured in the
              Nature Holidays brochure, from local day trips
              to journeys across India.
            </p>
          </div>

          <div className="about-collection-grid">
            {collections.map((item) => (
              <article
                className="about-collection-card"
                key={item.number}
              >
                <div className="about-collection-card__top">
                  <span>{item.category}</span>
                  <span>{item.number}</span>
                </div>

                <h3>{item.title}</h3>

                <p>{item.description}</p>

                <div className="about-collection-card__examples">
                  {item.examples}
                </div>

                <Link
                  to="/packages"
                  className="about-collection-card__link"
                >
                  Explore journeys <span>↗</span>
                </Link>
              </article>
            ))}
          </div>
        </section>

        {/* TRAVELLER TYPES */}

        <section className="about-travellers">
          <div className="about-travellers__heading">
            <span className="about-eyebrow">03 / WHO WE CATER TO</span>

            <h2>
              Different travellers. Different journeys.
            </h2>

            <p>
              Not every holiday looks the same. Our brochure
              brings together travel options for a range
              of occasions, interests and group sizes.
            </p>
          </div>

          <div className="about-travellers__list">
            {travellerTypes.map((style, index) => (
              <div className="about-traveller-item" key={style}>
                <span>
                  {String(index + 1).padStart(2, "0")}
                </span>

                <strong>{style}</strong>

                <span aria-hidden="true">↗</span>
              </div>
            ))}
          </div>
        </section>

        {/* SERVICES */}

        <section className="about-services">
          <div className="about-section-heading">
            <div>
              <span className="about-eyebrow">04 / BEYOND DESTINATIONS</span>

              <h2>
                The journey is in the details.
              </h2>
            </div>

            <p>
              Travel involves more than choosing a destination.
              The brochure also highlights supporting travel
              arrangements that bring a holiday together.
            </p>
          </div>

          <div className="about-services__grid">
            {services.map((service) => (
              <article key={service.number}>
                <span>{service.number}</span>
                <h3>{service.title}</h3>
                <p>{service.description}</p>
              </article>
            ))}
          </div>
        </section>

        {/* COMPANY STATEMENT */}

        <section className="about-belief">
          <span className="about-eyebrow">
            NATURE HOLIDAYS · ESTABLISHED 2011
          </span>

          <p>
            There is always somewhere new to go.
            <span>And a new memory waiting to be made.</span>
          </p>

          <Link
            to="/destinations"
            className="about-button about-button--light"
          >
            Find your next destination <span>↗</span>
          </Link>
        </section>

        {/* REVIEWS - RESERVED FOR VERIFIED FEEDBACK */}

        <section className="about-reviews-note">
          <span className="about-eyebrow">
            05 / THE PEOPLE WE TRAVEL WITH
          </span>

          <h2>
            Every journey has a story.
          </h2>

          <p>
            We're building a space for genuine traveller
            experiences. Verified customer feedback will
            appear here as it becomes available.
          </p>
        </section>

        {/* CONTACT */}

        <section className="about-contact">
          <div>
            <span className="about-eyebrow">
              LET'S PLAN SOMETHING
            </span>

            <h2>Where would you like to go?</h2>

            <p>
              Tell us about the journey you have in mind.
              Let's explore the possibilities together.
            </p>
          </div>

          <Link to="/contact" className="about-button">
            Plan your journey <span>↗</span>
          </Link>
        </section>

      </main>

      <Footer />
    </>
  );
}
