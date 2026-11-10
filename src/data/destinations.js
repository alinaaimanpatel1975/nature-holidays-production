
import { destinationImages } from "./destination-images";

const catalogue = [
  // KARNATAKA
  ["mysore", "Mysore", "Karnataka", "Palaces & heritage", "Royal architecture, gardens and cultural landmarks.", "Mysore palace and heritage city tour;Chamundi Hills half-day excursion;Mysore local food and market walk"],
  ["coorg", "Coorg", "Karnataka", "Coffee estates & nature", "Coffee plantations, waterfalls and Western Ghats landscapes.", "Coorg coffee plantation walk;Abbey Falls and Madikeri day trip;Dubare nature and river experience"],
  ["chikmagalur", "Chikmagalur", "Karnataka", "Coffee hills & viewpoints", "Mountain roads, coffee estates and scenic viewpoints.", "Mullayanagiri and Baba Budangiri day trip;Coffee estate guided experience;Chikmagalur waterfall excursion"],
  ["hampi", "Hampi", "Karnataka", "Temples & historic ruins", "Discover the remarkable ruins and architecture of the Vijayanagara Empire.", "Hampi UNESCO heritage day tour;Hampi sunrise and landscape excursion;Coracle river experience"],
  ["gokarna", "Gokarna", "Karnataka", "Beaches & coastal escapes", "Coastal trails, beaches and relaxed seaside experiences.", "Gokarna beach-hopping day trip;Half-day coastal trek;Mahabaleshwar Temple and town heritage walk"],
  ["dandeli", "Dandeli", "Karnataka", "Rivers & outdoor adventures", "Forest landscapes, river scenery and outdoor experiences.", "Kali River nature excursion;Dandeli forest day experience;Guided birdwatching and wildlife walk"],
  ["udupi", "Udupi", "Karnataka", "Temples & coastal culture", "Temple heritage, beaches and regional cuisine.", "Udupi temple and heritage tour;Malpe beach day trip;St Mary's Island sightseeing excursion"],
  ["bengaluru", "Bengaluru", "Karnataka", "City life & culture", "Explore gardens, historic sites, markets and the city's food scene.", "Bengaluru city sightseeing day tour;Lalbagh and Cubbon Park half-day visit;Bengaluru heritage and food walk"],
  ["mangaluru", "Mangaluru", "Karnataka", "Coastal heritage", "Beaches, temples, local markets and coastal cuisine.", "Mangaluru city sightseeing tour;Panambur beach half-day visit;Coastal food and culture walk"],

  // TAMIL NADU
  ["ooty", "Ooty", "Tamil Nadu", "Hills & gardens", "Discover lakes, gardens and mountain scenery in the Nilgiri hills.", "Ooty sightseeing day tour;Nilgiri Mountain Railway experience;Ooty tea factory and plantation visit"],
  ["kodaikanal", "Kodaikanal", "Tamil Nadu", "Lakes & mountain views", "Lakeside scenery, hill viewpoints and walks in Kodaikanal.", "Kodaikanal sightseeing day tour;Pillar Rocks and forest viewpoint excursion;Kodaikanal lake and local market walk"],
  ["chennai", "Chennai", "Tamil Nadu", "Coastal city & heritage", "A city of historic architecture, beaches and South Indian culture.", "Chennai city sightseeing day tour;Marina Beach and heritage half-day walk;Mylapore temple and culture tour"],
  ["mahabalipuram", "Mahabalipuram", "Tamil Nadu", "UNESCO heritage & coast", "Ancient rock-cut monuments and coastal heritage.", "Mahabalipuram UNESCO monuments day tour;Shore Temple and Five Rathas visit;Mahabalipuram sculpture heritage walk"],
  ["madurai", "Madurai", "Tamil Nadu", "Temple heritage", "Explore historic temples, architecture and traditional markets.", "Madurai Meenakshi Temple heritage tour;Madurai food and market walk;Madurai city sightseeing half-day tour"],
  ["rameswaram", "Rameswaram", "Tamil Nadu", "Pilgrimage & coastal landscapes", "Sacred sites, coastal scenery and historic landmarks.", "Rameswaram temple and heritage tour;Dhanushkodi day excursion;Pamban Bridge sightseeing trip"],
  ["thanjavur", "Thanjavur", "Tamil Nadu", "Chola heritage & art", "Discover monumental temples, classical art and cultural history.", "Brihadeeswarar Temple heritage tour;Thanjavur art and craft experience;Thanjavur cultural sightseeing day trip"],
  ["kanyakumari", "Kanyakumari", "Tamil Nadu", "Coastal landmarks", "Where India's southern coastline meets memorable sea views.", "Kanyakumari sightseeing day tour;Vivekananda Rock and memorial visit;Sunset coastal sightseeing experience"],

  // KERALA
  ["munnar", "Munnar", "Kerala", "Tea country & viewpoints", "Explore tea-covered hills, scenic viewpoints and reservoir landscapes.", "Munnar tea plantation day tour;Eravikulam and hill viewpoint excursion;Munnar tea museum visit"],
  ["alleppey", "Alleppey", "Kerala", "Backwaters & coastal days", "Discover Alappuzha's backwaters, houseboats, beaches and heritage.", "Alleppey backwater day cruise;Village and canal sightseeing tour;Alleppey beach and lighthouse visit"],
  ["kochi", "Kochi", "Kerala", "Harbour city & heritage", "Historic waterfronts, colonial architecture and local culture.", "Fort Kochi heritage walking tour;Mattancherry palace and spice market visit;Kochi harbour sightseeing cruise"],
  ["wayanad", "Wayanad", "Kerala", "Forests & waterfalls", "Green landscapes, wildlife areas and mountain scenery.", "Wayanad waterfall day trip;Edakkal Caves heritage excursion;Wayanad nature and plantation walk"],
  ["kovalam", "Kovalam", "Kerala", "Beaches & coastal escapes", "Lighthouse beaches and relaxed coastal experiences.", "Kovalam beach sightseeing tour;Lighthouse beach half-day visit;Thiruvananthapuram and Kovalam day trip"],
  ["varkala", "Varkala", "Kerala", "Cliffs & coastal culture", "Dramatic coastal cliffs, beaches and local experiences.", "Varkala cliff and beach walking tour;Anjengo heritage day excursion;Varkala coastal sunset experience"],
  ["thekkady", "Thekkady", "Kerala", "Wildlife & spice trails", "Forest scenery, spice plantations and nature experiences.", "Thekkady spice plantation tour;Periyar nature sightseeing excursion;Kumily local culture and food walk"],

  // ANDHRA PRADESH & TELANGANA
  ["visakhapatnam", "Visakhapatnam", "Andhra Pradesh", "Beaches & coastal viewpoints", "Coastal viewpoints, beaches and city landmarks.", "Visakhapatnam city sightseeing tour;Kailasagiri and beach day trip;Araku Valley day excursion"],
  ["tirupati", "Tirupati", "Andhra Pradesh", "Pilgrimage & heritage", "A major pilgrimage destination surrounded by historic sites.", "Tirupati temple town sightseeing;Chandragiri Fort heritage excursion;Tirupati local culture tour"],
  ["vijayawada", "Vijayawada", "Andhra Pradesh", "Riverfront & heritage", "River landscapes, temples and historic attractions.", "Vijayawada city sightseeing day tour;Undavalli Caves heritage visit;Amaravati heritage excursion"],
  ["hyderabad", "Hyderabad", "Telangana", "Historic city & food culture", "Explore forts, palaces, bazaars and famous local cuisine.", "Hyderabad heritage city day tour;Charminar and Laad Bazaar walking tour;Hyderabad food trail"],
  ["warangal", "Warangal", "Telangana", "Kakatiya heritage", "Historic forts, temples and architectural landmarks.", "Warangal heritage sightseeing day tour;Thousand Pillar Temple visit;Ramappa Temple heritage excursion"],

  // GOA
  ["goa", "Goa", "Goa", "Beaches & coastal heritage", "Explore beaches, historic churches and forts along Goa's coast.", "North Goa sightseeing day tour;South Goa beaches and heritage trip;Old Goa churches and heritage walk"],
  ["panaji", "Panaji", "Goa", "Portuguese heritage & city life", "Colourful streets, riverfronts and historic Goan architecture.", "Fontainhas heritage walking tour;Panaji city sightseeing half-day tour;Mandovi river sightseeing cruise"],

  // MAHARASHTRA
  ["mumbai", "Mumbai", "Maharashtra", "City landmarks & coastline", "Discover iconic landmarks, markets and coastal neighbourhoods.", "Mumbai city sightseeing day tour;South Mumbai heritage walking tour;Elephanta Caves excursion"],
  ["pune", "Pune", "Maharashtra", "Culture & urban escapes", "Historic forts, museums and lively city experiences.", "Pune heritage city day tour;Shaniwar Wada and old city walk;Sinhagad Fort day excursion"],
  ["lonavala", "Lonavala", "Maharashtra", "Hill escapes & viewpoints", "A popular escape for viewpoints, caves and monsoon landscapes.", "Lonavala and Khandala sightseeing day trip;Karla and Bhaja Caves heritage tour;Lonavala waterfall and viewpoint excursion"],
  ["mahabaleshwar", "Mahabaleshwar", "Maharashtra", "Hill stations & nature", "Valley viewpoints, strawberry farms and scenic landscapes.", "Mahabaleshwar sightseeing day tour;Strawberry farm visit;Panchgani and Table Land excursion"],
  ["nashik", "Nashik", "Maharashtra", "Heritage & vineyards", "Historic temples, riverfronts and regional food experiences.", "Nashik heritage city tour;Trimbakeshwar temple excursion;Nashik vineyard and countryside experience"],
  ["aurangabad", "Chhatrapati Sambhajinagar", "Maharashtra", "Cave heritage & history", "Explore remarkable cave monuments and historic architecture.", "Ajanta Caves day tour;Ellora Caves and Grishneshwar excursion;Daulatabad Fort heritage tour"],

  // GUJARAT
  ["ahmedabad", "Ahmedabad", "Gujarat", "Architecture & heritage", "Historic neighbourhoods, stepwells and cultural landmarks.", "Ahmedabad heritage city walking tour;Adalaj Stepwell and Gandhinagar day trip;Sabarmati Ashram sightseeing visit"],
  ["kutch", "Kutch", "Gujarat", "Desert landscapes & crafts", "White desert scenery, traditional villages and handicrafts.", "Great Rann of Kutch sightseeing excursion;Bhuj heritage and craft village tour;Kutch handicraft workshop visit"],
  ["dwarka", "Dwarka", "Gujarat", "Coastal pilgrimage", "Temple heritage and coastal landscapes.", "Dwarka temple and heritage day tour;Bet Dwarka excursion;Dwarka coastal sightseeing trip"],
  ["somnath", "Somnath", "Gujarat", "Temple heritage & coast", "Historic pilgrimage sites and Arabian Sea scenery.", "Somnath temple heritage tour;Somnath and Gir day excursion;Prabhas Patan heritage walk"],

  // RAJASTHAN
  ["jaipur", "Jaipur", "Rajasthan", "Forts & royal landmarks", "Explore the Pink City through its palaces, courtyards and historic forts.", "Jaipur royal heritage day tour;Amber Fort and Jal Mahal excursion;Jaipur food and bazaar walking tour"],
  ["udaipur", "Udaipur", "Rajasthan", "Lakes & palaces", "Palaces, lakes and old-city experiences in the City of Lakes.", "Udaipur city palace and heritage tour;Lake Pichola boat sightseeing;Udaipur old city food walk"],
  ["jodhpur", "Jodhpur", "Rajasthan", "Forts & blue city", "Discover Mehrangarh Fort, blue lanes and desert culture.", "Jodhpur Blue City walking tour;Mehrangarh Fort heritage visit;Jodhpur food and market trail"],
  ["jaisalmer", "Jaisalmer", "Rajasthan", "Desert & forts", "Golden sandstone architecture and Thar Desert landscapes.", "Jaisalmer fort and heritage day tour;Sam Sand Dunes desert excursion;Jaisalmer havelis walking tour"],
  ["pushkar", "Pushkar", "Rajasthan", "Sacred town & markets", "Temple heritage, lakefront ghats and colourful bazaars.", "Pushkar heritage walking tour;Pushkar and Ajmer day trip;Pushkar local food and market walk"],
  ["mount-abu", "Mount Abu", "Rajasthan", "Hill station & temples", "A scenic hill escape with lakes, viewpoints and temple architecture.", "Mount Abu sightseeing day tour;Dilwara Temples heritage visit;Nakki Lake and sunset viewpoint trip"],

  // UTTAR PRADESH & UTTARAKHAND
  ["agra", "Agra", "Uttar Pradesh", "Monuments & Mughal heritage", "Discover the Taj Mahal, Agra Fort and the city's architectural heritage.", "Taj Mahal and Agra Fort day tour;Agra Mughal heritage walking tour;Fatehpur Sikri day excursion"],
  ["varanasi", "Varanasi", "Uttar Pradesh", "Spiritual heritage & riverfront", "Historic ghats, temples and the culture of the Ganges.", "Varanasi heritage and temple walking tour;Ganges sunrise boat sightseeing;Sarnath half-day heritage excursion"],
  ["lucknow", "Lucknow", "Uttar Pradesh", "Nawabi architecture & food", "Discover grand architecture, old markets and Awadhi cuisine.", "Lucknow heritage city day tour;Bara Imambara and old city walk;Lucknow Awadhi food trail"],
  ["ayodhya", "Ayodhya", "Uttar Pradesh", "Temple heritage", "Explore the city's important religious and historic landmarks.", "Ayodhya temple and heritage sightseeing;Saryu riverfront experience;Ayodhya local culture walking tour"],
  ["rishikesh", "Rishikesh", "Uttarakhand", "River & outdoor experiences", "Riverfronts, yoga culture and Himalayan foothill scenery.", "Rishikesh city and ashram walking tour;Ganga riverfront sightseeing;Rishikesh nature and waterfall excursion"],
  ["haridwar", "Haridwar", "Uttarakhand", "Pilgrimage & riverfront", "Historic ghats, temples and spiritual landmarks.", "Haridwar temple and heritage day tour;Har Ki Pauri riverfront visit;Rishikesh and Haridwar combined day trip"],
  ["mussoorie", "Mussoorie", "Uttarakhand", "Mountain escapes", "Hilltop viewpoints, colonial-era streets and mountain scenery.", "Mussoorie sightseeing day tour;Kempty Falls excursion;Landour heritage and café walk"],
  ["nainital", "Nainital", "Uttarakhand", "Lakes & mountain views", "Lakeside sightseeing and scenic Himalayan viewpoints.", "Nainital lake and town day tour;Bhimtal and Sattal excursion;Nainital heritage walking tour"],

  // HIMACHAL PRADESH, PUNJAB, HARYANA, JAMMU & KASHMIR, LADAKH
  ["shimla", "Shimla", "Himachal Pradesh", "Mountain heritage", "Colonial architecture, mountain views and historic promenades.", "Shimla city sightseeing day tour;Kufri and Mashobra excursion;Shimla heritage walking tour"],
  ["manali", "Manali", "Himachal Pradesh", "Mountain adventure", "Mountain scenery, riverside escapes and local culture.", "Manali sightseeing day tour;Solang Valley excursion;Old Manali culture and café walk"],
  ["dharamshala", "Dharamshala", "Himachal Pradesh", "Himalayan culture", "Mountain monasteries, local markets and scenic landscapes.", "McLeod Ganj heritage walking tour;Dharamshala monastery sightseeing;Triund region nature excursion"],
  ["amritsar", "Amritsar", "Punjab", "Heritage & food culture", "Historic landmarks, markets and celebrated Punjabi cuisine.", "Amritsar heritage city day tour;Golden Temple and Jallianwala Bagh visit;Amritsar food walking tour"],
  ["chandigarh", "Chandigarh", "Chandigarh", "Architecture & gardens", "Planned-city architecture, gardens and cultural attractions.", "Chandigarh city sightseeing day tour;Rock Garden and Sukhna Lake visit;Capitol Complex architecture tour"],
  ["gurugram", "Gurugram", "Haryana", "Urban experiences", "Contemporary city attractions, dining and nearby heritage escapes.", "Gurugram city experience tour;Sultanpur bird sanctuary excursion;Kurukshetra heritage day trip"],
  ["srinagar", "Srinagar", "Jammu and Kashmir", "Lakes & gardens", "Lake landscapes, gardens and Kashmiri heritage.", "Srinagar city and Mughal gardens tour;Dal Lake sightseeing experience;Old Srinagar heritage walking tour"],
  ["gulmarg", "Gulmarg", "Jammu and Kashmir", "Mountain scenery", "Mountain landscapes, meadows and seasonal outdoor experiences.", "Gulmarg sightseeing day trip;Gulmarg meadow and viewpoint excursion;Tangmarg and Gulmarg regional tour"],
  ["leh", "Leh", "Ladakh", "High-altitude landscapes", "Monasteries, mountain passes and distinctive Himalayan culture.", "Leh monastery and heritage day tour;Shanti Stupa and Leh Palace visit;Indus Valley sightseeing excursion"],
  ["jammu", "Jammu", "Jammu and Kashmir", "Temple heritage & hills", "Historic temples, forts and foothill landscapes.", "Jammu city heritage day tour;Bahu Fort and gardens visit;Patnitop day excursion"],

  // WEST BENGAL, ODISHA, BIHAR, JHARKHAND
  ["kolkata", "Kolkata", "West Bengal", "Colonial heritage & culture", "Historic architecture, cultural districts and celebrated food.", "Kolkata heritage city day tour;Victoria Memorial and Maidan visit;Kolkata street food walking tour"],
  ["darjeeling", "Darjeeling", "West Bengal", "Tea gardens & mountains", "Tea estates, Himalayan viewpoints and mountain railway heritage.", "Darjeeling sightseeing day tour;Tea estate guided experience;Tiger Hill and monastery excursion"],
  ["kalimpong", "Kalimpong", "West Bengal", "Hills & monasteries", "Quiet mountain landscapes, monasteries and local markets.", "Kalimpong sightseeing day tour;Monastery and heritage walk;Kalimpong flower and nursery visit"],
  ["sundarbans", "Sundarbans", "West Bengal", "Mangroves & wildlife", "Explore the remarkable mangrove ecosystem and river landscapes.", "Sundarbans guided day excursion;Mangrove river sightseeing cruise;Sundarbans village and nature experience"],
  ["puri", "Puri", "Odisha", "Temple heritage & coast", "Temple architecture, coastal scenery and local traditions.", "Puri temple and heritage day tour;Puri beach sightseeing;Konark Sun Temple day excursion"],
  ["bhubaneswar", "Bhubaneswar", "Odisha", "Temple city & architecture", "Ancient temples, caves and regional cultural heritage.", "Bhubaneswar temple heritage day tour;Udayagiri and Khandagiri caves visit;Dhauli and city sightseeing excursion"],
  ["bodh-gaya", "Bodh Gaya", "Bihar", "Buddhist heritage", "Important Buddhist heritage sites and peaceful cultural landmarks.", "Bodh Gaya heritage walking tour;Mahabodhi Temple sightseeing;Rajgir and Nalanda day excursion"],
  ["ranchi", "Ranchi", "Jharkhand", "Waterfalls & nature", "Waterfalls, forest landscapes and regional sightseeing.", "Ranchi waterfall sightseeing day tour;Hundru Falls excursion;Ranchi city and heritage tour"],
  ["deoghar", "Deoghar", "Jharkhand", "Temple heritage", "Temple landmarks and regional pilgrimage experiences.", "Deoghar temple heritage tour;Trikuta Hills sightseeing excursion;Deoghar local culture walk"],

  // NORTHEAST INDIA
  ["guwahati", "Guwahati", "Assam", "River & temple heritage", "Brahmaputra river scenery, temples and local culture.", "Guwahati city sightseeing day tour;Kamakhya Temple heritage visit;Brahmaputra river sightseeing cruise"],
  ["kaziranga", "Kaziranga", "Assam", "Wildlife & nature", "A celebrated landscape of grasslands, wetlands and wildlife.", "Kaziranga nature and visitor experience;Kaziranga landscape sightseeing excursion;Assam tea and village experience"],
  ["shillong", "Shillong", "Meghalaya", "Hills & waterfalls", "Mountain viewpoints, waterfalls and lively local culture.", "Shillong city sightseeing day tour;Elephant Falls and viewpoints trip;Shillong local food and culture walk"],
  ["cherrapunji", "Cherrapunji", "Meghalaya", "Waterfalls & living root bridges", "Waterfalls, caves and remarkable living root bridge landscapes.", "Cherrapunji waterfall day tour;Living root bridge guided excursion;Mawsmai Cave and scenic viewpoint trip"],
  ["tawang", "Tawang", "Arunachal Pradesh", "Monasteries & mountain scenery", "High mountain landscapes and significant monastery heritage.", "Tawang monastery heritage tour;Tawang scenic viewpoint excursion;Jaswant Garh and regional sightseeing"],
  ["ziro", "Ziro", "Arunachal Pradesh", "Valleys & local culture", "Valley landscapes, traditional villages and local culture.", "Ziro valley sightseeing day tour;Apatani village cultural walk;Ziro nature and landscape excursion"],
  ["gangtok", "Gangtok", "Sikkim", "Himalayan city & culture", "Monasteries, mountain viewpoints and local markets.", "Gangtok city sightseeing day tour;Rumtek Monastery excursion;Gangtok heritage and food walk"],
  ["pelling", "Pelling", "Sikkim", "Mountain views & monasteries", "Himalayan scenery, monasteries and heritage sites.", "Pelling sightseeing day tour;Pemayangtse Monastery visit;Rabdentse ruins heritage excursion"],
  ["kohima", "Kohima", "Nagaland", "Tribal heritage & hills", "Historic landmarks, traditional culture and hill landscapes.", "Kohima city heritage tour;Kisama heritage village visit;Kohima local culture walking experience"],
  ["imphal", "Imphal", "Manipur", "Culture & heritage", "Historic sites, traditional markets and regional culture.", "Imphal city sightseeing day tour;Kangla Fort heritage visit;Ima Keithel market and culture walk"],
  ["aizawl", "Aizawl", "Mizoram", "Hill city & local culture", "Hilltop views, local markets and cultural experiences.", "Aizawl city sightseeing tour;Mizoram culture and market walk;Reiek hill day excursion"],
  ["agartala", "Agartala", "Tripura", "Palaces & heritage", "Palace architecture, temples and regional history.", "Agartala city sightseeing day tour;Ujjayanta Palace heritage visit;Neermahal day excursion"],

  // UNION TERRITORIES
  ["delhi", "Delhi", "Delhi", "Capital city & monuments", "Explore historic monuments, markets and modern city experiences.", "Old Delhi heritage and food walking tour;New Delhi monuments day tour;Qutub Minar and Humayun's Tomb excursion"],
  ["port-blair", "Port Blair", "Andaman and Nicobar Islands", "Island history & beaches", "Island landscapes, marine experiences and historic landmarks.", "Port Blair city sightseeing day tour;Cellular Jail heritage visit;Coral island boat excursion"],
  ["havelock", "Swaraj Dweep (Havelock)", "Andaman and Nicobar Islands", "Island beaches & marine life", "Island beaches and marine landscapes.", "Radhanagar Beach sightseeing;Island coastal day excursion;Marine life visitor experience"],
  ["neil-island", "Shaheed Dweep (Neil Island)", "Andaman and Nicobar Islands", "Island escapes", "Quiet beaches, natural rock formations and coastal scenery.", "Neil Island beach sightseeing tour;Natural Bridge coastal visit;Island cycling and village experience"],
  ["kavaratti", "Kavaratti", "Lakshadweep", "Island lagoons & marine life", "Explore island scenery and lagoon environments.", "Kavaratti island sightseeing;Lagoon visitor experience;Island culture and heritage walk"],
  ["agatti", "Agatti", "Lakshadweep", "Coral islands & beaches", "Island landscapes and clear lagoon scenery.", "Agatti island sightseeing;Coastal village experience;Lagoon and beach visitor excursion"],
  ["pondicherry", "Puducherry", "Puducherry", "French heritage & coastline", "Heritage streets, seaside promenades and café culture.", "Puducherry French Quarter walking tour;Auroville day excursion;Promenade and heritage sightseeing"],
  ["silvassa", "Silvassa", "Dadra and Nagar Haveli and Daman and Diu", "Nature & tribal heritage", "Green landscapes, local heritage and relaxed sightseeing.", "Silvassa city sightseeing day tour;Vanganga Lake Garden visit;Tribal culture and heritage experience"],
  ["daman", "Daman", "Dadra and Nagar Haveli and Daman and Diu", "Coastal heritage", "Coastal forts, beaches and historic Portuguese-era landmarks.", "Daman fort and heritage day tour;Devka beach sightseeing;Daman coastal and church walk"],
  ["diu", "Diu", "Dadra and Nagar Haveli and Daman and Diu", "Island heritage & beaches", "Historic forts, beaches and coastal architecture.", "Diu Fort and heritage sightseeing;Nagoa Beach day visit;Diu island sightseeing tour"],
];

