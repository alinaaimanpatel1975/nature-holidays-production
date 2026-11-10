import { useState } from "react";
import "./PackageCategories.css";
import { useNavigate } from "react-router-dom";

const categories = [
  {
    id: "school-college",
    number: "01",
    name: "School & College",
    description: "Learning beyond the classroom.",
    tags: "Educational tours · Student outings",
  },
  {
    id: "honeymoon",
    number: "02",
    name: "Honeymoon",
    description: "Somewhere special. Just you two.",
    tags: "Romantic stays · Couple getaways",
  },
  {
    id: "corporate-group",
    number: "03",
    name: "Corporate & Group",
    description: "A change of scene for the whole team.",
    tags: "Team outings · Group journeys",
  },
  {
    id: "family",
    number: "04",
    name: "Family Holidays",
    description: "Good days with your favourite people.",
    tags: "Family breaks · Domestic tours",
  },
  {
    id: "weekend",
    number: "05",
    name: "Weekend & Day Outings",
    description: "A little break goes a long way.",
    tags: "Resort days · Quick escapes",
  },
];

export default function PackageCategories() {
  const navigate = useNavigate();
  const [selectedId, setSelectedId] = useState(null);

  const selectedCategory = categories.find(
    (category) => category.id === selectedId
  );

  return (
    <section
      className="package-categories"
      id="packages"
      aria-labelledby="package-categories-title"
    >
      <div className="package-categories-heading">
        <div>
          <span className="package-section-label">
            YOUR PEOPLE. YOUR KIND OF TRIP.
          </span>

          <h2 id="package-categories-title">
            Find your package.
          </h2>
        </div>

        <p>
          Big group plans or a little time away.
          Start with the trip you have in mind.
        </p>
      </div>

      <div className="package-category-grid">
        {categories.map((category) => {
          const isSelected = selectedId === category.id;

          return (
            <button
              key={category.id}
              type="button"
              className={`package-category-card ${
                isSelected ? "package-category-card--selected" : ""
              }`}
              aria-pressed={isSelected}
              onClick={() => navigate(`/packages?category=${category.id}`)}
            >
              <span className="package-category-top">
                <span className="package-category-number">
                  {category.number}
                </span>

                <span className="package-category-arrow" aria-hidden="true">
                  ↗
                </span>
              </span>

              <span className="package-category-name">
                {category.name}
              </span>

              <span className="package-category-description">
                {category.description}
              </span>

              <span className="package-category-tags">
                {category.tags}
              </span>
            </button>
          );
        })}
      </div>

      <p className="package-category-status" role="status">
        {selectedCategory
          ? `${selectedCategory.name} selected. Package listings will connect here in the next step.`
          : "Choose the kind of trip you’re planning."}
      </p>
    </section>
  );
}