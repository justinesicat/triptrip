-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 14, 2025 at 05:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `travel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `submitted_at`) VALUES
(1, 'xzceaxz', 'justinephilipsicat@gmail.com', 'w', 'waaawaw', '2025-12-13 12:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `code` varchar(5) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `code`, `name`) VALUES
(1, 'PH', 'Philippines'),
(2, 'US', 'United States'),
(3, 'AU', 'Australia'),
(4, 'JP', 'Japan'),
(5, 'UK', 'United Kingdom'),
(6, 'CA', 'Canada'),
(7, 'SG', 'Singapore'),
(8, 'MY', 'Malaysia');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `slug` varchar(50) NOT NULL,
  `destination_name` varchar(100) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `introduction` text DEFAULT NULL,
  `intro_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) NOT NULL DEFAULT 'country'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `parent_id`, `slug`, `destination_name`, `tagline`, `introduction`, `intro_image`, `created_at`, `type`) VALUES
(1, NULL, 'thailand', 'thailand', 'golden temples, turquoise seas, endless warmth.', 'Thailand is a Southeast Asian constitutional monarchy known as the \"Land of Smiles\" for its hospitable people. Located on the Indochinese Peninsula, it is bordered by Myanmar, Laos, Cambodia, and Malaysia. The country boasts diverse geography, from northern forests to central plains and rugged southern coasts, and is famous for its ornate temples, vibrant cuisine, bustling cities like the capital Bangkok, and pristine tropical landscapes. Thailand is unique as it is the only Southeast Asian country that was never colonized by European powers.', 'https://www.toptourist.com/wp-content/uploads/2016/02/Wat-Phra-Ram-1920x900.jpg', '2025-12-09 07:59:26', 'country'),
(2, 1, 'bangkok', 'bangkok', 'golden temples and neon nights', 'Bangkok is Thailand\'s vibrant, bustling capital, a dynamic mix of modern skyscrapers and ancient traditions, famous for its ornate temples, rich culture, chaotic street life, floating markets, and delicious food, serving as the nation\'s political, commercial, and spiritual heart. Known as Krung Thep (\"City of Angels\"), it\'s a massive metropolis on the Chao Phraya River, blending historical grandeur like the Grand Palace with contemporary energy, traffic, and a thriving tourism scene. ', 'https://res.klook.com/image/upload/fl_lossy.progressive,q_60/Mobile/City/bswpxlc7f9ooxoanlu6h.jpg', '2025-12-14 13:48:48', 'country'),
(8, NULL, 'japan', 'japan', 'immerse yourself in the spirit of zen.', 'Japan is an  East Asian island nation known as the \"Land of the Rising Sun,\" celebrated for its unique balance between tradition and modernity. The country consists of thousands of islands, with the four largest being Honshu, Hokkaido, Kyushu, and Shikoku. Japan’s cultural depth, advanced technology, and natural beauty make it one of the most fascinating destinations on Earth. From serene temples and shrines to neon-lit cityscapes and culinary wonders, Japan offers travelers an unforgettable experience.', 'https://i0.wp.com/www.touristjapan.com/wp-content/uploads/2023/04/fujiyoshida-view-scaled-e1680427764989.jpg?resize=2000%2C800&ssl=1', '2025-12-14 15:50:30', 'country'),
(9, 8, 'tokyo', 'tokyo', 'ramen hair, don\'t care', 'With its futuristic skyscrapers, unrivaled food scene, and wild nightlife, Tokyo is a rush of pure adrenaline. The city is famously cutting-edge, yet its ancient Buddhist temples, vintage teahouses, and peaceful gardens offer a serene escape—and a reminder of its past.', 'https://www.eyexplore.com/wp-content/uploads/tokyo-by-night-photo-tour-eyexplore-5.jpg', '2025-12-14 15:52:57', 'country'),
(10, NULL, 'philippines', 'philippines', 'it\'s more fun in the Philippines!', 'The Philippines is an archipelago consisting of 7,100 islands with a total land area of approximately 300,000 square kilometers. It has three major island groups: Luzon in the north, Visayas in the middle and Mindanao further down in the South.', 'https://images.goway.com/production/hero_image/El%20Nido%20bay%2C%20Philippines_AdobeStock_48512464.jpeg?VersionId=N4SutR1ZqOsAm7SWMWEUnekojQYVeGzO', '2025-12-14 15:58:08', 'country'),
(11, NULL, 'france', 'france', 'find your romance in the city of lights', 'France, the French Republic, is a modern nation in Western Europe, known for its rich culture, influential history, and diverse landscapes. It\'s the largest country in Western Europe and a global center for art, fashion, and cuisine, attracting the most tourists worldwide. Its capital is Paris, and it is a democratic republic with a hexagonal territory that borders multiple countries and two major seas.', 'https://cdn.kimkim.com/files/a/content_articles/featured_photos/ad4152414df2cdd37318a45116425968e54660f5/big-be61419e9d977f19df34a89410365609.jpg', '2025-12-14 16:08:13', 'country'),
(12, 11, 'paris', 'paris', 'paris~dise, je t\'aime', 'Paris has a reputation for being the ultimate romantic getaway. But what visitors really swoon over is the city itself. Those grand stone and wrought-iron buildings, the sidewalks brimming with cozy cafés, and the Seine’s curving riverbanks are downright cinematic. But the city’s charms go beyond looks. The culinary scene creates an endless list of must-eat French dishes—rich and hearty coq au vin, golden buttery croissants. It’s also worth trying modern fusion and inventive international food here. (Trust us, the city’s falafel is outstanding.) And the spirit of Paris invites ducking down side streets, lingering in museums, and exploring mazes of shops. At the end of the day, head to the Champ-de-Mars to get uninterrupted views of the Eiffel Tower as it glitters into the night.', 'https://lp-cms-production.imgix.net/2021-02/GettyRF_824655732.jpg?auto=format,compress&q=72&w=1095&fit=crop&crop=faces,edges', '2025-12-14 16:11:28', 'country'),
(13, 10, 'tarlac', 'tarlac', 'where culture meets progress', 'Tarlac is a province in the heart of Central Luzon known for its rich history, cultural diversity, and steady progress. Often called the melting pot of the region, it is home to a mix of Kapampangan, Ilocano, Pangasinense, and Tagalog cultures, reflecting unity amid diversity. Tarlac holds historical importance as it once served as the seat of the First Philippine Republic, while its wide plains and farmlands support agriculture and local livelihoods. Today, the province continues to grow as a center for education, trade, and development while preserving its cultural heritage.', 'https://mediaim.expedia.com/destination/2/c03b4b49124bb0a4cbcb60ed27f5770c.jpg', '2025-12-14 16:17:05', 'country');