function inferCategory(title) {
  const text = title.toLowerCase();

  if (/food|culinary|market|café|cafe|cooking/.test(text)) return "food";
  if (/temple|heritage|fort|palace|monument|museum|church|historical|cultural|culture|village/.test(text)) return "culture";
  if (/trek|adventure|rafting|camp|wildlife|safari|birdwatch|cycling/.test(text)) return "adventure";
  if (/cruise|boat|river|beach|island|backwater|lake|lagoon/.test(text)) return "water";
  if (/nature|forest|waterfall|garden|hill|viewpoint|valley|mountain|tea|coffee|plantation/.test(text)) return "nature";
  if (/ticket|visit|sightseeing|excursion/.test(text)) return "attractions";

  return "tours";
}

function getDuration(title) {
  const text = title.toLowerCase();

  if (/day tour|day trip|day excursion/.test(text)) {
    return { days: 1, nights: 0, label: "1 day" };
  }

  if (/half-day|walking tour|food walk|walk|visit|experience/.test(text)) {
    return { hours: 3, label: "Approx. 3 hours" };
  }

  return { days: 1, nights: 0, label: "1 day" };
}

// Normalize names so capitalization, spaces, hyphens and punctuation
// do not prevent a destination from finding its images.
function normalizeDestination(value = "") {
  return value.toLowerCase().replace(/[^a-z0-9]/g, "");
}

