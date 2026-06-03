<?php
include 'config.php';
include 'auth.php';

$userType = $_SESSION['user_type'] ?? 'guest';
$isLoggedIn = in_array($userType, ['standard', 'admin']);
$username = $_SESSION['username'] ?? '';
$userEmail = $_SESSION['email'] ?? '';

$contactMessage = "";
$contactMessageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {

    if ($isLoggedIn) {
        $name = $username;
        $email = $userEmail;
    } else {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
    }

    $subject = trim($_POST['subject']);
    $messageContent = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($messageContent)) {
        $contactMessage = "All fields are required.";
        $contactMessageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactMessage = "Invalid email format.";
        $contactMessageType = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $messageContent);

        if ($stmt->execute()) {
            $_SESSION['contactMessage'] = "Your message has been submitted. We'll get back to you soon and always check your email!";
            $_SESSION['contactMessageType'] = "success";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $contactMessage = "Failed to submit message. Please try again later.";
            $contactMessageType = "error";
        }
        $stmt->close();
    }
}

// Check for messages in session (after redirect)
if (isset($_SESSION['contactMessage'])) {
    $contactMessage = $_SESSION['contactMessage'];
    $contactMessageType = $_SESSION['contactMessageType'];
    unset($_SESSION['contactMessage'], $_SESSION['contactMessageType']);
}

// Get Search Query and Filters
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>contact | triptrip</title>
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
        <!-- Contact Heading -->
        <section class="heading-section">
            <div class="heading-content">
                <p class="heading">contact</p>
                <p class="subtext">we're here to assist you.</p>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section">
            <img class="section-top" src="images/sectiontop/mailtop.webp" alt="" aria-hidden="true">

            <div class="contact-content">
                <!-- Contact SECTION -->
                <section class="contact-section">
                     <p class="heading">get in touch</p>
                        <p class="subtext">whether it's about destinations, plans, or feedback, we're here to help you every step of the way.</p>

                        <?php if (!empty($contactMessage)): ?>
                            <div class="<?php echo $contactMessageType; ?>-message">
                                <?php echo htmlspecialchars($contactMessage); ?>
                            </div>
                        <?php endif; ?>
                        
                    <form id="contactForm" class="contact-form" method="POST" action="">
                        
                        <?php if ($isLoggedIn): ?>
                            <!-- Logged-in user pre-filled and read-only -->
                            <input type="text" name="name" value="<?= htmlspecialchars($username) ?>" readonly>
                            <input type="email" name="email" value="<?= htmlspecialchars($userEmail) ?>" readonly>
                        <?php else: ?>
                            <!-- Guest can enter their own name/email -->
                            <input type="text" name="name" placeholder="Your Name" required>
                            <input type="email" name="email" placeholder="Your Email" required>
                        <?php endif; ?>
                        
                        <input type="text" name="subject" placeholder="Subject" required>
                        <textarea name="message" rows="5" placeholder="Write your message here..." required></textarea>
                        
                        <button type="submit" class="submit-btn" name="submit_contact">
                            <i class="fa-solid fa-paper-plane"></i> SUBMIT MESSAGE
                        </button>
                    </form>

                    <p class="alt-contact">
                        or send an email to <br>
                        <a href="mailto:triptrip.support@gmail.com">triptrip.support@gmail.com</a>
                     </p>
                </section>
            </div>
            <img class="section-bot" src="images/sectiontop/mailtop.webp" alt="" aria-hidden="true">
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
</script>
</body>
</html>