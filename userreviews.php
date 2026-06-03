<?php
include 'config.php';
include 'auth.php';

$userType  = $_SESSION['user_type'] ?? 'guest';
$canReview = in_array($userType, ['standard', 'admin']);

// Initialize variables
$selectedReviewId = isset($_GET['review_id']) ? intval($_GET['review_id']) : null;
$selectedTripId = null;
$tripDetails = null;
$allReviews = [];
$avgRating = 0;
$reviewCount = 0;
$errorMessage = '';
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $reviewerName = trim($_POST['reviewer-name']);
    $destination = trim($_POST['destination']);
    $rating = intval($_POST['rating']);
    $reviewTitle = trim($_POST['title']);
    $reviewText = trim($_POST['review-text']);
    
    // Validate destination
    $checkDestSQL = "SELECT id FROM trips WHERE trip_name = ?";
    $stmt = $conn->prepare($checkDestSQL);
    $stmt->bind_param("s", $destination);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tripData = $result->fetch_assoc();
        $tripId = $tripData['id'];
        
        $insertSQL = "INSERT INTO trip_ratings (trip_id, reviewer_name, rating, review_title, review_text, created_at, status) 
                      VALUES (?, ?, ?, ?, ?, NOW(), 'pending')";
        $insertStmt = $conn->prepare($insertSQL);
        $insertStmt->bind_param("isiss", $tripId, $reviewerName, $rating, $reviewTitle, $reviewText);
        
        if ($insertStmt->execute()) {
            // Redirect to prevent re-submission and show success
            header("Location: userreviews.php?success=1#submitreview");
            exit();
        } else {
            $errorMessage = "Error submitting review. Please try again.";
        }
        $insertStmt->close();
    } else {
        $errorMessage = "Destination not found. Please enter a valid destination name.";
    }
    $stmt->close();
}

// Check for success flag (before HTML)
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Your review has been submitted successfully!";
}

// Check for success flag in URL (after redirect)
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Your review has been submitted successfully!";
}

// If review_id is provided, get the trip_id and trip details
if ($selectedReviewId) {
    $reviewSQL = "SELECT tr.trip_id, t.trip_name, t.location, t.image_url,
                         COALESCE(parent.destination_name, d.destination_name) as country,
                         tr.status
                  FROM trip_ratings tr
                  JOIN trips t ON tr.trip_id = t.id
                  LEFT JOIN destinations d ON t.destination_id = d.id
                  LEFT JOIN destinations parent ON d.parent_id = parent.id
                  WHERE tr.id = ?";
    $stmt = $conn->prepare($reviewSQL);
    $stmt->bind_param("i", $selectedReviewId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tripDetails = $result->fetch_assoc();

        // If the review is pending, redirect to error page
        if ($tripDetails['status'] !== 'approved') {
            header("Location: error.html");
            exit();
        }

        $selectedTripId = $tripDetails['trip_id'];
    } else {
        // Review ID not found
        header("Location: error.html");
        exit();
    }
    $stmt->close();
}

// If we have a trip_id, get all reviews for that trip
if ($selectedTripId) {
    // Get average rating and count
    $statsSQL = "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
                FROM trip_ratings 
                WHERE trip_id = ? AND status='approved'";
    $stmt = $conn->prepare($statsSQL);
    $stmt->bind_param("i", $selectedTripId);
    $stmt->execute();
    $statsResult = $stmt->get_result();
    $stats = $statsResult->fetch_assoc();
    $avgRating = round($stats['avg_rating'], 1);
    $reviewCount = $stats['review_count'];
    $stmt->close();
    
    // Get the selected review first
    $selectedReviewSQL = "SELECT tr.*, t.trip_name, t.image_url,
                                 COALESCE(parent.destination_name, d.destination_name) as country
                          FROM trip_ratings tr
                          JOIN trips t ON tr.trip_id = t.id
                          LEFT JOIN destinations d ON t.destination_id = d.id
                          LEFT JOIN destinations parent ON d.parent_id = parent.id
                          WHERE tr.id = ?";
    $stmt = $conn->prepare($selectedReviewSQL);
    $stmt->bind_param("i", $selectedReviewId);
    $stmt->execute();
    $selectedReview = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get other reviews (excluding the selected one), limit to 2 more for initial display
    $otherReviewsSQL = "SELECT tr.*, t.trip_name, t.image_url,
                            COALESCE(parent.destination_name, d.destination_name) as country
                        FROM trip_ratings tr
                        JOIN trips t ON tr.trip_id = t.id
                        LEFT JOIN destinations d ON t.destination_id = d.id
                        LEFT JOIN destinations parent ON d.parent_id = parent.id
                        WHERE tr.trip_id = ? AND tr.id != ? AND tr.status='approved'
                        ORDER BY RAND()";
    $stmt = $conn->prepare($otherReviewsSQL);
    $stmt->bind_param("ii", $selectedTripId, $selectedReviewId);
    $stmt->execute();
    $otherReviewsResult = $stmt->get_result();
    
    // Combine: selected review first, then up to 2 random others
    if ($selectedReview) {
        $allReviews[] = $selectedReview;
    }
    while ($review = $otherReviewsResult->fetch_assoc()) {
        $allReviews[] = $review;
    }
    $stmt->close();
}

