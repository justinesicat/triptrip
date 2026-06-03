<?php
include 'config.php';

// Get search query and filters from GET
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$destinations = isset($_GET['trip-destination']) ? $_GET['trip-destination'] : [];
$tripTypes = isset($_GET['trip-type']) ? $_GET['trip-type'] : [];
$location = isset($_GET['location']) ? $_GET['location'] : '';
$budget = isset($_GET['budget']) ? floatval($_GET['budget']) : 100000;
$reviewFilter = isset($_GET['review-filter']) ? $_GET['review-filter'] : 'all';

if ($searchQuery === '') {
    // No search query, return empty results
    $trips = [];
} else {
    $destinations = isset($_GET['trip-destination']) ? $_GET['trip-destination'] : [];
    $tripTypes = isset($_GET['trip-type']) ? $_GET['trip-type'] : [];
    $location = isset($_GET['location']) ? $_GET['location'] : '';
    $budget = isset($_GET['budget']) ? floatval($_GET['budget']) : 100000;
    $reviewFilter = isset($_GET['review-filter']) ? $_GET['review-filter'] : 'all';
}

// Base SQL: Calculate average rating from trip_ratings table
$sql = "SELECT t.*, d.slug AS destination_slug, d.destination_name,
        COALESCE(AVG(tr.rating), t.rating) as avg_rating,
        COUNT(tr.id) as review_count
        FROM trips t
        JOIN destinations d ON t.destination_id = d.id
        LEFT JOIN trip_ratings tr ON t.id = tr.trip_id AND tr.status = 'approved'
        WHERE (t.trip_name LIKE ? OR d.destination_name LIKE ?)";
$params = ["%$searchQuery%", "%$searchQuery%"];
$types = "ss";

// Add filters dynamically
if (!empty($destinations)) {
    $placeholders = implode(',', array_fill(0, count($destinations), '?'));
    $sql .= " AND t.destination_category IN ($placeholders)";
    $params = array_merge($params, $destinations);
    $types .= str_repeat('s', count($destinations));
}

if (!empty($tripTypes)) {
    $placeholders = implode(',', array_fill(0, count($tripTypes), '?'));
    $sql .= " AND t.trip_type IN ($placeholders)";
    $params = array_merge($params, $tripTypes);
    $types .= str_repeat('s', count($tripTypes));
}

if ($location && $location !== 'all') {
    $sql .= " AND t.location = ?";
    $params[] = $location;
    $types .= "s";
}

if ($budget) {
    $sql .= " AND t.price <= ?";
    $params[] = $budget;
    $types .= "d";
}

// Group by trip to calculate averages
$sql .= " GROUP BY t.id, d.slug, d.destination_name";

// Apply rating filter AFTER grouping
if ($reviewFilter && $reviewFilter !== 'all') {
    if ($reviewFilter === '10') {
        $sql .= " HAVING avg_rating >= 9.5";
    } elseif ($reviewFilter === '7-9') {
        $sql .= " HAVING avg_rating BETWEEN 7 AND 9.4";
    } elseif ($reviewFilter === '4-6') {
        $sql .= " HAVING avg_rating BETWEEN 4 AND 6.9";
    } elseif ($reviewFilter === '1-3') {
        $sql .= " HAVING avg_rating BETWEEN 1 AND 3.9";
    }
}

// Optional: Sorting
if (isset($_GET['price-sort'])) {
    if ($_GET['price-sort'] === 'low-high') $sql .= " ORDER BY t.price ASC";
    elseif ($_GET['price-sort'] === 'high-low') $sql .= " ORDER BY t.price DESC";
}

// Prepare statement dynamically
$stmt = $conn->prepare($sql);

