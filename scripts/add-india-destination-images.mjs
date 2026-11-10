
import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const DESTINATIONS_FILE = path.join(ROOT, "src/data/destinations.js");
const IMAGES_FILE = path.join(ROOT, "src/data/destination-images.js");
const OUTPUT_DIR = path.join(ROOT, "public/images/destinations");

const PHOTOS_PER_DESTINATION = 3;
const API = "https://commons.wikimedia.org/w/api.php";
const USER_AGENT = "NatureHolidaysImageImporter/1.0 (local website project)";

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function cleanHtml(value = "") {
  return String(value)
    .replace(/<[^>]*>/g, " ")
    .replace(/&amp;/g, "&")
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/&nbsp;/g, " ")
    .replace(/\s+/g, " ")
    .trim();
}

function allowedCommercialLicence(name = "") {
  return /^(CC0|CC BY(?:-SA)?(?:\s|$)|Public domain)/i.test(name.trim());
}

function extractDestinations(source) {
  const match = source.match(
    /const\s+catalogue\s*=\s*\[([\s\S]*?)\n\];/
  );

  if (!match) {
    throw new Error(
      "Could not find the catalogue array in src/data/destinations.js"
    );
  }

  const rows = [];
  const rowPattern =
    /\[\s*"([^"]+)"\s*,\s*"([^"]+)"\s*,\s*"([^"]+)"/g;

  for (const row of match[1].matchAll(rowPattern)) {
    rows.push({
      slug: row[1],
      name: row[2],
      state: row[3],
    });
  }

  return [...new Map(rows.map((item) => [item.slug, item])).values()];
}

async function searchCommons(destination) {
  const params = new URLSearchParams({
    action: "query",
    format: "json",
    generator: "search",
    gsrnamespace: "6",
    gsrsearch: `${destination.name} ${destination.state}`,
    gsrlimit: "30",
    prop: "imageinfo",
    iiprop: "url|size|mime|extmetadata",
    iiurlwidth: "1400",
    iiextmetadatafilter:
      "Artist|Credit|LicenseShortName|LicenseUrl|ImageDescription",
  });

  const response = await fetch(`${API}?${params}`, {
    headers: { "User-Agent": USER_AGENT },
  });

  if (!response.ok) {
    throw new Error(`Commons search failed: ${response.status}`);
  }

  const data = await response.json();
  return Object.values(data.query?.pages ?? {});
}

function getImageDetails(page) {
  const info = page.imageinfo?.[0];
  if (!info || !info.thumburl) return null;

  const metadata = info.extmetadata ?? {};
  const licence = cleanHtml(metadata.LicenseShortName?.value ?? "");

  if (!allowedCommercialLicence(licence)) return null;
  if (!info.mime?.startsWith("image/")) return null;
  if ((info.width ?? 0) < 900 || (info.height ?? 0) < 500) return null;

  const imageUrl = info.thumburl;
  const extension = path.extname(new URL(imageUrl).pathname).toLowerCase();

  if (![".jpg", ".jpeg", ".png", ".webp"].includes(extension)) {
    return null;
  }

  return {
    title: page.title,
    imageUrl,
    extension,
    author: cleanHtml(metadata.Artist?.value ?? "Author not listed"),
    licence,
    licenceUrl: metadata.LicenseUrl?.value ?? "",
    description: cleanHtml(metadata.ImageDescription?.value ?? ""),
    pageUrl: `https://commons.wikimedia.org/wiki/${encodeURIComponent(
      page.title.replaceAll(" ", "_")
    )}`,
  };
}

async function downloadImage(url, destinationPath) {
  const response = await fetch(url, {
    headers: { "User-Agent": USER_AGENT },
  });

  if (!response.ok) {
    throw new Error(`Image download failed: ${response.status}`);
  }

  const bytes = Buffer.from(await response.arrayBuffer());
  await fs.writeFile(destinationPath, bytes);
}

