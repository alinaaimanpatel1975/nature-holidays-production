
import { packages } from "./packages";
import { destinations } from "./destinations";
import {
  indiaExtraDestinations,
  indiaExtraExperiences,
} from "./india-catalogue";

export const marketplaceCategories = [
  { id: "all", name: "Everything" },
  { id: "attractions", name: "Attractions & Tickets" },
  { id: "tours", name: "Tours & Day Trips" },
  { id: "adventure", name: "Adventure & Outdoor" },
  { id: "stays", name: "Hotels & Stays" },
  { id: "transport", name: "Transport & Transfers" },
  { id: "culture", name: "Culture & Heritage" },
  { id: "food", name: "Food & Experiences" },
  { id: "groups", name: "Group & Educational Travel" },
];

export const marketplacePackages = packages.map((item) => ({
  ...item,
  productType: "package",
  listingStatus: "enquiry-only",
  category: "tours",
  priceStatus: item.pricing?.status || "quote-required",
}));

const originalExperiences = destinations.flatMap((destination) =>
  (destination.experiences || []).map((experience) => ({
    ...experience,
    productType: "experience",
    destination: destination.name,
    destinationSlug: destination.slug,
    state: destination.state,
    listingStatus: "enquiry-only",
    priceStatus: "quote-required",
  }))
);

const uniqueById = (items) => {
  const seen = new Set();

  return items.filter((item) => {
    if (!item.id || seen.has(item.id)) return false;
    seen.add(item.id);
    return true;
  });
};

export const marketplaceExperiences = uniqueById([
  ...originalExperiences,
  ...indiaExtraExperiences,
]);

export const marketplaceDestinations = uniqueById([
  ...destinations,
  ...indiaExtraDestinations,
]);

export const marketplaceListings = uniqueById([
  ...marketplacePackages,
  ...marketplaceExperiences,
]);

export function getMarketplaceListing(id) {
  return marketplaceListings.find(
    (item) => String(item.id) === String(id)
  );
}

export function getMarketplaceDestination(slug) {
  return marketplaceDestinations.find(
    (item) => item.slug === slug
  );
}

export function getMarketplaceCategory(categoryId) {
  return marketplaceCategories.find(
    (item) => item.id === categoryId
  );
}

export function getMarketplaceListingsByCategory(categoryId) {
  if (!categoryId || categoryId === "all") {
    return marketplaceListings;
  }

  return marketplaceListings.filter(
    (item) => item.category === categoryId
  );
}

export function getMarketplaceListingsByDestination(slug) {
  return marketplaceListings.filter(
    (item) => item.destinationSlug === slug
  );
}