-- --------------------------------------------------------

--
-- Table structure for table `destination_gallery`
--

CREATE TABLE `destination_gallery` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destination_gallery`
--

INSERT INTO `destination_gallery` (`id`, `destination_id`, `image_url`, `alt_text`) VALUES
(19, 1, 'https://res.cloudinary.com/cdn-connections/image/upload/c_limit,w_3840/f_auto/q_auto:best/v1746008069/Destinations/Asia/Thailand/TOURS/Grand%20Tour%20Thailand/Header_Thailand_Winter?_a=BAVAZGBy0', 'Grand Tour Thailand Winter'),
(20, 1, 'https://cdn.vietlongtravel.com/wp-content/uploads/2025/04/Bangkok-thailand-2.jpg?strip=all&lossy=1&ssl=1', 'Bangkok'),
(21, 1, 'https://shef.com/homemade-food/wp-content/uploads/thai-food01-scaled-e1662414525462.jpg', 'Thai Food'),
(22, 1, 'https://images.squarespace-cdn.com/content/v1/62f1cb15a2cb083186ccd6d1/1664428270099-0IBN4EE2IC5CSRFBHKYO/4024-1024x683.jpg', 'Thai Dish'),
(23, 1, 'https://images.goway.com/production/hero/iStock-2142836599.jpg?VersionId=XundOhlq2_dm.dUthOI4TAVJwdA4wheL', 'Thai People'),
(24, 1, 'https://www.grasshopperadventures.com/sites/default/files/styles/wysiwyg_large/public/2025-07/When%20and%20where%20is%20the%20Thailand%20Festival%20of%20Lights.jpg.jpg?itok=ZxpWbvbO', 'Thai Festival of Lights');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_description` text DEFAULT NULL,
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `destination_id`, `event_name`, `event_description`, `image_url`) VALUES
(16, 1, 'Yi Peng Lantern Festival', 'Celebrated mainly in Chiang Mai, thousands of paper lanterns are released into the sky, symbolizing the release of worries and bad luck. Participants often write wishes on their lanterns before letting them float away, creating one of the most visually stunning events in Thailand.', 'https://ik.imagekit.io/tvlk/dam/i/01k5vkgaz8xvv98fb6wbgbkjse.jpeg'),
(17, 1, 'Poi Sang Long Festival', 'A colorful ordination ceremony for young Shan boys in Northern Thailand, particularly in Mae Hong Son. The boys are dressed in ornate costumes and paraded through the streets before being ordained as novice monks, marking an important rite of passage.', 'https://maehongsonthailand.com/wp-content/uploads/2018/07/poi-sang-long-festival02.jpg'),
(18, 1, 'Traditional Lanna Dance', 'An iconic part of Northern Thai culture featuring graceful hand and body movements. Dances such as Fon Leb (Finger Dance) and Fon Tien (Candle Dance) are performed during cultural festivals, showcasing elegance and devotion through movement and symbolism.', 'https://images.chiangmaicitylife.com/clg/wp-content/uploads/2020/03/Lanna_art_34.jpg'),
(19, 1, 'Hae Nang Maew (Pray for Rainfall)', 'A unique rain-making ritual where villagers parade with a cat, believed to have spiritual power to bring rain. The ritual highlights Thailand’s deep agricultural traditions and connection between humans, nature, and spirituality.', 'https://static.bangkokpost.com/media/content/20180621/c1_1489654.jpg'),
(20, 1, 'Bun Bang Fai (Rocket Festival)', 'A lively and colorful event held in Yasothon province, where homemade rockets are launched into the sky to encourage rainfall for the farming season. The festival features music, parades, and traditional dances celebrating fertility and prosperity.', 'https://thaicyclopedia.com/wp-content/uploads/2024/05/94.png');