// Bind parameters dynamically
if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch trips with destination slug
$trips = [];
if ($result && $result->num_rows > 0) {
    $trips = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>search results | triptrip</title>
    <link rel="stylesheet" href="css/default.css?v=<?php echo time(); ?>">

    <!-- Outside References -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Luxurious+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    
<header>
    <div class="logo-search">
        <!-- triptrip Logo -->
        <img class="logo-white" src="images/triptripLOGO.png" alt="logo">

    <!-- Search Bar -->
    <div class="search-bar">
        <form id="search-form" action="search.php" method="GET">
            <input type="text" name="q" id="search-input" placeholder="SEARCH...">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
    </div>

    <!-- Hidden Checkbox for the HAMBURGER -->
    <input type="checkbox" id="menu-toggle">

    <!-- Hamburger icon -->
    <label for="menu-toggle" class="menu-icon">
        <i class="fa-solid fa-bars"></i>
    </label>

    <!-- Drawer Overlay -->
    <label for="menu-toggle" class="overlay"></label>

    <!-- Drawer -->
    <nav id="nav-drawer" class="nav-drawer">
        <!-- Close for Navigation Drawer -->
        <label for="menu-toggle" class="close-icon">
        <i class="fa-solid fa-xmark"></i>
        </label>

        <!-- Navigation Links -->
        <ul class="nav_links">
            <li><a href="index.php"><i class="fa-solid fa-house"></i> HOME</a></li>

            <!-- Dropdown Link -->
            <li class="dropdown">
                <a href="#"><i class="fa-solid fa-map"></i> TRIPS</a>
                <div class="dropdown-center">
                    <ul class="dropdown-content">
                        <li><a href="featured.php">FEATURED TRIPS</a></li>
                        <li><a href="userreviews.php">USER REVIEWS</a></li>
                    </ul>
                </div>
            </li>

            <li><a href="about.php"><i class="fa-solid fa-info-circle"></i> ABOUT</a></li>
            <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> CONTACT</a></li>
            <li><a href="account.php"><button class="account"><i class="fa-solid fa-user"></i> ACCOUNT</button></a></li>
        </ul>
    </nav>
</header>

    <main>
        <!-- SEARCH Section -->
        <section class="search-section">
            <img class="section-top" src="images/sectiontop/riptop.webp" alt="" aria-hidden="true">

            <div class="searches-content">
                <p class="heading">search results</p>

                <form class="filters-section" action="search.php" method="GET">
                    <!-- Hidden input to preserve search query -->
                    <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
                    
                    <!-- Clear -->
                    <?php
                    if (isset($_GET['clear'])) {
                        $destinations = [];
                        $tripTypes = [];
                        $location = 'all';
                        $budget = 100000;
                        $reviewFilter = 'all';
                    }
                    ?>

                    <div class="filters-header">
                        <div class="filters-title">
                            <i class="fa fa-filter"></i> filters
                        </div>
                        <div class="filters-buttons">
                            <button type="submit" class="clear-filters" name="clear" value="1">Clear All</button>
                            <button type="submit" class="apply-filters">Apply</button>
                        </div>
                    </div>

                    <div class="filters-grid">
                        <!-- Destination checkboxes -->
                        <div class="filter-group">
                            <label class="filter-label">Destination</label>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="beach" value="beach" name="trip-destination[]" <?= in_array('beach', $destinations) ? 'checked' : '' ?>>
                                    <label for="beach">Beach</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="nature" value="nature" name="trip-destination[]" <?= in_array('nature', $destinations) ? 'checked' : '' ?>>
                                    <label for="nature">Nature</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="city" value="city" name="trip-destination[]" <?= in_array('city', $destinations) ? 'checked' : '' ?>>
                                    <label for="city">City</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="heritage" value="heritage" name="trip-destination[]" <?= in_array('heritage', $destinations) ? 'checked' : '' ?>>
                                    <label for="heritage">Heritage</label>
                                </div>
                            </div>
                        </div>

                        <!-- Trip Type checkboxes -->
                        <div class="filter-group">
                            <label class="filter-label">Trip Type</label>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="checkbox" id="adventure" value="adventure" name="trip-type[]" <?= in_array('adventure', $tripTypes) ? 'checked' : '' ?>>
                                    <label for="adventure">Adventure</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="leisure" value="leisure" name="trip-type[]" <?= in_array('leisure', $tripTypes) ? 'checked' : '' ?>>
                                    <label for="leisure">Leisure</label>
                                </div>
                                <div class="filter-option">
                                    <input type="checkbox" id="cultural" value="cultural" name="trip-type[]" <?= in_array('cultural', $tripTypes) ? 'checked' : '' ?>>
                                    <label for="cultural">Cultural</label>
                                </div>
                            </div>
                        </div>

                        <!-- Location radios -->
                        <div class="filter-group">
                            <label class="filter-label">Location</label>
                            <div class="filter-options">
                                <div class="filter-option">
                                    <input type="radio" name="location" id="all-locations" value="all" <?= $location === 'all' || $location === '' ? 'checked' : '' ?>>
                                    <label for="all-locations">All Destinations</label>
                                </div>
                                <div class="filter-option">
                                    <input type="radio" name="location" id="local" value="local" <?= $location === 'local' ? 'checked' : '' ?>>
                                    <label for="local">Local (Philippines)</label>
                                </div>
                                <div class="filter-option">
                                    <input type="radio" name="location" id="international" value="international" <?= $location === 'international' ? 'checked' : '' ?>>
                                    <label for="international">International</label>
                                </div>
                            </div>
                        </div>

                        <!-- Budget slider -->
                        <div class="filter-group">
                            <label class="filter-label">Price Range (₱)</label>
                            <div class="budget-container">
                                <input type="range" id="price-range" name="budget" min="1000" max="100000" step="1000" value="<?= htmlspecialchars($budget) ?>">
                                <span class="budget-value" id="price-value">₱ <?= number_format($budget) ?></span>
                            </div>
                        </div>

                        <!-- Sort and reviews -->
                        <div class="filter-group">
                            <label class="filter-label">Sort By Price</label>
                            <select class="filter-select" id="price-sort" name="price-sort">
                                <option value="none" <?= !isset($_GET['price-sort']) || $_GET['price-sort'] === 'none' ? 'selected' : '' ?>>No Sorting</option>
                                <option value="low-high" <?= isset($_GET['price-sort']) && $_GET['price-sort'] === 'low-high' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="high-low" <?= isset($_GET['price-sort']) && $_GET['price-sort'] === 'high-low' ? 'selected' : '' ?>>Price: High to Low</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">User Reviews</label>
                            <select class="filter-select" id="review-filter" name="review-filter">
                                <option value="all" <?= $reviewFilter === 'all' ? 'selected' : '' ?>>All Ratings</option>
                                <option value="10" <?= $reviewFilter === '10' ? 'selected' : '' ?>>10 ★</option>
                                <option value="7-9" <?= $reviewFilter === '7-9' ? 'selected' : '' ?>>7 to 9 ★</option>
                                <option value="4-6" <?= $reviewFilter === '4-6' ? 'selected' : '' ?>>4 to 6 ★</option>
                                <option value="1-3" <?= $reviewFilter === '1-3' ? 'selected' : '' ?>>1 to 3 ★</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="results-container">

                    <?php if (!empty($trips)): ?>
                        <?php foreach ($trips as $trip): ?>
                            <div class="result-card">
                                <img src="<?= htmlspecialchars($trip['image_url']) ?>" alt="<?= htmlspecialchars($trip['trip_name']) ?>">
                                <div class="result-info">
                                <h3><?= htmlspecialchars($trip['trip_name']) ?></h3>
                                <div class="rating">
                                    <?php
                                        // Use the calculated average rating
                                        $rating = floatval($trip['avg_rating']);
                                        $fullStars = floor($rating);
                                        $halfStar = ($rating - $fullStars >= 0.5) ? 1 : 0;
                                        $emptyStars = 10 - ($fullStars + $halfStar);
                                        $reviewCount = intval($trip['review_count']);
                                    ?>

                                    <span class="stars">
                                        <!-- Full Stars -->
                                        <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                            <span class="starfill">★</span>
                                        <?php endfor; ?>

                                        <!-- Half Star (optional) -->
                                        <?php if ($halfStar): ?>
                                            <span class="starhalf">★</span>
                                        <?php endif; ?>

                                        <!-- Empty Stars -->
                                        <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                            <span class="starempty">★</span>
                                        <?php endfor; ?>
                                    </span>

                                    <span class="rating-text">
                                        <?= number_format($rating, 1) ?>
                                        <?php if ($reviewCount > 0): ?>
                                            <span class="review-count">(<?= $reviewCount ?> <?= $reviewCount === 1 ? 'review' : 'reviews' ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <p>Price: ₱<?= number_format($trip['price'], 2) ?></p>
                                <p><?= htmlspecialchars($trip['description']) ?></p>
                                
                                <!-- FIXED: Now uses slug instead of destination_name -->
                                <a href="destination.php?dest=<?= urlencode($trip['destination_slug']) ?>" class="view-btn">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>no trips found matching your filters.</p>
                    <?php endif; ?>
                    
                </div>

            </div>
            <img class="section-bot" src="images/sectiontop/riptop.webp" alt="" aria-hidden="true">
        </section>
    </main>
    
<!-- FOOTER Section -->
<footer>
    <div class="footer-content">
        <!-- Logo -->
        <img class="logo" src="images/triptripLOGO.png" alt="triptrip logo">

        <!-- Footer Navigation Links -->
        <div class="footer-links">
            <a href="about.php" title="About">About</a>
            <a href="contact.php" title="Contact">Contact</a>
            <a href="privacypolicy.php" title="Privacy Policy">Privacy Policy</a>
        </div>

        <!-- Social Media Links -->
        <div class="socials">
            <a href="https://www.facebook.com/triptrip" target="_blank" title="triptrip's Facebook Page">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
            <a href="https://www.instagram.com/triptrip" target="_blank" title="triptrip's Instagram Page">
                <i class="fa-brands fa-instagram"></i>
            </a>
            <a href="https://www.x.com/triptrip" target="_blank" title="triptrip's X / Twitter Page">
                <i class="fa-brands fa-x-twitter"></i>
            </a>
            <a href="https://www.tiktok.com/@triptrip" target="_blank" title="triptrip's TikTok Page">
                <i class="fa-brands fa-tiktok"></i>
            </a>
        </div>
    </div>

    <!-- Footer Bottom / Year -->
    <div class="footer-bottom">
        <p>© <span class="footer-year">2025</span> triptrip. All Rights Reserved.</p>
    </div>
</footer>

<script src="main.js" defer></script>
<script>
    // Empty Search Bar
    document.getElementById('search-form').addEventListener('submit', function(e) {
    const query = document.getElementById('search-input').value.trim();
    if (query === '') {
        e.preventDefault(); // the Search Bar Button is unfunctional
        alert('Please enter a search term.');
        }
    });

    // Update budget value display with the slider
    const priceRange = document.getElementById('price-range');
    const priceValue = document.getElementById('price-value');
    
    if (priceRange && priceValue) {
        priceRange.addEventListener('input', function() {
            priceValue.textContent = '₱ ' + parseInt(this.value).toLocaleString();
        });
    }
</script>
</body>
</html>