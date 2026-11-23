
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import "./TripPlanner.css";

const tripTypes = [
  "Family Holiday",
  "Adventure & Wildlife",
  "Religious & Pilgrimage",
  "School & College",
  "Honeymoon",
  "Corporate & Group",
  "Weekend & Day Outing",
  "Cultural & Heritage",
  "Other / Custom Trip",
];

function getToday() {
  const today = new Date();
  return [
    today.getFullYear(),
    String(today.getMonth() + 1).padStart(2, "0"),
    String(today.getDate()).padStart(2, "0"),
  ].join("-");
}

export default function TripPlanner() {
  const navigate = useNavigate();
  const [isOpen, setIsOpen] = useState(false);
  const [startDate, setStartDate] = useState("");
  const [endDate, setEndDate] = useState("");
  const [tripType, setTripType] = useState("");
  const [error, setError] = useState("");

  function handleSubmit(event) {
    event.preventDefault();

    if (!tripType) {
      setError("Choose the kind of trip you're planning.");
      return;
    }

    if (startDate && endDate && endDate < startDate) {
      setError("Your return date must be after your start date.");
      return;
    }

    const params = new URLSearchParams();

    if (startDate) params.set("date", startDate);
    if (endDate) params.set("endDate", endDate);
    params.set("tripType", tripType);

    setIsOpen(false);
    navigate(`/contact?${params.toString()}`);
  }

  return (
    <section className="trip-planner-section">
      <div className="trip-planner-intro">
        <span className="trip-planner-eyebrow">YOUR NEXT ESCAPE</span>
        <h2>Somewhere is calling.</h2>
        <p>Tell us what you're dreaming of. We'll help you plan the rest.</p>
      </div>

      <button
        className="trip-planner-trigger"
        type="button"
        onClick={() => {
          setError("");
          setIsOpen(true);
        }}
      >
        <span>
          <small>READY WHEN YOU ARE</small>
          <strong>Plan your next journey</strong>
        </span>
        <span className="trip-planner-trigger-arrow" aria-hidden="true">
          ↗
        </span>
      </button>

      {isOpen && (
        <div
          className="trip-planner-overlay"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget) {
              setIsOpen(false);
            }
          }}
        >
          <section
            className="trip-planner-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="trip-planner-title"
          >
            <button
              className="trip-planner-close"
              type="button"
              aria-label="Close trip planner"
              onClick={() => setIsOpen(false)}
            >
              ×
            </button>

            <span className="trip-planner-eyebrow">
              LET'S MAKE IT HAPPEN
            </span>
            <h2 id="trip-planner-title">Where should we take you?</h2>
            <p className="trip-planner-description">
              A few details are all we need to get the conversation started.
            </p>

            <form onSubmit={handleSubmit}>
              <div className="trip-planner-fields">
                <label>
                  <span>Departure date</span>
                  <input
                    type="date"
                    min={getToday()}
                    value={startDate}
                    onChange={(event) => {
                      setStartDate(event.target.value);
                      setError("");
                    }}
                  />
                </label>

                <label>
                  <span>Return date</span>
                  <input
                    type="date"
                    min={startDate || getToday()}
                    value={endDate}
                    onChange={(event) => {
                      setEndDate(event.target.value);
                      setError("");
                    }}
                  />
                </label>

                <label className="trip-planner-type">
                  <span>What kind of trip are you imagining?</span>
                  <select
                    value={tripType}
                    onChange={(event) => {
                      setTripType(event.target.value);
                      setError("");
                    }}
                    required
                  >
                    <option value="" disabled>
                      Choose your travel style
                    </option>
                    {tripTypes.map((type) => (
                      <option key={type} value={type}>
                        {type}
                      </option>
                    ))}
                  </select>
                </label>
              </div>

              {error && (
                <p className="trip-planner-error" role="alert">
                  {error}
                </p>
              )}

              <button className="trip-planner-submit" type="submit">
                Continue to enquiry <span aria-hidden="true">↗</span>
              </button>

              <p className="trip-planner-footnote">
                Dates are flexible? You can leave them blank and discuss
                options with our team.
              </p>
            </form>
          </section>
        </div>
      )}
    </section>
  );
}
