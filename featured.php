<?php
include 'config.php';
include 'auth.php';

// Get Search Query and Filters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Featured Trips Section
$countrySQL = "SELECT * FROM featured WHERE item_type = 'country'";
$countryResult = $conn->query($countrySQL);

$landmarkSQL = "SELECT * FROM featured WHERE item_type = 'landmark'";
$landmarkResult = $conn->query($landmarkSQL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>featured | triptrip</title>
    <link rel="stylesheet" href="css/default.css?v=<?php echo time(); ?>">

    <link rel="stylesheet" href="css/default.css">

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
                <p class="heading">featured trips</p>
                <p class="subtext">from hidden gems to iconic spots, these featured trips are crafted to inspire your next great adventure.</p>
            </div>
        </section>

        <!-- First Section / Featured Countries -->
        <section class="feafirst-section" id="countries">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/circtop.webp" alt="" aria-hidden="true">

            <div class="feafirst-content">
                <p class="heading">featured countries</p>
                <p class="subtext">countries that inspire, experiences that last.</p>

            <!-- List of Featured Countries -->
                <div class="carousel-center">
                    <div class="gallery-container">

                        <?php
                            if ($countryResult && $countryResult->num_rows > 0):
                            while ($featured = $countryResult->fetch_assoc()): ?>
                            <div class="gallery">
                                <a href="<?php echo $featured['link_url']; ?>">
                                    <img src="<?php echo $featured['image_url']; ?>" alt="<?php echo $featured['title']; ?>">
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

        <!-- Last Section -->
        <section class="fealast-section" id="spots">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/hilltop.webp" alt="" aria-hidden="true">

            <div class="fealast-content">
                <p class="heading">featured spots</p>
                <p class="subtext">from famous landmarks to secret hideaways.</p>

                <!-- List of Featured Landmarks -->
                <div class="carousel-center">
                    <div class="gallery-container">

                        <?php if ($landmarkResult && $landmarkResult->num_rows > 0): ?>
                            <?php while ($featured = $landmarkResult->fetch_assoc()): ?>
                                <div class="gallery">
                                    <a href="<?= htmlspecialchars($featured['link_url']) ?>">
                                        <img 
                                            src="<?= htmlspecialchars($featured['image_url']) ?>" 
                                            alt="<?= htmlspecialchars($featured['title']) ?>">
                                    </a>
                                    <div class="description">
                                        <?= htmlspecialchars($featured['title']) ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>

                    </div>
                </div>
            <p class="gallery-scroll-hint">←&nbsp;&nbsp;swipe to browse&nbsp;&nbsp;→</p>
            
        </section>
        <img class="section-bot" src="images/sectiontop/hilltop.webp" alt="" aria-hidden="true">
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
        e.preventDefault(); // the Search Bar Button is unfunctional
        alert('Please enter a search term.');
        }
    });
</script>
</body>
</html>