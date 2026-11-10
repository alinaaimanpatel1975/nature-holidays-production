import "./WhyUs.css";
import { Link } from "react-router-dom";
const reasons = [
  {
    number: "01",
    title: "Trips for your kind of group",
    description:
      "School and college outings, corporate trips, family holidays and honeymoon packages.",
  },
  {
    number: "02",
    title: "More of the planning, together",
    description:
      "Tour packages, hotel and resort reservations, plus flight, train and bus booking assistance.",
  },
  {
    number: "03",
    title: "Local escapes to longer journeys",
    description:
      "Explore Bengaluru day outings, weekend breaks and tours across North and South India.",
  },
  {
    number: "04",
    title: "A team you can talk to",
    description:
      "Connect with our Bengaluru team to discuss your dates, group size and travel requirements.",
  },
];

export default function WhyUs() {
  return (
    <section className="why-us" aria-labelledby="why-us-title">
      <div className="why-us-heading">
        <span className="why-us-eyebrow">A LITTLE ABOUT US</span>

        <h2 id="why-us-title">Why Nature Holidays?</h2>

        <p>
          Based in Bengaluru and established in 2011.
          Helping you turn travel plans into shared experiences.
        </p>
      </div>

      <div className="why-us-grid">
        {reasons.map((reason) => (
          <article className="why-us-item" key={reason.number}>
            <span className="why-us-number">{reason.number}</span>

            <h3>{reason.title}</h3>

            <p>{reason.description}</p>
          </article>
        ))}
      </div>

      <div className="why-us-contact">
        <div>
          <h3>Have a trip in mind?</h3>
          <p>Let’s talk destinations, dates and possibilities.</p>
        </div>

        <Link to="/contact">
  Talk to our team
  <span aria-hidden="true">↗</span>
</Link>
      </div>
    </section>
  );
}