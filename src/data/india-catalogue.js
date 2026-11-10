
import { destinationImages } from "./destination-images";

const places = [
  ["delhi", "New Delhi", "Delhi", "Capital city, monuments and food"],
  ["varanasi", "Varanasi", "Uttar Pradesh", "Sacred riverfront and heritage"],
  ["rishikesh", "Rishikesh", "Uttarakhand", "Yoga, rivers and adventure"],
  ["haridwar", "Haridwar", "Uttarakhand", "Spiritual journeys and riverfront"],
  ["shimla", "Shimla", "Himachal Pradesh", "Mountain escapes and colonial heritage"],
  ["manali", "Manali", "Himachal Pradesh", "Himalayan adventure and nature"],
  ["dharamshala", "Dharamshala", "Himachal Pradesh", "Mountain culture and monasteries"],
  ["amritsar", "Amritsar", "Punjab", "Sikh heritage and Punjabi food"],
  ["chandigarh", "Chandigarh", "Chandigarh", "Architecture, gardens and city life"],
  ["jodhpur", "Jodhpur", "Rajasthan", "Blue city and desert heritage"],
  ["udaipur", "Udaipur", "Rajasthan", "Lakes, palaces and romantic escapes"],
  ["jaisalmer", "Jaisalmer", "Rajasthan", "Golden fort and desert experiences"],
  ["pushkar", "Pushkar", "Rajasthan", "Sacred lake and cultural markets"],
  ["ranthambore", "Ranthambore", "Rajasthan", "Wildlife and nature"],
  ["mumbai", "Mumbai", "Maharashtra", "Coastal city and cultural experiences"],
  ["pune", "Pune", "Maharashtra", "Heritage, food and hill escapes"],
  ["lonavala", "Lonavala", "Maharashtra", "Hill stations and waterfalls"],
  ["mahabaleshwar", "Mahabaleshwar", "Maharashtra", "Valleys, viewpoints and strawberry farms"],
  ["ajanta", "Ajanta Caves", "Maharashtra", "Ancient art and heritage"],
  ["ellora", "Ellora Caves", "Maharashtra", "Rock-cut architecture and history"],
  ["ahmedabad", "Ahmedabad", "Gujarat", "Architecture, textiles and food"],
  ["kutch", "Kutch", "Gujarat", "White desert and local culture"],
  ["dwarka", "Dwarka", "Gujarat", "Coastal temples and pilgrimage"],
  ["statue-of-unity", "Kevadia", "Gujarat", "Monument and nature attractions"],
  ["hyderabad", "Hyderabad", "Telangana", "Fortresses, food and city culture"],
  ["visakhapatnam", "Visakhapatnam", "Andhra Pradesh", "Beaches and coastal viewpoints"],
  ["havelock", "Swaraj Dweep", "Andaman and Nicobar Islands", "Island beaches and marine life"],
  ["port-blair", "Sri Vijaya Puram", "Andaman and Nicobar Islands", "Island history and coastal experiences"],
  ["darjeeling", "Darjeeling", "West Bengal", "Tea gardens and Himalayan views"],
  ["sundarbans", "Sundarbans", "West Bengal", "Mangrove forests and wildlife"],
];

const activityTemplates = [
  {
    suffix: "city-and-heritage-tour",
    category: "culture",
    title: (name) => `${name} Heritage & City Tour`,
    duration: "Approx. 4 hours",
    description: (name) =>
      `Discover the landmarks, local history, architecture and neighbourhoods of ${name} with a guided city exploration.`,
  },
  {
    suffix: "local-food-trail",
    category: "food",
    title: (name) => `${name} Local Food Trail`,
    duration: "Approx. 3 hours",
    description: (name) =>
      `Explore regional flavours, traditional dishes and local food culture in ${name}. Specific stops depend on operator confirmation.`,
  },
  {
    suffix: "nature-and-outdoor-experience",
    category: "adventure",
    title: (name) => `${name} Nature & Outdoor Experience`,
    duration: "Approx. 4 hours",
    description: (name) =>
      `Explore the natural surroundings of ${name}. Routes, activities, weather restrictions and operator availability require confirmation.`,
  },
  {
    suffix: "guided-highlights",
    category: "attractions",
    title: (name) => `${name} Highlights & Sightseeing`,
    duration: "Approx. 3 hours",
    description: (name) =>
      `Visit notable sights and discover what makes ${name} distinctive. Final stops, entry tickets and timings require confirmation.`,
  },
];

const slugify = (value) =>
  value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");

export const indiaExtraDestinations = places.map(
  ([slug, name, state, theme]) => ({
    slug,
    name,
    state,
    theme,
    introduction: `Explore ${name}, discover its local character and find experiences worth adding to your India itinerary.`,
    listingStatus: "enquiry-only",
  })
);

export const indiaExtraExperiences = places.flatMap(
  ([slug, name, state]) =>
    activityTemplates.map((template) => {
      const matchingImage = destinationImages.find(
        (photo) =>
          photo.destination?.toLowerCase() === name.toLowerCase()
      );

      return {
        id: `${slug}-${template.suffix}`,
        slug: `${slug}-${template.suffix}`,
        name: template.title(name),
        title: template.title(name),
        destination: name,
        destinationSlug: slug,
        state,
        category: template.category,
        type: template.category,
        description: template.description(name),
        overview: template.description(name),
        duration: {
          label: template.duration,
          hours: 3,
        },
        image: matchingImage?.image || "",
        productType: "experience",
        listingStatus: "enquiry-only",
        priceStatus: "quote-required",
        pricing: {
          status: "quote-required",
          currency: "INR",
          startingPrice: null,
        },
        booking: {
          mode: "request",
          confirmation: "Requires operator confirmation.",
        },
        inclusions: [],
        exclusions: [],
        cancellation: "Confirm cancellation terms before booking.",
        sourceStatus: "editorial-catalogue",
      };
    })
);
