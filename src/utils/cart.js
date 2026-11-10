
// One shared cart contract for the experience page, cart page and floating badge.

export const CART_STORAGE_KEY = "nature-holidays-cart";

const LEGACY_CART_KEYS = ["natureHolidaysCart"];

function isBrowser() {
  return (
    typeof window !== "undefined" &&
    typeof window.localStorage !== "undefined"
  );
}

function safeNumber(value) {
  if (value === null || value === undefined || value === "") {
    return null;
  }

  const number = Number(value);

  return Number.isFinite(number) && number > 0
    ? number
    : null;
}

export function normalizeCartItem(item = {}) {
  const quantityValue = Number(item.quantity ?? item.people ?? 1);

  const quantity = Number.isFinite(quantityValue)
    ? Math.min(20, Math.max(1, Math.floor(quantityValue)))
    : 1;

  const storedTotal =
    item.total !== null &&
    item.total !== undefined &&
    quantity > 0
      ? Number(item.total) / quantity
      : null;

  const rawUnitPrice = safeNumber(
    item.unitPrice ?? item.pricePerPerson ?? storedTotal
  );

  const pricingStatus =
    item.pricingStatus ||
    (rawUnitPrice === null ? "quote-required" : "listed");

  const unitPrice =
    pricingStatus === "listed" ? rawUnitPrice : null;

  const cartId =
    item.cartId ||
    item.id ||
    `legacy-${item.listingId || item.name || "experience"}-${item.date || "no-date"}-${item.packageName || item.option || "standard"}`;

  return {
    cartId: String(cartId),

    listingId: String(
      item.listingId || item.experienceId || item.id || cartId
    ),

    name: item.name || item.title || "Selected experience",

    destination: item.destination || item.location || "India",

    productType: item.productType || "experience",

    packageName:
      item.packageName || item.option || "Standard experience",

    date: item.date || "",

    quantity,
    people: quantity,

    unitPrice,

    total: unitPrice === null ? null : unitPrice * quantity,

    pricingStatus:
      unitPrice === null ? "quote-required" : "listed",

    image: item.image || item.coverImage || "",
  };
}

function parseItems(raw) {
  try {
    const parsed = JSON.parse(raw || "[]");

    return Array.isArray(parsed)
      ? parsed.map(normalizeCartItem)
      : [];
  } catch {
    return [];
  }
}

function notifyCartChanged() {
  if (!isBrowser()) return;

  window.dispatchEvent(
    new Event("nature-holidays-cart-updated")
  );
}

export function readCart() {
  if (!isBrowser()) return [];

  let canonical = [];
  let legacyItems = [];
  let canonicalKeyMissing = false;

  try {
    canonicalKeyMissing =
      window.localStorage.getItem(CART_STORAGE_KEY) === null;

    canonical = parseItems(
      window.localStorage.getItem(CART_STORAGE_KEY)
    );

    legacyItems = LEGACY_CART_KEYS.flatMap((key) =>
      parseItems(window.localStorage.getItem(key))
    );
  } catch {
    return [];
  }

  const combined = [...canonical];

  const knownIds = new Set(
    canonical.map((item) => item.cartId)
  );

  legacyItems.forEach((item) => {
    if (!knownIds.has(item.cartId)) {
      combined.push(item);
      knownIds.add(item.cartId);
    }
  });

  // Migrate old cart data into the shared storage format.
  if (legacyItems.length || canonicalKeyMissing) {
    try {
      window.localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(combined)
      );

      LEGACY_CART_KEYS.forEach((key) =>
        window.localStorage.removeItem(key)
      );
    } catch {
      // Keep the in-memory cart usable.
    }
  }

  return combined;
}

export function writeCart(items) {
  const normalized = Array.isArray(items)
    ? items.map(normalizeCartItem)
    : [];

  if (isBrowser()) {
    try {
      window.localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(normalized)
      );

      LEGACY_CART_KEYS.forEach((key) =>
        window.localStorage.removeItem(key)
      );

      notifyCartChanged();
    } catch {
      // The current page can still use the returned data.
    }
  }

  return normalized;
}

export function addCartItem(item) {
  const normalized = normalizeCartItem({
    ...item,
    cartId:
      item.cartId ||
      `${item.listingId || "experience"}-${Date.now()}`,
  });

  return writeCart([...readCart(), normalized]);
}

export function updateCartItem(cartId, changes) {
  return writeCart(
    readCart().map((item) =>
      item.cartId === cartId
        ? { ...item, ...changes }
        : item
    )
  );
}

export function removeCartItem(cartId) {
  return writeCart(
    readCart().filter((item) => item.cartId !== cartId)
  );
}

export function subscribeToCart(callback) {
  if (!isBrowser()) return () => {};

  const refresh = () => callback(readCart());

  window.addEventListener("storage", refresh);

  window.addEventListener(
    "nature-holidays-cart-updated",
    refresh
  );

  return () => {
    window.removeEventListener("storage", refresh);

    window.removeEventListener(
      "nature-holidays-cart-updated",
      refresh
    );
  };
}

export function getDistinctExperienceCount(items = readCart()) {
  return new Set(
    items.map((item) => item.listingId || item.cartId)
  ).size;
}

export function getCartTotals(items = readCart()) {
  const pricedItems = items.filter(
    (item) =>
      item.pricingStatus === "listed" &&
      safeNumber(item.unitPrice)
  );

  const total = pricedItems.reduce(
    (sum, item) => sum + item.unitPrice * item.quantity,
    0
  );

  return {
    total,

    hasPricedItems: pricedItems.length > 0,

    hasQuoteItems: items.some(
      (item) =>
        item.pricingStatus !== "listed" ||
        !safeNumber(item.unitPrice)
    ),
  };
}