// Some catalogue names differ from their destination slugs.
const destinationAliases = {
  aurangabad: ["Chhatrapati Sambhajinagar"],
  pondicherry: ["Puducherry"],
  havelock: ["Swaraj Dweep (Havelock)"],
  "neil-island": ["Shaheed Dweep (Neil Island)"],
};

// Remove repeated destination slugs before creating the destination list.
const seenSlugs = new Set();

const uniqueCatalogue = catalogue.filter(([slug]) => {
  if (seenSlugs.has(slug)) return false;

  seenSlugs.add(slug);
  return true;
});

const places = uniqueCatalogue.map(
  ([slug, name, state, theme, introduction, activityText]) => ({
    slug,
    name,
    state,
    theme,
    introduction,
    heroId: `${slug}-hero`,
    packageId: null,
    activityNames: activityText.split(";"),
  })
);

// Preserve the original package relationships.
const originalPackageLinks = {
  mysore: "mysore-ooty",
  ooty: "mysore-ooty",
  munnar: "munnar-alleppey-kochi",
  alleppey: "munnar-alleppey-kochi",
  kochi: "munnar-alleppey-kochi",
  jaipur: "raj-se-taj-tak",
  agra: "raj-se-taj-tak",
  goa: "goa-dandeli",
  dandeli: "goa-dandeli",
  hampi: "hampi-pattadakal",
};

