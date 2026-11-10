
import { useEffect, useRef, useState } from "react";
import BounceCards from "./BounceCards";
import "./PopularTours.css";
import { useNavigate } from "react-router-dom";

const tours = [
  {
    name: "Mysore",
    state: "Karnataka",
    image: import.meta.env.BASE_URL + "images/tour-mysore.jpg",
    highlight: "Palaces & heritage",
    tag: "CULTURE",
  },
  {
    name: "Ooty",
    state: "Tamil Nadu",
    image: import.meta.env.BASE_URL + "images/tour-ooty.jpg",
    highlight: "Hills & scenic journeys",
    tag: "HILL ESCAPE",
  },
  {
    name: "Munnar",
    state: "Kerala",
    image: import.meta.env.BASE_URL + "images/tour-munnar.jpg",
    highlight: "Tea estates & mountain views",
    tag: "NATURE",
  },
  {
    name: "Alleppey",
    state: "Kerala",
    image: import.meta.env.BASE_URL + "images/tour-alleppey.jpg",
    highlight: "Backwaters & houseboats",
    tag: "SLOW TRAVEL",
  },
  {
    name: "Jaipur",
    state: "Rajasthan",
    image: import.meta.env.BASE_URL + "images/tour-jaipur.jpg",
    highlight: "Forts & royal architecture",
    tag: "HERITAGE",
  },
  {
    name: "Agra",
    state: "Uttar Pradesh",
    image: import.meta.env.BASE_URL + "images/tour-agra.jpg",
    highlight: "Monuments & history",
    tag: "ICONIC INDIA",
  },
  {
    name: "Goa",
    state: "Goa",
    image: import.meta.env.BASE_URL + "images/tour-goa.jpg",
    highlight: "Beaches & coastal escapes",
    tag: "COASTAL",
  },
  {
    name: "Dandeli",
    state: "Karnataka",
    image: import.meta.env.BASE_URL + "images/things-to-do.jpg",
    highlight: "Forest & river adventures",
    tag: "ADVENTURE",
  },
  {
    name: "Hampi",
    state: "Karnataka",
    image: import.meta.env.BASE_URL + "images/tour-hampi.jpg",
    highlight: "Ruins & heritage trails",
    tag: "HISTORY",
  },
  {
    name: "Kodaikanal",
    state: "Tamil Nadu",
    image: import.meta.env.BASE_URL + "images/tour-kodaikanal.jpg",
    highlight: "Lakes & hill escapes",
    tag: "HILL ESCAPE",
  },
];

const destinationSlugs = {
  Mysore: "mysore",
  Ooty: "ooty",
  Munnar: "munnar",
  Alleppey: "alleppey",
  Jaipur: "jaipur",
  Agra: "agra",
  Goa: "goa",
  Dandeli: "dandeli",
  Hampi: "hampi",
  Kodaikanal: "kodaikanal",
};

function TourRow({ items, onSelect, rowNumber, title, description }) {
  const rowRef = useRef(null);
  const [width, setWidth] = useState(0);
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const element = rowRef.current;
    if (!element) return;

    const resizeObserver = new ResizeObserver(([entry]) => {
      setWidth(entry.contentRect.width);
    });

    const intersectionObserver = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setVisible(true);
          intersectionObserver.disconnect();
        }
      },
      { threshold: 0.15 }
    );

    resizeObserver.observe(element);
    intersectionObserver.observe(element);

    return () => {
      resizeObserver.disconnect();
      intersectionObserver.disconnect();
    };
  }, []);

  const spread = Math.min(150, width * 0.145);
  const cardSize = Math.min(210, width * 0.27);
  const pushDistance = Math.min(70, width * 0.055);
  const height = Math.max(190, cardSize + 100);

  return (
    <div className="popular-tour-row" ref={rowRef}>
      <div className="popular-tour-row-heading">
        <div>
          <span className="popular-tour-row-index">
            COLLECTION 0{rowNumber}
          </span>
          <h3>{title}</h3>
          <p>{description}</p>
        </div>

        <span className="popular-tour-row-count">
          0{items.length} DESTINATIONS
        </span>
      </div>

      <div
        className="popular-tour-stage"
        style={{ minHeight: height }}
      >
        {visible && width > 0 && (
          <BounceCards
            images={items.map((tour) => ({
              src: tour.image,
              alt: `${tour.name}, ${tour.state}`,
            }))}
            containerWidth={width}
            containerHeight={height}
            cardSize={cardSize}
            spread={spread}
            rotation={7}
            arc={12}
            pushDistance={pushDistance}
            hoverScale={1.06}
            bounciness={0.5}
            animationDelay={0.1}
            animationStagger={0.09}
            borderWidth={4}
            borderColor="#F7F5EF"
            radius={20}
            shadow
            enableHover
            onCardClick={(index) => onSelect(items[index])}
          />
        )}
      </div>

      <div
        className="popular-tour-names"
        aria-label={`Destinations in collection ${rowNumber}`}
      >
        {items.map((tour, index) => (
          <button
            type="button"
            key={tour.name}
            onClick={() => onSelect(tour)}
            aria-label={`Explore ${tour.name}, ${tour.state}`}
          >
            <span className="popular-tour-name-number">
              0{index + 1}
            </span>

            <span className="popular-tour-name-content">
              <strong>{tour.name}</strong>
              <small>{tour.tag}</small>
            </span>

            <span className="popular-tour-name-arrow" aria-hidden="true">
              ↗
            </span>
          </button>
        ))}
      </div>
    </div>
  );
}

export default function PopularTours() {
  const navigate = useNavigate();

  function openTour(tour) {
    const slug = destinationSlugs[tour.name];

    if (slug) {
      navigate(`/destinations/${slug}`);
    }
  }

  return (
    <section
      className="popular-tours"
      aria-labelledby="popular-tours-title"
    >
      <div className="popular-tours-inner">
        <div className="popular-tours-heading">
          <div className="popular-tours-heading-copy">
            <span className="popular-tours-eyebrow">
              THE PLACES WORTH THE JOURNEY
            </span>

            <h2 id="popular-tours-title">
              India, <em>in every direction.</em>
            </h2>

            <p>
              From quiet mountain mornings to cities that never
              run out of stories. Find the place that feels like
              your next adventure.
            </p>
          </div>

          <button
            type="button"
            className="popular-tours-all-link"
            onClick={() => navigate("/destinations")}
          >
            Explore all destinations
            <span aria-hidden="true">↗</span>
          </button>
        </div>

        <TourRow
          items={tours.slice(0, 5)}
          onSelect={openTour}
          rowNumber={1}
          title="The slow escapes"
          description="Green landscapes, cooler air and room to breathe."
        />

        <TourRow
          items={tours.slice(5, 10)}
          onSelect={openTour}
          rowNumber={2}
          title="The story seekers"
          description="Ancient streets, coastal days and unforgettable places."
        />

        <div className="popular-tours-bottom">
          <span>YOUR NEXT CHAPTER STARTS SOMEWHERE.</span>

          <button
            type="button"
            onClick={() => navigate("/destinations")}
          >
            Find your destination
            <span aria-hidden="true">↗</span>
          </button>
        </div>
      </div>
    </section>
  );
}