-- --------------------------------------------------------

--
-- Table structure for table `featured`
--

CREATE TABLE `featured` (
  `featured_id` int(11) NOT NULL,
  `item_type` enum('country','landmark') NOT NULL,
  `title` varchar(255) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `link_url` varchar(500) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured`
--

INSERT INTO `featured` (`featured_id`, `item_type`, `title`, `image_url`, `link_url`, `date_added`) VALUES
(1, 'country', 'Thailand', 'https://www.ingtaxi.com/wp-content/uploads/2023/07/grand-palace.jpg', 'destination.php?dest=thailand', '2025-12-11 10:26:48'),
(2, 'country', 'Japan', 'https://e0.pxfuel.com/wallpapers/802/638/desktop-wallpaper-torii-japan-red-lake-resolution-japanese-torii.jpg', 'destination.php?dest=japan', '2025-12-11 10:26:48'),
(3, 'country', 'France', 'https://www.royalcaribbean.com/media-assets/pmc/content/dam/shore-x/paris-le-havre-leh/lh05-a-taste-of-paris/stock-photo-paris-street-at-night-with-the-arc-de-triomphe-in-paris-france-315886463.jpg?w=1440', 'destination.php?dest=france', '2025-12-11 10:26:48'),
(4, 'landmark', 'Bangkok', 'https://www.thailand-reiseprofis.com/wp-content/bilder/wat-arun-bangkok.jpg', 'destination.php?dest=bangkok', '2025-12-13 11:10:27'),
(5, 'landmark', 'Tokyo', 'https://media.digitalnomads.world/wp-content/uploads/2021/02/20120635/tokyo-for-digital-nomads.jpg', 'destination.php?dest=tokyo', '2025-12-13 11:10:27'),
(6, 'landmark', 'Paris', 'https://media.cntraveler.com/photos/58de89946c3567139f9b6cca/1:1/w_3633,h_3633,c_limit/GettyImages-468366251.jpg', 'destination.php?dest=paris', '2025-12-13 11:10:27');

-- --------------------------------------------------------

--
-- Table structure for table `local_foods`
--

CREATE TABLE `local_foods` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `local_foods`
--

INSERT INTO `local_foods` (`id`, `destination_id`, `food_name`, `description`, `image_url`) VALUES
(16, 1, 'Tom Yum Goong', 'A spicy and sour shrimp soup balancing heat, tang, and sweetness.', 'https://warningsugarygoodness.com/wp-content/uploads/2024/06/tom-yum-goong-spicy-thai-soup-500x375.jpg'),
(17, 1, 'Pad Thai', 'Stir-fried rice noodles with eggs, tofu, or shrimp, garnished with peanuts and lime.', 'https://www.recipetineats.com/tachyon/2020/01/Chicken-Pad-Thai_9-SQ.jpg?resize=500%2C500'),
(18, 1, 'Som Tum', 'Spicy green papaya salad often paired with grilled chicken or sticky rice.', 'https://c.ndtvimg.com/1hkfbvu_som-tam-salad_625x300_04_September_18.jpg'),
(19, 1, 'Kai Med Ma Muang', 'Stir-fried chicken with cashews, soy sauce, and honey—mild but flavorful.', 'https://i0.wp.com/zorzascuisine.home.blog/wp-content/uploads/2018/11/kai-med-ma.jpg?fit=1200%2C675&ssl=1'),
(20, 1, 'Thai Chicken Satay', 'Grilled marinated chicken skewers served with peanut sauce.', 'https://www.seriouseats.com/thmb/uJYjjYQuiXHUsSvW8NjxqZrwB8U=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/__opt__aboutcom__coeus__resources__content_migration__serious_eats__seriouseats.com__recipes__images__2016__08__21060606-chicken-satay-08-31f6ef90e9c04286aef7450c9451fe14.jpg'),
(22, 2, 'Mango Sticky Rice (Khao Niew Mamuang)', 'Mango Sticky Rice is a popular Thai dessert made with sweet sticky rice, fresh ripe mango slices, and creamy coconut milk. It is especially popular during mango season and is commonly sold in markets and street stalls.', 'https://susiecooksthai.com/wp-content/uploads/2024/05/chris_15655_Plate_of_Mango_and_Sticky-2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tourist_spots`
--

CREATE TABLE `tourist_spots` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `spot_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `entrance_fee_adult` varchar(50) DEFAULT NULL,
  `entrance_fee_child` varchar(50) DEFAULT NULL,
  `schedule` text DEFAULT NULL,
  `how_to_get_there` text DEFAULT NULL,
  `best_time` text DEFAULT NULL,
  `image_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tourist_spots`
--

INSERT INTO `tourist_spots` (`id`, `destination_id`, `spot_name`, `description`, `entrance_fee_adult`, `entrance_fee_child`, `schedule`, `how_to_get_there`, `best_time`, `image_url`) VALUES
(16, 1, 'Wat Phra Chetuphon', 'Wat Phra Chetuphon Wimonmangkalaram Ratchaworamahawihan or Wat Pho is located on Maha Rat Road next to the Grand Palace. It is a first class royal temple and an ancient site, built under the orders of King Rama I for monks to study Dharma. During the reign of King Rama III, the temple was renovated and inscribed with academic texts across its walls, marking it as Thailand’s first university.', '300.00 Baht', '300.00 Baht', 'Open daily from 8:00 AM - 8:00 PM', 'Wat Pho is easily accessible by taking the BTS Skytrain to Saphan Taksin station, followed by a short boat ride to Tha Tien pier, just a brief walk from the temple.', 'The best time to explore is between November and February, during the cooler months. Visit early in the morning or late afternoon to avoid the heat and crowds.', 'https://preparetravelplans.com/wp-content/uploads/2020/03/18-Phra-Rabiang-Cloisters-Wat-Pho.jpg'),
(17, 1, 'Chatuchak Weekend Market', 'BTS Skytrain: Take the Sukhumvit Line to Mo Chit Station (N8), Exit 1.MRT: Get off at Chatuchak Park or Kamphaeng Phet station.Tuk Tuk: A traditional ride experience; fares range from 100–350 Baht depending on distance.Bus: Cheapest option—only 20 Baht, ideal from Khao San Road area.\r\n', '', '', 'Friday: 6:00 PM - 12:00 AM\r\nSaturday - Sunday: 9:00 AM - 6:00 PM', 'BTS Skytrain: Take the Sukhumvit Line to Mo Chit Station (N8), Exit 1.\r\nMRT: Get off at Chatuchak Park or Kamphaeng Phet station.\r\nTuk Tuk: A traditional ride experience; fares range from 100–350 Baht depending on distance.\r\nBus: Cheapest option—only 20 Baht, ideal from Khao San Road area.', 'Arrive early in the morning when the market first opens to beat the crowds and enjoy the best shopping experience.', 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/12/56/c6/14/dsc-2066-largejpg.jpg?w=1200&h=-1&s=1'),
(18, 1, 'The Grand Palace', 'Established in 1782, the Grand Palace served as the ceremonial residence of Thailand’s kings. It features magnificent buildings, including the Temple of the Emerald Buddha, courtyards, and museums. It remains an iconic symbol of Thai craftsmanship and spirituality.', '', '', 'Open daily: 8:30 AM - 3:30 PM', 'Take the MRT Blue Line to Sanam Chai Station (15-minute walk to the palace) or the BTS Skytrain to Saphan Taksin Station, then a Chao Phraya Express Boat to Tha Chang Pier.', 'Visit from November to February for the best weather conditions.', 'https://lp-cms-production.s3.amazonaws.com/public/2021-06/shutterstockRF_1614073372.jpg'),
(19, 1, 'Siam Paragon', 'This upscale shopping complex features luxury brands, gourmet food halls, car showrooms, and family-friendly attractions like SEA LIFE Bangkok Ocean World and Madame Tussauds. It’s one of Bangkok’s modern cultural hubs.', '', '', 'Open daily: 10:00 AM - 10:00 PM', 'Located at Siam BTS Station (CEN), accessible via both the Sukhumvit and Silom Lines. Taxis and shuttle bus services to Siam Premium Outlets are also available.', 'Visit between November and February for pleasant weather, though May and June offer fewer crowds.', 'https://www.siamdiscovery.co.th/public/upload/8e58ef13e385a32db1263823c31fe3f0.jpg'),
(20, 1, 'The Sanctuary of Truth', 'The Sanctuary of Truth in Pattaya is a monumental all-wood structure standing 105 meters tall. It combines Thai craftsmanship with spiritual and philosophical symbolism, reflecting humanity’s relationship with the universe. Built to withstand seaside weather, it remains one of Thailand’s most unique architectural wonders.', '', '', 'Open daily: 8:00 AM - 8:30 PM', 'Take a baht bus north to Dolphin Circle, then walk 15 minutes or hire a motorcycle cab for the final stretch.', 'The best months are November to February for cooler weather and clearer views.', 'https://res.klook.com/images/fl_lossy.progressive,q_65/c_fill,w_3000,h_1999/w_80,x_15,y_15,g_south_west,l_Klook_water_br_trans_yhcmh3/activities/riwuojas4vdmpyr2biqu/TheSanctuaryofTruthTicketinPattaya-KlookPhilippines.jpg'),
(22, 2, 'Wat Arun Ratchawararam (Temple of Dawn)', 'Wat Arun is one of Bangkok’s most iconic riverside temples, known for its tall central prang decorated with colorful porcelain and seashells. It is especially beautiful at sunrise and sunset, offering a scenic view of the Chao Phraya River.', 'PHP 300', 'PHP 300', 'Daily, 8:00 AM – 6:00 PM', 'Take the Chao Phraya Express Boat to Tha Tien Pier, then a short ferry ride across the river to Wat Arun.\r\nTaxi or Grab is also available.', 'Early morning or late afternoon for cooler weather and great photos.', 'https://i0.wp.com/azureskyfollows.com/wp-content/uploads/2020/10/prang-of-wat-arun.jpeg?fit=1280%2C720&ssl=1');

-- --------------------------------------------------------

--
-- Table structure for table `traditions`
--

CREATE TABLE `traditions` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `tradition_title` varchar(100) NOT NULL,
  `tradition_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `traditions`
--

INSERT INTO `traditions` (`id`, `destination_id`, `tradition_title`, `tradition_description`) VALUES
(25, 1, 'The Wai Greeting', 'The traditional Thai greeting, called the “wai,” involves pressing your palms together in front of your chest and bowing slightly. It’s used to greet, thank, or apologize and shows respect—especially to elders, monks, or superiors.'),
(26, 1, 'Removing Shoes Indoors', 'Before entering homes, temples, and even some shops, it’s customary to remove your shoes. This practice reflects respect and cleanliness, as shoes are considered dirty and unfit for sacred or living spaces.'),
(27, 1, 'Respect for the Head and Feet', 'In Thai culture, the head is viewed as the most sacred part of the body while the feet are the lowest. Touching someone’s head or pointing your feet at others, religious icons, or images of the King is considered very disrespectful.'),
(28, 1, 'Polite Speech', 'Thais often end sentences with polite particles — “krub” (for men) and “ka” (for women) — to express respect and friendliness in everyday conversation.'),
(29, 1, 'Temple Etiquette', 'When visiting temples, dress modestly by covering shoulders and knees. Always remove hats and shoes before entering, keep your voice low, and never point your feet toward Buddha statues. Women should avoid touching monks.'),
(30, 1, 'Respect for the Monarchy', 'The Thai royal family is deeply revered. Showing disrespect toward the monarchy is socially unacceptable and punishable by law. Always stand during the national anthem and avoid casual discussion about the royal family.'),
(31, 1, 'Maintaining Harmony and “Saving Face”', 'Thais value calmness and avoiding confrontation. Raising your voice, arguing publicly, or embarrassing someone is considered rude. Smiling and staying composed are signs of maturity and respect.'),
(32, 1, 'Dining Etiquette', 'Thai meals are often shared family-style. Use a spoon to eat and a fork to push food onto it — never put a fork directly in your mouth. Slurping noodles is acceptable and shows appreciation for the food. Wait for the eldest person to begin eating before starting your meal.');

-- --------------------------------------------------------

--
-- Table structure for table `travel_tips`
--

CREATE TABLE `travel_tips` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `tip_title` varchar(100) NOT NULL,
  `tip_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travel_tips`
--

INSERT INTO `travel_tips` (`id`, `destination_id`, `tip_title`, `tip_description`) VALUES
(10, 1, 'Timing', 'The rainy season varies by region—June–October in most parts, and October–December along the Gulf Coast.'),
(11, 1, 'Health', 'Vaccinations for tetanus and hepatitis A are recommended; malaria precautions may be needed in border regions.'),
(12, 1, 'Planning', 'Book popular activities in advance during November–March, Thailand’s peak tourist season.');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `trip_name` varchar(255) NOT NULL,
  `destination_category` enum('Beach','Nature','City','Heritage') NOT NULL,
  `trip_type` enum('Adventure','Leisure','Cultural') NOT NULL,
  `location` enum('Local','International') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `description` text DEFAULT NULL,
  `image_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `destination_id`, `trip_name`, `destination_category`, `trip_type`, `location`, `price`, `rating`, `description`, `image_url`, `created_at`) VALUES
(1, 2, 'Bangkok', 'City', 'Cultural', 'International', 20000.00, 0.0, 'golden temples and neon nights', 'https://res.klook.com/image/upload/fl_lossy.progressive,q_60/Mobile/City/bswpxlc7f9ooxoanlu6h.jpg', '2025-12-14 13:48:48'),
(5, 9, 'Tokyo', 'City', 'Leisure', 'International', 40000.00, 0.0, 'ramen hair, don\'t care', 'https://www.eyexplore.com/wp-content/uploads/tokyo-by-night-photo-tour-eyexplore-5.jpg', '2025-12-14 15:52:57'),
(6, 12, 'Paris', 'City', 'Cultural', 'International', 70000.00, 0.0, 'paris~dise, je t\'aime', 'https://lp-cms-production.imgix.net/2021-02/GettyRF_824655732.jpg?auto=format,compress&q=72&w=1095&fit=crop&crop=faces,edges', '2025-12-14 16:11:28'),
(7, 13, 'Tarlac', 'Nature', 'Cultural', 'Local', 500.00, 0.0, 'where culture meets progress', 'https://mediaim.expedia.com/destination/2/c03b4b49124bb0a4cbcb60ed27f5770c.jpg', '2025-12-14 16:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `trip_ratings`
--

CREATE TABLE `trip_ratings` (
  `id` int(11) NOT NULL,
  `trip_id` int(11) NOT NULL,
  `reviewer_name` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review_title` varchar(255) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trip_ratings`
--

INSERT INTO `trip_ratings` (`id`, `trip_id`, `reviewer_name`, `rating`, `review_title`, `review_text`, `created_at`, `status`) VALUES
(1, 1, 'Sarah Mangoba', 9, 'Amazing Street Food and Culture!', 'Bangkok exceeded all my expectations! The Grand Palace was breathtaking, and the street food scene is unmatched. Khao San Road at night is vibrant and full of energy. The floating markets were a unique experience. Only downside was the traffic, but the BTS Skytrain made getting around easy. Definitely coming back!', '2025-12-11 11:59:59', 'approved'),
(2, 1, 'Marcus Lewis Hamilton', 10, 'Perfect City Break', 'Temples, food, nightlife - Bangkok has it all! Five days wasn\'t enough.', '2025-12-11 12:02:40', 'approved'),
(3, 1, 'Emily Riley', 7, 'Great but Overwhelming', 'Loved the culture and food, but the heat and crowds were intense. Go during cooler months if possible.', '2025-12-11 12:02:40', 'approved'),
(4, 1, 'Chen Wei', 8, 'Budget Traveler\'s Paradise', 'Amazing value for money. Stayed in a nice hostel, ate incredible food daily, all without breaking the bank.', '2025-12-11 12:02:40', 'approved'),
(26, 5, 'Justine Philip T. Sicat', 10, 'The BEST!', 'I love everything Tokyo.', '2025-12-14 15:54:12', 'approved'),
(27, 7, 'Justine Philip T. Sicat', 8, 'my HOMETOWN', 'My childhood place, now with full of traffic! Sadly, I had to commute for 2 hours.', '2025-12-14 16:19:05', 'approved'),
(28, 6, '6uim', 4, 'DO NOT GO!', 'The tourist spots are mesmerizing, but the people here really sucks!', '2025-12-14 16:24:58', 'approved'),
(29, 5, '6uim', 9, 'Kawaii', 'There\'s no such thing as boring here. I\'d like to visit Tokyo again and again!', '2025-12-14 16:25:56', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `user_type` enum('guest','standard','admin') NOT NULL DEFAULT 'standard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `country`, `profile_pic`, `user_type`, `created_at`, `updated_at`) VALUES
(1, 'Justine Philip T. Sicat', 'justinephilipsicat@gmail.com', '$2y$10$AAEE.hB9bIGdm1tGf4S3meTViexBP1zZTUC/ROUaIuqdSC7JxBS86', 'PH', 'profile_693d9aa7d39295.56190619.gif', 'admin', '2025-12-13 09:10:20', '2025-12-14 16:26:23'),
(2, '6uim', '6uim@yahoo.com', '$2y$10$pvZJKEb7EYXuc1VtKCdsueU0XCr7Ex2mHoAOBe5Uus/Mi3O9sjZJS', 'PH', 'profile_693edb6d27dca8.81825846.gif', 'standard', '2025-12-13 15:43:39', '2025-12-14 15:44:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `destination_gallery`
--
ALTER TABLE `destination_gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `featured`
--
ALTER TABLE `featured`
  ADD PRIMARY KEY (`featured_id`);

--
-- Indexes for table `local_foods`
--
ALTER TABLE `local_foods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `traditions`
--
ALTER TABLE `traditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `travel_tips`
--
ALTER TABLE `travel_tips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trip_ratings`
--
ALTER TABLE `trip_ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `destination_gallery`
--
ALTER TABLE `destination_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `featured`
--
ALTER TABLE `featured`
  MODIFY `featured_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `local_foods`
--
ALTER TABLE `local_foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tourist_spots`
--
ALTER TABLE `tourist_spots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `traditions`
--
ALTER TABLE `traditions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `travel_tips`
--
ALTER TABLE `travel_tips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `trip_ratings`
--
ALTER TABLE `trip_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
