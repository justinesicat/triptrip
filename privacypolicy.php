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
    <title>privacy policy | triptrip</title>
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
        <!-- Privacy Policy Heading -->
        <section class="heading-section">
            <div class="heading-content">
                <p class="heading">privacy policy</p>
                <p class="subtext">last updated: November 4, 2025</p>
            </div>
        </section>

        <!-- Privacy Policy Section -->
        <section class="truesection">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/edgecurvetop.webp" alt="" aria-hidden="true">

            <div class="truecontent">
                <!-- POLICY CONTENT SECTION -->
                <section class="truesection">
                    <div class="truetext">
                        <p class="heading">Protecting Your Data Every Step of the Trip.</p>

                        <p>Welcome to <strong><i>triptrip</i></strong>, your trusted travel destination guide. 
                            We value your privacy and are committed to protecting your personal information. 
                            <br>This Privacy Policy explains how we collect, use, and safeguard your data when you use our website and related services.
                        </p>

                        <section class="info-collected">
                            <p class="heading">1. information we collect</p>
                            <p>When you visit or interact with <i>triptrip</i>, we may collect:<br>...</p>
                            <div class="centerlist">
                                <ul>
                                    <li><strong>Personal Information</strong> — such as your name, email address, or contact details when you voluntarily provide them (for example, signing up for newsletters, submitting feedback, or contacting us).</li>
                                    <li><strong>Usage Data</strong> — including browser type, device information, IP address, pages visited, and time spent on our site. This helps us improve user experience.</li>
                                    <li><strong>Cookies and Similar Technologies</strong> — used to enhance site performance, personalize content, and analyze traffic patterns.</li>
                                </ul>
                            </div>
                        </section>

                        <section class="how-use">
                            <p class="heading">2. how we use your information</p>
                            <div class="centerlist">
                                <ul>
                                    <li>To improve our website, services, and content recommendations.</li>
                                    <li>To respond to inquiries or feedback.</li>
                                    <li>To send updates, newsletters, or promotional offers only if you’ve opted in.</li>
                                    <li>To monitor site usage and detect or prevent technical issues or abuse.</li>
                                </ul>
                            </div>
                        </section>

                        <section class="share">
                            <p class="heading">3. data sharing and disclosure</p>
                            <p><i>triptrip</i> does <strong>not</strong> sell, rent, or trade your personal data.</p>
                            <p>We may share limited information with:<br>...</p>
                            <div class="centerlist">
                                <ul>
                                    <li><strong>Service Providers</strong> that help us operate and maintain the site (for example, analytics or hosting providers).</li>
                                    <li><strong>Legal Authorities</strong> when required by law or to protect our rights, property, or users.</li>
                                </ul>
                            </div>
                        </section>

                        <section class="cookies">
                            <p class="heading">4. cookies and tracking technologies</p>
                            <p>Cookies help us remember your preferences and analyze how you use our website. 
                                <br>You can manage or disable cookies in your browser settings.
                                <br><strong>Note:</strong> some site features may not function correctly if cookies are turned off.</p>
                        </section>

                        <section class="security">
                            <p class="heading">5. data security</p>
                            <p>We use industry-standard security measures to protect your information.
                                <br>However, no system is completely secure, and we cannot guarantee absolute protection against unauthorized access.</p>
                        </section>

                        <section class="links">
                            <p class="heading">6. links to other websites</p>
                            <p><i>triptrip</i> may contain links to external websites or social media pages. 
                                <br>We are not responsible for the privacy practices or content of those third-party sites.
                                <br>Please review their privacy policies before sharing personal information with them.</p>
                        </section>

                        <section class="your-rights">
                            <p class="heading">7. your rights</p>
                            <div class="centerlist">
                                <ul>
                                    <li>Request access to the personal data we hold about you.</li>
                                    <li>Ask us to correct or delete your information.</li>
                                    <li>Withdraw your consent for data processing (for example, unsubscribe from newsletters).</li>
                                </ul>
                            </div>
                        </section>

                        <section class="children">
                            <p class="heading">8. children's privacy</p>
                            <p><i>triptrip</i> does not knowingly collect personal data from children under 13 years old. 
                                <br>If you believe we have inadvertently collected such data, please contact us and we will promptly delete it.</p>
                        </section>

                        <section class="changes">
                            <p class="heading">9. changes to this policy</p>
                            <p>We may update this Privacy Policy from time to time. Any revisions will be posted on this page with a new “last updated” date.
                                <br>Please check this page periodically for updates.</p>
                        </section>

                        <section class="contact">
                            <p class="heading">10. contact</p>
                            <p>If you have questions or concerns about this Privacy Policy, please contact us at:<br>...</p>
                            <p><a class="email" href="mailto:triptrip.support@gmail.com">triptrip.support@gmail.com</a></p>
                        </section>

                    </div>
                </section>
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