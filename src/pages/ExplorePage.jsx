
import { useState } from "react";
import { Link, useParams } from "react-router-dom";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";
import "./ExplorePage.css";

const exploreCategories = {
  "things-to-do": {
    title: "Things to do",
    subtitle: "Find a good reason to get out.",
    description:
      "A journey can be a roller coaster, a quiet walk through an old city, a day surrounded by nature or a table full of local food. Explore outing options and find experiences to add to your itinerary.",
    image: import.meta.env.BASE_URL + "images/things-to-do.jpg",
    groups: ["All options", "Theme Parks", "Resort Outings", "Sightseeing"],
    items: [
      {
        name: "Wonderla Parks & Resort",
        group: "Theme Parks",
        description:
          "Enquire about a park outing and available arrangements.",
      },
      {
        name: "GRS Fantasy Park",
        group: "Theme Parks",
        description:
          "Ask about entry arrangements and group outing options.",
      },
      {
        name: "Snow City",
        group: "Theme Parks",
        description:
          "Check current visit options for your preferred date.",
      },
      {
        name: "Fun World",
        group: "Theme Parks",
        description:
          "Explore an amusement-park outing with your group.",
      },
      {
        name: "Jollywood Studios",
        group: "Theme Parks",
        description:
          "Request current outing options and entry information.",
      },
      {
        name: "Elim Resort",
        group: "Resort Outings",
        description:
          "Ask about available day-out packages and inclusions.",
      },
      {
        name: "Guhantara Resort",
        group: "Resort Outings",
        description:
          "Enquire about a resort outing for your group.",
      },
      {
        name: "Dharamshi Resort",
        group: "Resort Outings",
        description:
          "Request the current day-out arrangements and quote.",
      },
      {
        name: "Greenwood Resort",
        group: "Resort Outings",
        description:
          "Check availability and outing options with the team.",
      },
      {
        name: "Dreamland Resort",
        group: "Resort Outings",
        description:
          "Ask about group sizes, dates and package inclusions.",
      },
      {
        name: "Avaniya Adventures Resort",
        group: "Resort Outings",
        description:
          "Enquire about available outings and activities.",
      },
      {
        name: "Namma Bengaluru Rounds",
        group: "Sightseeing",
        description:
          "Plan a city route around gardens, museums and landmarks.",
      },
      {
        name: "Nandi Hills and Isha Temple",
        group: "Sightseeing",
        description:
          "Explore this outing listed in the brochure.",
      },
      {
        name: "Mysore, KRS Garden",
        group: "Sightseeing",
        description:
          "Request the sightseeing route and travel arrangements.",
      },
      {
        name: "Hampi & Pattadakal",
        group: "Sightseeing",
        description:
          "Ask about a heritage journey through these destinations.",
      },
    ],
    experienceIdeas: [
      {
        number: "01",
        title: "Adventure & outdoors",
        description:
          "Build an active day around outdoor exploration, nature trails and adventure-focused outings. Ask about the activity, age requirements, safety arrangements and what is included.",
        tags: ["Outdoor exploration", "Adventure outings", "Nature trails"],
      },
      {
        number: "02",
        title: "Water & coastal experiences",
        description:
          "Think beyond the usual sightseeing route. Explore beach destinations, coastal outings and water-based experiences. Confirm seasonal conditions and activity availability before planning.",
        tags: ["Beach days", "Water experiences", "Coastal trips"],
      },
      {
        number: "03",
        title: "Wildlife & nature",
        description:
          "Make space for forests, landscapes and time outdoors. Ask about permitted visitor areas, guided experiences, travel time and any seasonal restrictions.",
        tags: ["Nature visits", "Wildlife experiences", "Scenic escapes"],
      },
      {
        number: "04",
        title: "Culture & heritage",
        description:
          "Explore historic places, architecture, local traditions and cultural landmarks. A little context can turn a sightseeing stop into a memorable experience.",
        tags: ["Heritage sites", "City walks", "Architecture"],
      },
      {
        number: "05",
        title: "Food & local discoveries",
        description:
          "Discover a destination through its food, markets and neighbourhoods. Ask about local food experiences and whether they can fit into your travel itinerary.",
        tags: ["Local cuisine", "Markets", "Food experiences"],
      },
      {
        number: "06",
        title: "Family & group outings",
        description:
          "Plan an outing around the people travelling with you. Consider travel time, age suitability, accessibility, meal arrangements and how much time your group wants to spend at each stop.",
        tags: ["Family outings", "School trips", "Group experiences"],
      },
    ],
    destinationIdeas: [
      {
        name: "Bengaluru",
        state: "Karnataka",
        description:
          "Plan a city day around gardens, landmarks and nearby sightseeing.",
      },
      {
        name: "Mysore",
        state: "Karnataka",
        description:
          "Explore a heritage-focused itinerary and nearby attractions.",
      },
      {
        name: "Hampi",
        state: "Karnataka",
        description:
          "Consider a journey centred on historic architecture and heritage.",
      },
      {
        name: "Ooty",
        state: "Tamil Nadu",
        description:
          "Build a hill-station itinerary around scenic stops and relaxed exploration.",
      },
      {
        name: "Munnar",
        state: "Kerala",
        description:
          "Explore a landscape-focused trip with time for scenic viewpoints.",
      },
      {
        name: "Goa",
        state: "Goa",
        description:
          "Consider a coastal itinerary with beach time and local discoveries.",
      },
    ],
    planningTips: [
      {
        title: "Start with your group",
        description:
          "The best outing for a family may be different from one for a school, college or corporate group. Share the number of travellers and their age groups.",
      },
      {
        title: "Choose your travel pace",
        description:
          "Decide whether you want a relaxed day, several sightseeing stops or an activity-filled itinerary. Leave room for travel between locations.",
      },
      {
        title: "Check the details",
        description:
          "Ask about opening times, entry arrangements, inclusions, transport and any restrictions that may apply on your chosen date.",
      },
      {
        title: "Plan around the season",
        description:
          "Weather and seasonal conditions can affect outdoor experiences. Confirm the best arrangements for your intended travel dates.",
      },
    ],
  },

  accommodation: {
    title: "Accommodation",
    subtitle: "Find your place to pause.",
    description:
      "The right stay can shape the entire trip. Whether you need a hotel for a city visit, a resort for a group outing or a quieter place to unwind, start with the kind of stay that suits your journey.",
    image: import.meta.env.BASE_URL + "images/accommodation.jpg",
    groups: [
      "All options",
      "Hotels",
      "Resorts",
      "Group Stays",
      "Unique Stays",
    ],
    items: [
      {
        name: "Hotel reservation assistance",
        group: "Hotels",
        description:
          "Share your destination, dates, room count and preferred hotel category.",
      },
      {
        name: "Resort booking enquiries",
        group: "Resorts",
        description:
          "Ask about available resorts and whether you need a day outing or an overnight stay.",
      },
      {
        name: "School & college group stays",
        group: "Group Stays",
        description:
          "Discuss group size, room arrangements, meal requirements and your itinerary.",
      },
      {
        name: "Corporate & group accommodation",
        group: "Group Stays",
        description:
          "Share your team's stay requirements and ask about suitable arrangements.",
      },
      {
        name: "Homestay enquiries",
        group: "Unique Stays",
        description:
          "Ask about homestay options, location, room arrangements and facilities for your trip.",
      },
      {
        name: "Villa and private stay enquiries",
        group: "Unique Stays",
        description:
          "Discuss your group size, privacy requirements, preferred location and stay dates.",
      },
      {
        name: "Heritage stay enquiries",
        group: "Unique Stays",
        description:
          "Ask about heritage-style properties and the facilities available for your stay.",
      },
      {
        name: "Family accommodation planning",
        group: "Group Stays",
        description:
          "Share the number of adults and children, room preferences and any special requirements.",
      },
    ],
    stayTypes: [
      {
        number: "01",
        title: "Hotels",
        description:
          "A practical choice for city visits, sightseeing trips and short stays. Compare location, room categories, meal options and the facilities you actually need.",
        tags: ["City breaks", "Short stays", "Convenience"],
      },
      {
        number: "02",
        title: "Resorts",
        description:
          "Consider a resort when the stay itself is part of the holiday. Ask about room options, dining, recreational facilities and whether activities are included or charged separately.",
        tags: ["Leisure trips", "Relaxation", "Group outings"],
      },
      {
        number: "03",
        title: "Homestays",
        description:
          "Homestays can offer a different way to experience a destination. Check the location, host arrangements, meal facilities, privacy and available amenities.",
        tags: ["Local experiences", "Small groups", "Longer stays"],
      },
      {
        number: "04",
        title: "Villas & private stays",
        description:
          "A private property may suit families or groups who prefer shared spaces. Confirm occupancy limits, bedroom arrangements, house rules and what is included.",
        tags: ["Families", "Groups", "Private spaces"],
      },
      {
        number: "05",
        title: "Heritage stays",
        description:
          "For travellers interested in history and character, consider heritage-style accommodation. Ask about accessibility, modern facilities and the property's location.",
        tags: ["Culture", "Architecture", "Slow travel"],
      },
      {
        number: "06",
        title: "Hill-station retreats",
        description:
          "For a mountain getaway, consider how close the property is to your planned stops. Check road access, weather conditions, heating arrangements and transport.",
        tags: ["Hill destinations", "Nature", "Scenic stays"],
      },
      {
        number: "07",
        title: "Beach stays",
        description:
          "Think about whether you want to be near the beach, close to restaurants or somewhere quieter. Confirm the actual distance and available facilities.",
        tags: ["Coastal trips", "Leisure", "Weekend breaks"],
      },
      {
        number: "08",
        title: "Unique stays",
        description:
          "Treehouses and other distinctive properties can make a trip feel different. Check access, safety arrangements, facilities and suitability for everyone travelling.",
        tags: ["Nature", "Special trips", "Unusual stays"],
      },
    ],
    destinationIdeas: [
      {
        name: "Coorg",
        state: "Karnataka",
        description:
          "Consider a nature-focused stay and check travel distances between your planned stops.",
      },
      {
        name: "Ooty",
        state: "Tamil Nadu",
        description:
          "Compare hill-station properties by location, access and seasonal comfort.",
      },
      {
        name: "Munnar",
        state: "Kerala",
        description:
          "Look at the location of your stay in relation to the landscapes and places you want to visit.",
      },
      {
        name: "Alleppey",
        state: "Kerala",
        description:
          "Explore the different stay styles available around the backwater region.",
      },
      {
        name: "Goa",
        state: "Goa",
        description:
          "Decide whether you prefer a beach-side location, a quieter area or a base near local attractions.",
      },
      {
        name: "Mysore",
        state: "Karnataka",
        description:
          "Consider a convenient base for city sightseeing and nearby excursions.",
      },
    ],
    planningTips: [
      {
        title: "Know your room requirements",
        description:
          "Share the number of rooms, adults, children and preferred room arrangements before requesting a quote.",
      },
      {
        title: "Compare the location",
        description:
          "A property may look close on a map but still require significant travel time. Check distances to your planned attractions and transport points.",
      },
      {
        title: "Ask what is included",
        description:
          "Confirm meals, taxes, extra beds, activities, parking, cancellation terms and any additional charges directly before booking.",
      },
      {
        title: "Mention special requirements",
        description:
          "Let the team know about accessibility needs, dietary requirements, children, senior travellers or other important preferences.",
      },
    ],
  },

  transport: {
    title: "Transport options",
    subtitle: "Let's get you there.",
    description:
      "Enquire about ticket-booking assistance and transport arrangements for your Nature Holidays trip.",
    image: import.meta.env.BASE_URL + "images/transport.jpg",
    groups: ["All options", "Flights", "Trains", "Buses", "Tour Transport"],
    items: [
      {
        name: "Flight ticket-booking assistance",
        group: "Flights",
        description:
          "Share your departure city, destination, dates and passenger count.",
      },
      {
        name: "Train ticket-booking assistance",
        group: "Trains",
        description:
          "Tell the team your route, travel date and preferred travel class.",
      },
      {
        name: "Bus ticket-booking assistance",
        group: "Buses",
        description:
          "Ask about available options for your route and travel date.",
      },
      {
        name: "Transport for your tour",
        group: "Tour Transport",
        description:
          "Discuss pickup points, group size and transport requirements for your package.",
      },
    ],
  },
};