// Get all trip names for autocomplete
$tripsSQL = "SELECT DISTINCT trip_name FROM trips ORDER BY trip_name";
$tripsResult = $conn->query($tripsSQL);

// Get random reviews for the "discover more" section
$discoverSQL = "SELECT t.id, t.trip_name, t.location, t.rating, t.image_url, 
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
$discoverResult = $conn->query($discoverSQL);

// Get Search Query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>user reviews | triptrip</title>
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
        <!-- User Reviews Heading -->
        <section class="heading-section">
            <div class="heading-content">
                <p class="heading">user reviews</p>
                <p class="subtext">real stories, real journeys. see what other explorers loved (or didn't) about their trips.</p>
            </div>
        </section>
                    
        <!-- Reviews Section -->
        <?php if ($selectedTripId && $tripDetails): ?>
        <section class="reviewdetails-section">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">
            
            <div class="reviewdetails-content">
                <div class="reviews-text">
                    <div class="review-photo">
                        <a href="destination.php?dest=<?= urlencode($tripDetails['trip_name']) ?>">
                            <img src="<?= htmlspecialchars($tripDetails['image_url']) ?>" 
                                alt="<?= htmlspecialchars($tripDetails['trip_name']) ?>">
                            <p class="photo-caption">
                                <?= htmlspecialchars($tripDetails['trip_name']) ?>, <?= mb_convert_case(htmlspecialchars($tripDetails['country']), MB_CASE_TITLE, "UTF-8") ?>
                            </p>
                        </a>
                    </div>
                    <div class="rating-display">
                        <span class="rating-number"><strong><?= $avgRating ?></strong></span>
                        <span class="rating-text">Average Rating</span>
                        <p class="rating-count">(based on <?= $reviewCount ?> review<?= $reviewCount != 1 ? 's' : '' ?>)</p>
                    </div>

                    <?php 
                    $displayCount = 0;
                    foreach ($allReviews as $index => $review): 
                        $rating = intval($review['rating']);
                        $stars = str_repeat('★', $rating);
                        $hideClass = ($displayCount >= 3) ? 'review-hidden' : '';
                        $displayCount++;
                        
                        // Format date
                        $date = new DateTime($review['created_at']);
                        $formattedDate = $date->format('F Y');
                    ?>
                    
                    <div class="reviews-container <?= $hideClass ?>">
                        <hr><br><br><br>
                        <div class="stamp-center">
                            <div class="stamp">
                                <img class="stampimg" src="images/poststamp.png" alt="stamp background">

                                <img class="stamp-photo" 
                                src="<?= htmlspecialchars($review['image_url']) ?>" 
                                alt="<?= htmlspecialchars($review['trip_name']) ?> photo">

                                <div class="stamp-inner">
                                    <div class="stamp-top"><?= strtoupper(htmlspecialchars($review['trip_name'])) ?><br><?= strtoupper(htmlspecialchars($review['country'])) ?></div>
                                    <div class="stamp-rating"><?= $rating ?></div>
                                    <div class="stamp-bottom"><?= $stars ?><br><b>USER RATING</b><br><i><?= htmlspecialchars($review['reviewer_name']) ?></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="review-text">
                            <h2><i>"<?= htmlspecialchars($review['review_title']) ?>"</i></h2><br>
                            <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                            <p class="reviewer"><br><i><strong><?= htmlspecialchars($review['reviewer_name']) ?></strong>, <?= $formattedDate ?></i></p>
                        </div>
                    </div>
                    
                    <?php endforeach; ?>

                </div>
                
                <?php if (count($allReviews) > 3): ?>
                <button type="button" id="viewMoreBtn" onclick="showMoreReviews()">
                    <i class="fa-solid fa-angle-right"></i> VIEW MORE
                </button>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- SUBMIT REVIEW SECTION -->
        <section class="submitreview-section" id="submitreview">
            <img class="section-top" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">

            <div class="submitreview-content">
                <p class="heading">submit your review</p>
                <p class="subtext">share your experience and help other travelers discover amazing places.</p>

                <?php if ($successMessage): ?>
                    <div class="success-message">
                        <strong>Success!</strong> <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($canReview): ?>

                    <!-- REVIEW FORM (STANDARD / ADMIN ONLY) -->
                    <form class="trip-review"
                        action="userreviews.php<?= $selectedReviewId ? '?review_id=' . $selectedReviewId : '' ?>"
                        method="POST">

                        <input type="text"
                            id="reviewer-name"
                            name="reviewer-name"
                            value="<?= htmlspecialchars($_SESSION['username']) ?>"
                            readonly>

                        <input type="text"
                            id="destination"
                            name="destination"
                            list="destinations-list"
                            placeholder="Destination"
                            required autocomplete="off">

                        <datalist id="destinations-list">
                            <?php while ($trip = $tripsResult->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($trip['trip_name']) ?>">
                            <?php endwhile; ?>
                        </datalist>

                        <input type="number"
                            id="rating"
                            name="rating"
                            placeholder="Rating (1–10)"
                            min="1" max="10" required>

                        <input type="text"
                            id="title"
                            name="title"
                            placeholder="Title"
                            required>

                        <textarea id="review-text"
                                name="review-text"
                                rows="5"
                                placeholder="Write your review here..."
                                required></textarea>

                        <button type="submit" name="submit_review">
                            <i class="fa-solid fa-paper-plane"></i> SUBMIT REVIEW
                        </button>
                    </form>

                <?php else: ?>

                    <!-- GUEST NOTE -->
                    <div class="guest-note">
                        <i class="fa-solid fa-lock"></i>
                        <p><strong>ONLY REGISTERED USERS ARE ALLOWED TO ADD REVIEWS.</strong></p>
                    </div>

                <?php endif; ?>
            </div>
        </section>

        <!-- User Reviews Section -->
        <section class="reviews" id="reviews">
            <img class="section-top" src="images/sectiontop/newstop.webp" alt="" aria-hidden="true">
            <div class="reviews-content">
                <p class="heading">more reviews</p>
                <p class="subtext">check out more from different people and different places.</p>

                <!-- List of Ratings -->
                <div class="stamp-center">
                    <div class="stamp-container">

                        <?php
                            if ($discoverResult && $discoverResult->num_rows > 0):
                                while ($trip = $discoverResult->fetch_assoc()): 

                                    // Use user_rating for rating display
                                    $rating = round($trip['user_rating'] ?? $trip['rating']);

                                    // Generate stars based on User Rating
                                    $stars = str_repeat('★', $rating);

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

                        <?php endwhile; endif; ?>

                    </div>
                </div>
                <p class="stamp-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
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
            e.preventDefault();
            alert('Please enter a search term.');
        }
    });

    // Show More Reviews Function
    function showMoreReviews() {
        const hiddenReviews = document.querySelectorAll('.reviews-container.review-hidden');
        const btn = document.getElementById('viewMoreBtn');
        
        // Show next 3 reviews
        let count = 0;
        hiddenReviews.forEach(function(review) {
            if (count < 3) {
                review.classList.remove('review-hidden');
                count++;
            }
        });
        
        // Hide button if no more hidden reviews
        const remainingHidden = document.querySelectorAll('.reviews-container.review-hidden');
        if (remainingHidden.length === 0) {
            btn.style.display = 'none';
        }
    }
</script>
</body>
</html>