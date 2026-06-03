<?php
include 'config.php';
include 'auth.php';

// Get Search Query and Filters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Featured Trips Section
$featuredSQL = "SELECT * FROM featured WHERE item_type = 'country'";
$featuredResult = $conn->query($featuredSQL);

// User Reviews Section
$reviewsSQL = "SELECT t.id, t.trip_name, t.location, t.rating, t.image_url, 
                      tr.id as review_id,
                      tr.reviewer_name,
                      tr.rating as user_rating,
                      COALESCE(parent.destination_name, d.destination_name) as country
                FROM trips t
                LEFT JOIN trip_ratings tr ON t.id = tr.trip_id
                LEFT JOIN destinations d ON t.destination_id = d.id
                LEFT JOIN destinations parent ON d.parent_id = parent.id
                WHERE tr.id IS NOT NULL AND tr.status='approved'
                ORDER BY RAND()
                LIMIT 6";
$reviewsResult = $conn->query($reviewsSQL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>triptrip | your travel destination guide</title>
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
        <!-- HERO Section -->
        <section class="hero" id="search">
            <div class="herotext">
                    <p class="texta">
                        <span>go</span>
                        <span>some</span>
                        <span>where</span>
                    </p>
                        <p class="textb">
                        <span>you'll</span>
                        <span>never</span>
                        <span>forget :)</span>
                    </p>
                        <p class="textc">
                        because the best memories don't live on your feed — they start with a <b>trip</b>.
                    </p>
                    <div class="hero-credit">
                        <p><b>Svaneti, Georgia</b> — Photo from transcaucasiantrail.org</p>
                    </div>
            </div>
        </section>
    
        <!-- SEARCH Section -->
        <section class="first-section" id="search">
            <img class="section-top" src="images/sectiontop/riptop.webp" alt="" aria-hidden="true">
            <div class="search-content">
                <p class="heading">your vibes meet the trip</p>
                <p class="subtext">find destinations that match your mood, budget, and bucket list energy.</p>

                <form class="trip-search" action="search.php" method="GET">
                    <input type="hidden" name="q" value="">

                    <input type="hidden" name="location" value="all">
                    <input type="hidden" name="price-sort" value="none">
                    <input type="hidden" name="review-filter" value="all">

                    <div class="trip-row">
                        <!-- Destination Selection -->
                        <select name="trip-destination[]">
                            <option value="" disabled selected>Select Destination</option>
                            <option value="beach">Beach</option>
                            <option value="nature">Nature</option>
                            <option value="city">City</option>
                            <option value="heritage">Heritage</option>
                        </select>
                        <!-- Trip Type Selection -->
                        <select name="trip-type[]">
                            <option value="" disabled selected>Trip Type</option>
                            <option value="adventure">Adventure</option>
                            <option value="leisure">Leisure</option>
                            <option value="cultural">Cultural</option>
                        </select>
                    </div>

                    <!-- Budget Slider -->
                    <div class="budget-container">
                        <label class="budget">&nbsp;&nbsp;Budget Range:</label>
                        <input type="range" 
                            name="budget" 
                            min="1000" 
                            max="100000" 
                            step="1000" 
                            value="1000" 
                            oninput="document.querySelector('.budget-value').textContent = '₱ ' + parseInt(this.value).toLocaleString();">
                        <span class="budget-value">₱ 1,000</span>
                    </div>

                    <!-- Find Trip BUTTON -->
                    <div class="findtripbtn">
                        <button type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i> Find Trips
                        </button>
                    </div>
                </form>

            </div>
        </section>

        <!-- FEATURED Section -->
        <section class="truesection" id="featured">
            <img class="section-top" src="images/sectiontop/circtop.webp" alt="" aria-hidden="true">
            <div class="featured-content">
                <p class="heading">featured trips</p>
                <p class="subtext">from hidden gems to iconic spots, these featured trips are crafted to inspire your next great adventure.</p>
            
                <!-- List of Featured Trips -->
                <div class="carousel-center">
                    <div class="gallery-container">

                        <?php
                            if ($featuredResult && $featuredResult->num_rows > 0):
                            while ($featured = $featuredResult->fetch_assoc()): ?>
                            <div class="gallery">
                                <a href="<?php echo $featured['link_url']; ?>">
                                    <img src="<?php echo $featured['image_url']; ?>" alt="<?php echo $featuredw['title']; ?>">
                                </a>
                                <div class="description">
                                    <?php echo $featured['title']; ?>
                                </div>
                            </div>
                        <?php endwhile; endif;?>

                    </div>
                </div>
            <p class="gallery-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
        </section>

        <!-- User Reviews Section -->
        <section class="reviews" id="reviews">
            <img class="section-top" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">
            <div class="reviews-content">
                <p class="heading">user reviews</p>
                <p class="subtext">real stories, real journeys. see what other explorers loved (or didn’t) about their trips.</p>

                    <!-- List of Ratings -->
                    <div class="stamp-center">
                        <div class="stamp-container">

                            <?php
                            if ($reviewsResult && $reviewsResult->num_rows > 0):
                                $counter = 0; // Initialize counter
                                while ($trip = $reviewsResult->fetch_assoc()):
                                    if ($counter >= 6) break; // Stop after 6 items

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

                            <?php
                                $counter++; // Increment counter
                                endwhile; endif;
                            ?>
                            
                        </div>
                    </div>
                <p class="stamp-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
                </div>
                
            </div>
            <img class="section-bot" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">
        </section>
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
        e.preventDefault(); // Stops
        alert('Please enter a search term.');
        }
    });
</script>
</body>
</html>