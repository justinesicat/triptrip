<?php
include 'config.php';
include 'auth.php';

// Get Search Query and Filters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>about | triptrip</title>
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
                <p class="heading">about us</p>
                <p class="subtext">connecting explorers to the heart of every destination.</p>
            </div>
        </section>

        <!-- First Section / Introduction -->
        <section class="first-section" id="intro">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">

            <div class="first-content">
                <p class="heading">explore the world</p>
                <p class="subtext">Ready to transcend the ordinary? <i>triptrip</i> is your curated gateway to the world's most exclusive destinations and unforgettable experiences. 
                We specialize in crafting high-level, bespoke journeys for the discerning traveler. 
                Explore our hand-picked selection of luxury retreats, private expeditions, and meticulously planned itineraries designed to inspire, indulge, and redefine your sense of adventure.
                Your next extraordinary trip begins here.</p>

            </div>
        </section>

        <!-- General Section -->            
        <section class="general-section" id="core-purpose">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">
            
                <div class="general-content">
                    <div class="general-text">
                        <div class="purpose-content">
                            <div class="purpose-image">
                                <img src="images/purpose.jfif" alt="Image of a traveller">
                            </div>
                            <div class="purpose-text">
                                <p class="heading">our core purpose</p>
                                <p class="subtext">The core purpose of <i>triptrip</i> is to serve as the definitive, high quality, practical guide for the conscious traveler.
                                    We curate information that goes beyond typical tourist spots, focusing instead on ethical operators, low-impact accommodations,
                                    and immersive cultural experiences that benefit local economies directly. We simplify the complex world of responsible tourism.</p>
                            </div>
                        </div>
                    </div>
                </div>
        </section>

        <section class="general-section" id="team">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">
            
                <div class="general-content">
                    <div class="general-text">
                        <p class="heading">meet our team</p>
                        <p class="subtext">We are dedicated Computer Science students from Tarlac State University,
                            united by a passion for technology and sustainable travel. 
                            <br>Our project is to build a high-quality platform that uses preference matching to
                            guide people to destinations that truly align with their values.</p>

                        <div class="members-grid">
                            <!-- Ju -->
                            <div class="member-card">
                                <img src="images/Sicat_ID.gif" alt="Justine T. Sicat">
                                <h4>Justine Philip T. Sicat</h4>
                                <br>
                                <p>Lead Developer</p>
                            </div>
                            
                            <!-- Renz -->
                            <div class="member-card">
                                <img src="images/Ronquillo.gif" alt="Renz Derick L. Ronquillo">
                                <h4>Renz Derick L. Ronquillo</h4>
                                <br>
                                <p>Senior Developer</p>
                            </div>

                            <!-- Nic -->
                            <div class="member-card">
                                <img src="images/Salvador_ID.png" alt="Carl Niccovi P. Salvador">
                                <h4>Carl Niccovi P. Salvador</h4>
                                <br>
                                <p>Back-end Developer</p>
                            </div>

                            <!-- Channelle -->
                            <div class="member-card">
                                <img src="images/Santos_ID.gif" alt="Chanelle T. Santos">
                                <h4>Chanelle T. Santos</h4>
                                <br>
                                <p>Front-end Developer</p>
                            </div>

                            <!-- Guim -->
                            <div class="member-card">
                                <img src="images/Conos_ID.gif" alt="Guillaume Angelo C. Conos">
                                <h4>Guillaume Angelo C. Conos</h4>
                                <br>
                                <p>Information Coordinator</p>
                            </div>
                            
                            <!-- Maverick -->
                            <div class="member-card">
                                <img src="images/Dipasupil_ID.gif" alt="Maverick Adrian C. Dipasupil">
                                <h4>Maverick Adrian C. Dipasupil</h4>
                                <br>
                                <p>Database Developer</p>
                            </div>
                            
                            <!--Iman-->
                            <div class="member-card">
                                <img src="images/Kasim_ID.gif" alt="Iman Huson M. Kasim">
                                <h4>Iman Huson M. Kasim</h4>
                                <br>
                                <p>Front-end Developer, Content Specialist</p>
                            </div>
                        </div>

                    </div>
                </div>
        </section>

        <!-- Last Section -->
        <section class="last-section" id="missionvision">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">

            <div class="last-content">
                <p class="heading">mission and vision</p>
                <div class="misvis-content">
                    <div class="mission">
                        <h3>Our Mission</h3>
                        <p>To guide and inspire travelers to make conscious choices that protect the environment, respect cultures, and support local economies. We provide the tools and resources necessary to transform every journey into a force for good.</p>
                    </div>
                    <div class="vision">
                        <h3>Our Vision</h3>
                        <p>A world where mindful travel is the norm, not the niche. We envision a future where exploring our planet actively contributes to its preservation and well-being, ensuring its beauty and diversity endure for every generation to come.</p>
                    </div>
                </div>
            </div>

        </section>
        <img class="section-bot" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">
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