<?php
session_start();
include 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: account.php");
    exit;
}

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($email) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT id, username, email, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Redirect to account page
                header("Location: account.php");
                exit;
            } else {
                $message = "Invalid email or password.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid email or password.";
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Get Search Query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login | triptrip</title>
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
        <!-- Account Heading -->
        <section class="heading-section">
            <div class="heading-content">
                <p class="heading">welcome back</p>
                <p class="subtext">login to access your account.</p>
            </div>
        </section>

        <!-- triptrip License Section -->
        <section class="account-section">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">

            <div class="account-content">
                <!-- PROFILE SECTION -->
                <section class="account-section profile-section">
                    <p class="heading">login</p>

                    <?php if (!empty($message)): ?>
                        <div class="<?php echo $messageType; ?>-message">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-container">
                        <div class="profile-card">
                            <div class="profile-top">
                                <img class="license-logo" src="images/triptripLOGO.png" alt="logo">
                                <p class="profile-title">triptrip's License</p>
                            </div>

                            <div class="profile-body">

                                <!-- LOGIN FORM -->
                                <div class="profile-info">
                                    <br>
                                    <!-- LOGIN FORM -->
                                    <form class="reglog-form" method="POST" action="login.php">
                                        <label class="profileemail" for="email">
                                            Email:
                                            <input type="email" id="email" name="email" required 
                                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                                   placeholder="Enter your email">
                                        </label>
                                        <label class="profilepassword" for="password">
                                            Password:
                                            <input type="password" id="password" name="password" required 
                                                   placeholder="Enter your password">
                                        </label>

                                        <!-- FORM BUTTONS -->
                                        <div class="form-login">
                                            <button type="submit">LOGIN</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="auth-footer">
                                    <p>
                                        don't have an account yet?
                                        <br><a href="register.php">Register here</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- decorative bottom -->
            <img class="section-bot" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">
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