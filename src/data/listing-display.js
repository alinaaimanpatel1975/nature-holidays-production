
import { destinationImages } from "./destination-images";

const indicativePrices = {
  attractions: 500,
  tours: 1500,
  adventure: 1800,
  culture: 1200,
  food: 1400,
  nature: 1200,
  water: 1800,
  stays: 2500,
  transport: 800,
  groups: 1800,
};

export function getListingImage(item, destinations = []) {
  if (
    item?.image ||
    item?.coverImage ||
    item?.thumbnail ||
    item?.images?.[0]
  ) {
    return (
      item.image ||
      item.coverImage ||
      item.thumbnail ||
      item.images[0]
    );
  }

  const destination = destinations.find(
    (place) =>
      place.slug === item?.destinationSlug ||
      place.name?.toLowerCase() ===
        item?.destination?.toLowerCase()
  );

  if (destination?.image) return destination.image;

  const matchingPhoto = destinationImages.find(
    (photo) =>
      photo.destination?.toLowerCase() ===
        (item?.destination || "").toLowerCase() ||
      photo.id === item?.heroId
  );

  return matchingPhoto?.image || "";
}

export function getIndicativePrice(item) {
  // Never replace an explicitly supplied price.
  const suppliedPrice =
    item?.pricing?.startingPrice ??
    item?.startingPrice ??
    item?.price;

  if (typeof suppliedPrice === "number" && suppliedPrice > 0) {
    return {
      amount: suppliedPrice,
      label: "Listed starting price",
      isEstimate: false,
    };
  }

  const category = (
    item?.category ||
    item?.type ||
    "tours"
  ).toLowerCase();

  const amount =
    indicativePrices[category] ??
    indicativePrices.tours;

  return {
    amount,
    label: "Estimated from",
    isEstimate: true,
  };
}

export function formatINR(amount) {
  return new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0,
  }).format(amount);
}