const categoryLinks = [
  { id: "things-to-do", name: "Things to do" },
  { id: "accommodation", name: "Accommodation" },
  { id: "transport", name: "Transport" },
];

function GuideCard({ number, title, description, tags }) {
  return (
    <article className="explore-guide-card">
      <span className="explore-guide-number">{number}</span>
      <h3>{title}</h3>
      <p>{description}</p>

      <div className="explore-guide-tags">
        {tags.map((tag) => (
          <span key={tag}>{tag}</span>
        ))}
      </div>
    </article>
  );
}

function InformationSection({ eyebrow, title, description, children }) {
  return (
    <section className="explore-information-section">
      <div className="explore-information-heading">
        <span>{eyebrow}</span>
        <h2>{title}</h2>
        {description && <p>{description}</p>}
      </div>

      {children}
    </section>
  );
}

function ExploreContent({ category, data }) {
  const [query, setQuery] = useState("");
  const [group, setGroup] = useState("All options");

  const isThingsToDo = category === "things-to-do";
  const isAccommodation = category === "accommodation";

  const visibleItems = data.items.filter((item) => {
    const matchesGroup =
      group === "All options" || item.group === group;

    const text =
      `${item.name} ${item.group} ${item.description}`.toLowerCase();

    return matchesGroup && text.includes(query.trim().toLowerCase());
  });

  const guideCards = isThingsToDo
    ? data.experienceIdeas
    : data.stayTypes;

  return (
    <>
      <section className="explore-page-banner">
        <img src={data.image} alt="" />
        <div className="explore-page-banner-shade" />

        <div className="explore-page-banner-content">
          <span>EXPLORE NATURE HOLIDAYS</span>
          <h1>{data.title}</h1>
          <p>{data.subtitle}</p>
        </div>
      </section>

      <main className="explore-page-content">
        <nav className="explore-page-tabs" aria-label="Explore categories">
          {categoryLinks.map((item) => (
            <Link
              key={item.id}
              to={`/explore/${item.id}`}
              aria-current={category === item.id ? "page" : undefined}
              className={
                category === item.id
                  ? "explore-page-tab explore-page-tab--active"
                  : "explore-page-tab"
              }
            >
              {item.name}
            </Link>
          ))}
        </nav>

        <div className="explore-page-introduction">
          <span className="explore-section-eyebrow">
            {isThingsToDo
              ? "MAKE THE JOURNEY YOURS"
              : isAccommodation
                ? "STAYS THAT SUIT YOUR JOURNEY"
                : "GETTING AROUND"}
          </span>

          <h2>
            {isThingsToDo
              ? "More than places. Moments."
              : isAccommodation
                ? "A stay for every kind of traveller."
                : "The journey starts here."}
          </h2>

          <p>{data.description}</p>
        </div>

        {(isThingsToDo || isAccommodation) && (
          <InformationSection
            eyebrow={isThingsToDo ? "FIND YOUR KIND OF DAY" : "FIND YOUR KIND OF STAY"}
            title={
              isThingsToDo
                ? "What are you in the mood for?"
                : "Explore different ways to stay."
            }
            description={
              isThingsToDo
                ? "Use these ideas to shape your itinerary. Ask us which arrangements can be explored for your trip."
                : "These are accommodation styles to consider when planning. Actual properties, facilities and availability must be confirmed."
            }
          >
            <div className="explore-guide-grid">
              {guideCards.map((item) => (
                <GuideCard
                  key={item.number}
                  number={item.number}
                  title={item.title}
                  description={item.description}
                  tags={item.tags}
                />
              ))}
            </div>
          </InformationSection>
        )}

        <section className="explore-listing-section">
          <div className="explore-listing-heading">
            <div>
              <span className="explore-section-eyebrow">
                {isThingsToDo
                  ? "CURRENT BROCHURE OPTIONS"
                  : isAccommodation
                    ? "STAY ENQUIRY SERVICES"
                    : "TRAVEL ARRANGEMENTS"}
              </span>

              <h2>
                {isThingsToDo
                  ? "Explore available outing options."
                  : isAccommodation
                    ? "Tell us what kind of stay you need."
                    : "Explore transport assistance."}
              </h2>

              <p>
                {isThingsToDo
                  ? "Browse the named outings and sightseeing options listed here."
                  : isAccommodation
                    ? "Share your requirements so the team can discuss suitable arrangements."
                    : "Share your route and travel details with the team."}
              </p>
            </div>
          </div>

          <label className="explore-page-search">
            <span>Search {data.title.toLowerCase()}</span>
            <input
              type="search"
              value={query}
              placeholder="Search by name or type"
              onChange={(event) => setQuery(event.target.value)}
            />
          </label>

          <div className="explore-page-filters" aria-label="Filter options">
            {data.groups.map((item) => (
              <button
                type="button"
                key={item}
                aria-pressed={group === item}
                className={group === item ? "explore-filter-active" : ""}
                onClick={() => setGroup(item)}
              >
                {item}
              </button>
            ))}
          </div>

          <p className="explore-page-count" role="status">
            {visibleItems.length}{" "}
            {visibleItems.length === 1 ? "option" : "options"}
          </p>

          <div className="explore-options-grid">
            {visibleItems.map((item, index) => (
              <article className="explore-option-card" key={item.name}>
                <span className="explore-option-category">
                  {item.group}
                </span>

                <span className="explore-option-number">
                  {String(index + 1).padStart(2, "0")}
                </span>

                <h3>{item.name}</h3>
                <p>{item.description}</p>

                <Link
                  to={`/contact?package=${encodeURIComponent(item.name)}`}
                >
                  Enquire now
                  <span aria-hidden="true">↗</span>
                </Link>
              </article>
            ))}
          </div>

          {visibleItems.length === 0 && (
            <div className="explore-empty">
              <h3>No matching options.</h3>
              <p>Try another search or clear your filters.</p>

              <button
                type="button"
                onClick={() => {
                  setQuery("");
                  setGroup("All options");
                }}
              >
                Clear filters
              </button>
            </div>
          )}
        </section>

        {(isThingsToDo || isAccommodation) && (
          <>
            <InformationSection
              eyebrow="A LITTLE INSPIRATION"
              title={
                isThingsToDo
                  ? "Where could the day take you?"
                  : "Choose a destination, then choose your stay."
              }
              description={
                isThingsToDo
                  ? "A few destinations to help you start imagining your itinerary."
                  : "Think about the places you want to visit before choosing where to stay."
              }
            >
              <div className="explore-destination-grid">
                {data.destinationIdeas.map((destination) => (
                  <article
                    className="explore-destination-card"
                    key={destination.name}
                  >
                    <span>{destination.state}</span>
                    <h3>{destination.name}</h3>
                    <p>{destination.description}</p>
                    <Link to={`/destinations/${destination.name.toLowerCase()}`}>
                      Explore destination
                      <span aria-hidden="true">↗</span>
                    </Link>
                  </article>
                ))}
              </div>
            </InformationSection>

            <InformationSection
              eyebrow="BEFORE YOU MAKE AN ENQUIRY"
              title={
                isThingsToDo
                  ? "A little planning goes a long way."
                  : "The details that make a stay work."
              }
              description={
                isThingsToDo
                  ? "A few details can help the team understand the kind of experience you're looking for."
                  : "The more clearly you describe your requirements, the easier it is to discuss suitable options."
              }
            >
              <div className="explore-planning-grid">
                {data.planningTips.map((tip, index) => (
                  <article className="explore-planning-card" key={tip.title}>
                    <span>
                      {String(index + 1).padStart(2, "0")}
                    </span>
                    <div>
                      <h3>{tip.title}</h3>
                      <p>{tip.description}</p>
                    </div>
                  </article>
                ))}
              </div>
            </InformationSection>
          </>
        )}

        <p className="explore-availability-note">
          All options are enquiry-based. Availability, entry arrangements,
          property details, inclusions and prices require confirmation from
          Nature Holidays. Submitting an enquiry does not reserve a ticket,
          room or journey.
        </p>

        <section className="explore-help-banner">
          <div>
            <span>LET'S PLAN SOMETHING GOOD</span>
            <h2>Not sure where to start?</h2>
            <p>
              Tell us where you want to go, who's travelling and what
              you have in mind. We'll help you explore the next steps.
            </p>
          </div>

          <Link to="/contact">
            Talk to our team
            <span aria-hidden="true">↗</span>
          </Link>
        </section>
      </main>
    </>
  );
}

export default function ExplorePage() {
  const { category } = useParams();
  const data = exploreCategories[category];

  return (
    <>
      <Navbar />

      {data ? (
        <ExploreContent
          key={category}
          category={category}
          data={data}
        />
      ) : (
        <main className="explore-page-content explore-empty">
          <h1>Let's find what you're looking for.</h1>
          <Link to="/explore/things-to-do">
            Explore things to do ↗
          </Link>
        </main>
      )}

      <Footer />
    </>
  );
}