async function main() {
  const destinationSource = await fs.readFile(DESTINATIONS_FILE, "utf8");
  const imageSource = await fs.readFile(IMAGES_FILE, "utf8");

  const destinations = extractDestinations(destinationSource);

  if (!destinations.length) {
    throw new Error("No destinations were found in the catalogue.");
  }

  console.log(`Found ${destinations.length} destinations.`);
  console.log("Starting image collection...\n");

  const records = [];
  const usedTitles = new Set();
  let totalDownloaded = 0;

  for (const destination of destinations) {
    console.log(`Searching: ${destination.name}, ${destination.state}`);

    try {
      const pages = await searchCommons(destination);
      let savedForDestination = 0;

      for (const page of pages) {
        if (savedForDestination >= PHOTOS_PER_DESTINATION) break;

        const details = getImageDetails(page);
        if (!details || usedTitles.has(details.title)) continue;

        const filename =
          `${String(savedForDestination + 1).padStart(2, "0")}` +
          details.extension;

        const folder = path.join(OUTPUT_DIR, destination.slug);
        await fs.mkdir(folder, { recursive: true });

        const destinationPath = path.join(folder, filename);

        try {
          await downloadImage(details.imageUrl, destinationPath);
        } catch (error) {
          console.log(`  Skipped download: ${error.message}`);
          continue;
        }

        usedTitles.add(details.title);
        savedForDestination++;
        totalDownloaded++;

        records.push({
          id: `${destination.slug}-commons-${savedForDestination}`,
          destination: destination.name,
          name: details.description || `${destination.name} view ${savedForDestination}`,
          image: `import.meta.env.BASE_URL + "images/destinations/${destination.slug}/${filename}"`,
          alt: details.description || `${destination.name}, ${destination.state}`,
          author: details.author,
          license: details.licence,
          licenseUrl: details.licenceUrl,
          sourceUrl: details.pageUrl,
          creditUrl: details.pageUrl,
        });
      }

      console.log(`  Added ${savedForDestination} photos.`);
    } catch (error) {
      console.log(`  Could not collect photos: ${error.message}`);
    }

    await sleep(350);
  }

  const startMarker = "// AUTO-ADDED INDIA DESTINATION PHOTOS START";
  const endMarker = "// AUTO-ADDED INDIA DESTINATION PHOTOS END";

  const cleanSource = imageSource.replace(
    new RegExp(`${startMarker}[\\s\\S]*?${endMarker}\\s*`, "g"),
    ""
  );

  const declaration = cleanSource.match(
    /export\s+const\s+destinationImages\s*=\s*\[/
  );

  if (!declaration) {
    throw new Error(
      "Could not find 'export const destinationImages = [' in destination-images.js. No image-library changes were made."
    );
  }

  const arrayStart = declaration.index + declaration[0].length;
  const arrayEnd = cleanSource.indexOf("];", arrayStart);

  if (arrayEnd === -1) {
    throw new Error("Could not find the end of the destinationImages array.");
  }

  const existingItems = cleanSource
    .slice(arrayStart, arrayEnd)
    .trim()
    .replace(/,\s*$/, "");

  const generatedItems = records
    .map((record) => {
      return `  {
    id: ${JSON.stringify(record.id)},
    destination: ${JSON.stringify(record.destination)},
    name: ${JSON.stringify(record.name)},
    image: ${record.image},
    alt: ${JSON.stringify(record.alt)},
    author: ${JSON.stringify(record.author)},
    license: ${JSON.stringify(record.license)},
    licenseUrl: ${JSON.stringify(record.licenseUrl)},
    sourceUrl: ${JSON.stringify(record.sourceUrl)},
    creditUrl: ${JSON.stringify(record.creditUrl)},
  }`;
    })
    .join(",\n");

  const addition = generatedItems
    ? `\n\n  ${startMarker}\n${generatedItems}\n  ${endMarker}\n`
    : "";

  const updatedSource =
    cleanSource.slice(0, arrayStart) +
    existingItems +
    (existingItems && generatedItems ? "," : "") +
    addition +
    cleanSource.slice(arrayEnd);

  await fs.writeFile(IMAGES_FILE, updatedSource, "utf8");

  console.log("\n--------------------------------");
  console.log("IMAGE COLLECTION FINISHED");
  console.log(`Destinations scanned: ${destinations.length}`);
  console.log(`Photos downloaded: ${totalDownloaded}`);
  console.log(`Destinations with photos: ${new Set(records.map((r) => r.destination)).size}`);
  console.log(`Photos saved in: public/images/destinations/`);
  console.log("Image credits added to destination-images.js");
  console.log("--------------------------------");

  const missing = destinations.filter(
    (destination) =>
      !records.some((record) => record.destination === destination.name)
  );

  if (missing.length) {
    console.log("\nDestinations still needing manual image selection:");
    missing.forEach((item) => console.log(`- ${item.name}, ${item.state}`));
  }
}

main().catch((error) => {
  console.error("\nIMAGE IMPORT FAILED:", error);
  process.exitCode = 1;
});
