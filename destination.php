<?php
include 'config.php';
include 'auth.php';

// Get search query and filters from GET
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$destinations = isset($_GET['trip-destination']) ? $_GET['trip-destination'] : [];
$tripTypes = isset($_GET['trip-type']) ? $_GET['trip-type'] : [];
$location = isset($_GET['location']) ? $_GET['location'] : '';
$budget = isset($_GET['budget']) ? floatval($_GET['budget']) : 100000;
$reviewFilter = isset($_GET['review-filter']) ? $_GET['review-filter'] : 'all';

// Base SQL: fetch trips where either the trip name OR destination name matches separately
$sql = "SELECT t.*, d.slug AS destination_slug, d.destination_name
        FROM trips t
        JOIN destinations d ON t.destination_id = d.id
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

if ($reviewFilter && $reviewFilter !== 'all') {
    if ($reviewFilter === '10') {
        $sql .= " AND t.rating = 10";
    } elseif ($reviewFilter === '7-9') {
        $sql .= " AND t.rating BETWEEN 7 AND 9";
    } elseif ($reviewFilter === '4-6') {
        $sql .= " AND t.rating BETWEEN 4 AND 6";
    } elseif ($reviewFilter === '1-3') {
        $sql .= " AND t.rating BETWEEN 1 AND 3";
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

if (isset($_GET['dest'])) {
    $destination_slug = $_GET['dest'];
} else {
    header("Location: error.php");
    exit;
}

// Query for destination
$destination_sql = "SELECT * FROM destinations WHERE slug = ?";
$stmt = $conn->prepare($destination_sql);
$stmt->bind_param("s", $destination_slug);
$stmt->execute();
$destination_result = $stmt->get_result();

if ($destination_result->num_rows === 0) {
    // If no destination found, block access
    header("Location: error.php");
    exit;
}

$destination = $destination_result->fetch_assoc();

// Get the Destination Data from URL
if (isset($_GET['dest'])) {
    $slug = $_GET['dest'];
} else {
    // Default or error handling
    echo "Destination not found!";
    exit;
}

$sql = "SELECT * FROM destinations WHERE slug=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();
$destination = $result->fetch_assoc();

// If destination does not exist
if (!$destination) {
    echo "Destination not found in database!";
    exit;
}

// User Reviews Section
$destination_id = $destination['id'];
$parent_id = $destination['parent_id'];

if ($parent_id === NULL || $parent_id === 0) {
    // This is a country - get reviews from all child destinations
    $reviewsSQL = "SELECT t.id, t.trip_name, t.location, t.rating, t.image_url, 
                          tr.id as review_id,
                          tr.reviewer_name,
                          tr.rating as user_rating,
                          COALESCE(parent.destination_name, d.destination_name) as country
                    FROM trips t
                    LEFT JOIN trip_ratings tr ON t.id = tr.trip_id
                    LEFT JOIN destinations d ON t.destination_id = d.id
                    LEFT JOIN destinations parent ON d.parent_id = parent.id
                    WHERE tr.id IS NOT NULL 
                    AND tr.status='approved'
                    AND (d.id = ? OR d.parent_id = ?)
                    ORDER BY RAND()
                    LIMIT 6";
    $stmt = $conn->prepare($reviewsSQL);
    $stmt->bind_param("ii", $destination_id, $destination_id);
} else {
    // This is a city/landmark - get reviews only from this destination
    $reviewsSQL = "SELECT t.id, t.trip_name, t.location, t.rating, t.image_url, 
                          tr.id as review_id,
                          tr.reviewer_name,
                          tr.rating as user_rating,
                          COALESCE(parent.destination_name, d.destination_name) as country
                    FROM trips t
                    LEFT JOIN trip_ratings tr ON t.id = tr.trip_id
                    LEFT JOIN destinations d ON t.destination_id = d.id
                    LEFT JOIN destinations parent ON d.parent_id = parent.id
                    WHERE tr.id IS NOT NULL 
                    AND tr.status='approved'
                    AND d.id = ?
                    ORDER BY RAND()
                    LIMIT 6";
    $stmt = $conn->prepare($reviewsSQL);
    $stmt->bind_param("i", $destination_id);
}

$stmt->execute();
$reviewsResult = $stmt->get_result();

$discoverSql = "SELECT * FROM trips LIMIT 10"; // Adjust your query as needed
$discoverResult = $conn->query($discoverSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $destination['destination_name']; ?></title>
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
    <!-- Heading -->
    <section class="heading-section">
        <div class="heading-content">
            <p class="heading"><?php echo $destination['destination_name']; ?></p>
            <p class="subtext"><?php echo $destination['tagline']; ?></p>
        </div>
    </section>

    <!-- Introduction -->
    <section class="first-section" id="introduction">
        <img class="section-top" src="images/sectiontop/circtopnormal.webp" alt="">
        <div class="first-content">
            <p class="heading">introduction</p>
            <div class="review-photo">
                <img src="<?php echo $destination['intro_image']; ?>" alt="<?php echo $destination['destination_name']; ?>">
            </div>
            <p class="subtext"><?php echo $destination['introduction']; ?></p>
        </div>
    </section>

    <?php

    // Get Tourist Spots from the Database
    $sql = "SELECT * FROM tourist_spots WHERE destination_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $destination['id']);
    $stmt->execute();
    $spots_result = $stmt->get_result();
    ?>

    <?php if ($spots_result && $spots_result->num_rows > 0): ?>
    <section class="general-section" id="tourist-spot">
        <img class="section-top" src="images/sectiontop/roundtop.webp" alt="">
        <div class="general-content">
            <div class="general-text">
                <p class="heading">top tourist spots</p>
                
                <!-- Gallery Carousel -->
                <section class="gallery-image">
                    <div class="carousel">
                        <?php 
                        $i = 1; 
                        $spots_result->data_seek(0);
                        while($spot = $spots_result->fetch_assoc()): 
                            if(!empty($spot['image_url'])): 
                        ?>
                            <input type="checkbox" id="spot<?php echo $i; ?>" class="toggle">
                            <label for="spot<?php echo $i; ?>" class="slide">
                                <p class="gcap"><?php echo $spot['spot_name']; ?></p>
                                <img src="<?php echo $spot['image_url']; ?>" alt="<?php echo $spot['spot_name']; ?>">
                                <span class="overlay"></span>
                            </label>
                        <?php 
                            endif;
                            $i++; 
                        endwhile; 
                        ?>
                    </div>
                </section>
                <p class="gallery-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
                
                <!-- Detailed Articles -->
                <?php 
                $spots_result->data_seek(0); // Reset pointer
                while($spot = $spots_result->fetch_assoc()): 
                ?>
                    <article>
                        <hr><br><br>
                        <h3><?php echo $spot['spot_name']; ?></h3>
                        <p><?php echo $spot['description']; ?></p>
                        <?php if($spot['entrance_fee_adult'] || $spot['entrance_fee_child']): ?>
                            <h4>Entrance Fee</h4>
                            <ul>
                                <?php if($spot['entrance_fee_adult']): ?><li>Adult - <?php echo $spot['entrance_fee_adult']; ?></li><?php endif; ?>
                                <?php if($spot['entrance_fee_child']): ?><li>Child - <?php echo $spot['entrance_fee_child']; ?></li><?php endif; ?>
                            </ul>
                        <?php endif; ?>
                        <?php if($spot['schedule']): ?>
                            <h4>Schedule</h4>
                            <p><?php echo nl2br($spot['schedule']); ?></p>
                        <?php endif; ?>
                        <?php if($spot['how_to_get_there']): ?>
                            <h4>How to get there?</h4>
                            <p><?php echo nl2br($spot['how_to_get_there']); ?></p>
                        <?php endif; ?>
                        <?php if($spot['best_time']): ?>
                            <h4>Best Time to Visit</h4>
                            <p><?php echo nl2br($spot['best_time']); ?></p>
                        <?php endif; ?>
                        <br><br><hr>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php
    // Get Other Information from the Database
    $tables = ['local_foods','traditions','events','travel_tips','destination_gallery'];
    foreach($tables as $table){
        $sql = "SELECT * FROM $table WHERE destination_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $destination['id']);
        $stmt->execute();
        ${$table.'_result'} = $stmt->get_result();
    }
    ?>

    <!-- Local Foods Section -->
    <?php if ($local_foods_result && $local_foods_result->num_rows > 0): ?>
    <section class="general-section" id="local-foods">
        <img class="section-top" src="images/sectiontop/riptop.webp" alt="" aria-hidden="true">
        
        <div class="general-content">
            <div class="general-text">
                <p class="heading">local foods</p>
                <p class="subtext">bites worth traveling for.</p>
                
                <!-- Gallery Carousel -->
                <section class="gallery-image">
                    <div class="carousel">
                        <?php 
                        $i = 1; 
                        $local_foods_result->data_seek(0); // Reset pointer
                        while($food = $local_foods_result->fetch_assoc()): 
                            if(!empty($food['image_url'])): 
                        ?>
                            <input type="checkbox" id="food<?php echo $i; ?>" class="toggle">
                            <label for="food<?php echo $i; ?>" class="slide">
                                <p class="gcap"><?php echo $food['food_name']; ?></p>
                                <img src="<?php echo $food['image_url']; ?>" alt="<?php echo $food['food_name']; ?>">
                                <span class="overlay"></span>
                            </label>
                        <?php 
                            endif;
                            $i++; 
                        endwhile; 
                        ?>
                    </div>
                </section>
                <p class="gallery-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
                
                <!-- Text List -->
                <ul>
                    <?php 
                    $local_foods_result->data_seek(0);
                    while($food = $local_foods_result->fetch_assoc()): 
                    ?>
                        <li><b><?php echo $food['food_name']; ?>:</b> <?php echo $food['description']; ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Traditions Section -->
    <?php if ($traditions_result && $traditions_result->num_rows > 0): ?>
    <section class="general-section" id="traditions">
    <img class="section-top" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">
    
    <div class="general-content">
        <div class="general-text">
        <p class="heading">traditions</p>
        <p class="subtext">from old tales to living customs — explore them all.</p>
        <ul>
            <?php while($tradition = $traditions_result->fetch_assoc()): ?>
                <li><b><?php echo $tradition['tradition_title']; ?>:</b> <?php echo $tradition['tradition_description']; ?></li>
            <?php endwhile; ?>
        </ul>
        </div>
    </div>
    </section>
    <?php endif; ?>

    <!-- Events Section -->
    <?php if ($events_result && $events_result->num_rows > 0): ?>
    <section class="general-section" id="events">
        <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">
        
        <div class="general-content">
            <div class="general-text">
                <p class="heading">events</p>
                <p class="subtext">these will spark joy and connection.</p>
                
                <!-- Gallery Carousel -->
                <section class="gallery-image">
                    <div class="carousel">
                        <?php 
                        $i = 1; 
                        $events_result->data_seek(0);  // ← Changed from $spots_result
                        while($event = $events_result->fetch_assoc()):  // ← Changed variable name
                            if(!empty($event['image_url'])):  // ← Changed to $event
                        ?>
                            <input type="checkbox" id="event<?php echo $i; ?>" class="toggle">  <!-- ← Changed ID from "spot" to "event" -->
                            <label for="event<?php echo $i; ?>" class="slide">  <!-- ← Changed for attribute -->
                                <p class="gcap"><?php echo $event['event_name']; ?></p>  <!-- ← Changed to event_name -->
                                <img src="<?php echo $event['image_url']; ?>" alt="<?php echo $event['event_name']; ?>">  <!-- ← Changed to $event -->
                                <span class="overlay"></span>
                            </label>
                        <?php 
                            endif;
                            $i++; 
                        endwhile; 
                        ?>
                    </div>
                </section>
                <p class="gallery-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
                
                <!-- Text List -->
                <ul>
                    <?php 
                    $events_result->data_seek(0);
                    while($event = $events_result->fetch_assoc()): 
                    ?>
                        <li><b><?php echo $event['event_name']; ?>:</b> <?php echo $event['event_description']; ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Travel Tips Section -->
    <?php if ($travel_tips_result && $travel_tips_result->num_rows > 0): ?>
    <section class="general-section" id="travel-tips">
    <img class="section-top" src="images/sectiontop/roundtop.webp" alt="" aria-hidden="true">
        
        <div class="general-content">
          <div class="general-text">
            <p class="heading">travel tips</p>
            <p class="subtext">little tips that make a big difference on the road.</p>
            <ul>
                <?php while($tip = $travel_tips_result->fetch_assoc()): ?>
                    <li><b><?php echo $tip['tip_title']; ?>:</b> <?php echo $tip['tip_description']; ?></li>
                <?php endwhile; ?>
            </ul>
          </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- User Reviews Section -->
    <?php if ($reviewsResult && $reviewsResult->num_rows > 0): ?>
    <section class="reviews" id="reviews">
        <img class="section-top" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">
        <div class="general-content">
            <p class="heading">user reviews</p>
            <p class="subtext">real stories, real journeys. see what other explorers loved (or didn't) about their trips.</p>

            <!-- List of Ratings -->
            <div class="stamp-center">
                <div class="stamp-container">

                    <?php
                        if ($reviewsResult && $reviewsResult->num_rows > 0):
                            while ($trip = $reviewsResult->fetch_assoc()): 

                                // Use user_rating for rating display
                                $rating = round($trip['user_rating'] ?? $trip['rating']);

                                // Generate stars based on User Rating
                                $stars = str_repeat('★', $rating);

                                // Make slug
                                $destinationSlug = strtolower(str_replace(' ', '', $trip['trip_name']));

                                // Shorten reviewer name
                                $reviewerName = $trip['reviewer_name'] ?? 'Anonymous';
                                $displayName = strlen($reviewerName) > 20 
                                    ? substr($reviewerName, 0, 17) . '...' 
                                    : $reviewerName;
                    ?>

                    <div class="stamp">
                        <img class="stampimg" src="images/poststamp.png" alt="stamp background">
                        <img class="stamp-photo" src="<?= htmlspecialchars($trip['image_url']) ?>" alt="<?= htmlspecialchars($trip['trip_name']) ?> photo">

                        <a href="userreviews.php?review_id=<?= $trip['review_id'] ?>" class="stamp-link">
                            <div class="stamp-inner">
                                <div class="stamp-top">
                                    <?= strtoupper(htmlspecialchars($trip['trip_name'])) ?><br>
                                    <?= strtoupper(htmlspecialchars($trip['country'])) ?>
                                </div>

                                <div class="stamp-rating"><?= $rating ?></div>

                                <div class="stamp-bottom">
                                    <?= $stars ?><br>
                                    <b>USER RATING</b><br>
                                    <i><?= htmlspecialchars($displayName) ?></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <?php endwhile; else: ?>
                        <p style="text-align: center; padding: 2rem;">No reviews available yet for this destination.</p>
                    <?php endif; ?>

                </div>
            </div>
            <p class="stamp-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
        </div>
    </section>
    <?php endif; ?>

    <!-- Gallery -->
    <?php if ($destination_gallery_result && $destination_gallery_result->num_rows > 0): ?>
    <section class="last-section" id="gallery">
        <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">

        <div class="last-content">
            <p class="heading">gallery</p>
            <p class="subtext">see the sights, feel the moments.</p>

            <!-- Gallery Section -->
            <section class="gallery-image">
                <div class="carousel">
                    <?php $i = 1; while($photo = $destination_gallery_result->fetch_assoc()): ?>
                        <input type="checkbox" id="img<?php echo $i; ?>" class="toggle">
                        <label for="img<?php echo $i; ?>" class="slide">
                            <p class="gcap"><?php echo $photo['alt_text']; ?></p>
                            <img src="<?php echo $photo['image_url']; ?>" alt="<?php echo $photo['alt_text']; ?>">
                            <span class="overlay"></span>
                        </label>
                    <?php $i++; endwhile; ?>
                </div>
            </section>
                
            <p class="gallery-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
        </div>
        <img class="section-bot" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">
    </section>
    <?php else: ?>
    <!-- Empty Gallery State -->
    <section class="last-section" id="gallery">
        <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">

        <div class="last-content">
            <p class="heading">gallery</p>
            <p class="subtext">see the sights, feel the moments.</p>
            
            <div style="text-align: center; padding: 3rem 2rem; color: #666;">
                <p><strong>NO PICTURES AVAILABLE YET.</strong></p>
            </div>
        </div>
        <img class="section-bot" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">
    </section>
    <?php endif; ?>

</main>

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
        <p>© <span id="year"></span> triptrip. All Rights Reserved.</p>
    </div>
</footer>

<script src="main.js" defer></script>
<script>
    // Year Today
    document.getElementById("year").textContent = new Date().getFullYear();

    // Empty Search Bar
    document.getElementById('search-form').addEventListener('submit', function(e) {
        const query = document.getElementById('search-input').value.trim();
        if (query === '') {
            e.preventDefault();
            alert('Please enter a search term.');
        }
    });
</script>
</body>
</html>