export const destinations = places.map((place) => {
  const acceptedNames = [
    place.slug,
    place.name,
    ...(destinationAliases[place.slug] || []),
  ].map(normalizeDestination);

  const imageAttractions = destinationImages.filter((image) =>
    acceptedNames.includes(normalizeDestination(image.destination))
  );

  const hero =
    imageAttractions.find((image) => image.id === place.heroId) ||
    imageAttractions.find((image) => image.id?.endsWith("-hero")) ||
    imageAttractions[0];

  const generatedExperiences = place.activityNames.map(
    (activityName, index) => {
      const matchingImage = imageAttractions.find((image) => {
        const imageName = (image.name || image.title || "").toLowerCase();
        const firstActivityWord = activityName
          .split(" ")[0]
          .toLowerCase();

        return imageName.includes(firstActivityWord);
      });

      const duration = getDuration(activityName);

      return {
        id: `${place.slug}-experience-${index + 1}`,
        name: activityName,
        title: activityName,
        destination: place.name,
        destinationSlug: place.slug,
        state: place.state,
        location: place.name,
        category: inferCategory(activityName),
        type: inferCategory(activityName),
        description: `${activityName} in ${place.name}, ${place.state}. Discover local highlights and ask Nature Holidays about planning this experience.`,
        overview: `${activityName} in ${place.name}, ${place.state}. Discover local highlights and ask Nature Holidays about planning this experience.`,
        duration,
        image: matchingImage?.image || hero?.image || "",
        productType: "experience",
        listingStatus: "enquiry-only",
        pricing: "Request a quote",
      };
    }
  );

  // Keep existing image/attraction records available to older pages.
  const existingIds = new Set(
    imageAttractions.map((item) => item.id)
  );

  const uniqueGeneratedExperiences = generatedExperiences.filter(
    (item) => !existingIds.has(item.id)
  );

  const attractions = [
    ...imageAttractions,
    ...uniqueGeneratedExperiences,
  ];

  return {
    ...place,
    packageId: originalPackageLinks[place.slug] || place.packageId,
    image: hero?.image || "",
    imageCredit: hero?.creditUrl || "/image-credits.html",
    attractions,
    experiences: generatedExperiences,
    listingStatus: "enquiry-only",
  };
});
