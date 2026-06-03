<?php
session_start();
include 'config.php';

$message = "";
$messageType = "";

// Countries

$countries = [];
$result = $conn->query("SELECT code, name FROM countries ORDER BY name ASC");

while ($row = $result->fetch_assoc()) {
    $countries[] = $row;
}

// Handle file upload
function handleProfilePicUpload() {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $filesize = $_FILES['profile_pic']['size'];
        $filetype = $_FILES['profile_pic']['type'];
        $tmp_name = $_FILES['profile_pic']['tmp_name'];
        
        // Get file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Validate file
        if (!in_array($ext, $allowed)) {
            return ['error' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
        }
        
        if ($filesize > 5000000) { // 5MB max
            return ['error' => 'File size must be less than 5MB.'];
        }
        
        // Generate unique filename
        $new_filename = uniqid('profile_', true) . '.' . $ext;
        $upload_path = 'images/profiles/' . $new_filename;
        
        // Create directory if it doesn't exist
        if (!file_exists('images/profiles')) {
            mkdir('images/profiles', 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($tmp_name, $upload_path)) {
            return ['success' => $new_filename];
        } else {
            return ['error' => 'Failed to upload file.'];
        }
    }
    
    return ['success' => 'default.png'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $country  = $_POST['country'];

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($country)) {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $messageType = "error";
    } else {
        // Handle profile picture upload
        $uploadResult = handleProfilePicUpload();
        
        if (isset($uploadResult['error'])) {
            $message = $uploadResult['error'];
            $messageType = "error";
        } else {
            $profile_pic = $uploadResult['success'];
            
            // Check if username already exists
            $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $checkUsername->bind_param("s", $username);
            $checkUsername->execute();
            $checkUsername->store_result();

            if ($checkUsername->num_rows > 0) {
                $message = "Username already taken.";
                $messageType = "error";
            } else {
                // Check if email already exists
                $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $checkEmail->bind_param("s", $email);
                $checkEmail->execute();
                $checkEmail->store_result();

                if ($checkEmail->num_rows > 0) {
                    $message = "Email already registered.";
                    $messageType = "error";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $user_type = "STANDARD";

                    $stmt = $conn->prepare("
                        INSERT INTO users (username, email, password, country, profile_pic, user_type)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->bind_param(
                        "ssssss",
                        $username,
                        $email,
                        $hashedPassword,
                        $country,
                        $profile_pic,
                        $user_type
                    );

                    if ($stmt->execute()) {
                        $message = "Registration successful! Redirecting to login...";
                        $messageType = "success";
                        header("refresh:2;url=login.php");
                    } else {
                        $message = "Registration failed: " . $stmt->error;
                        $messageType = "error";
                    }
                    $stmt->close();
                }
                $checkEmail->close();
            }
            $checkUsername->close();
        }
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
    <title>register | triptrip</title>
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
                <p class="heading">create your account</p>
                <p class="subtext">join triptrip and start exploring.</p>
            </div>
        </section>

        <!-- triptrip License Section -->
        <section class="account-section">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">

            <div class="account-content">
                <!-- PROFILE SECTION -->
                <section class="account-section profile-section">
                    <p class="heading">register</p>

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
                                <!-- PROFILE PHOTO -->
                                <div class="profile-photo">
                                    <svg id="profile-preview" width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect width="140" height="140" rx="6" fill="#e0e0e0"/>
                                        <circle cx="70" cy="50" r="25" fill="#999"/>
                                        <path d="M 30 140 Q 30 90, 70 90 Q 110 90, 110 140" fill="#999"/>
                                    </svg>
                                    <img id="preview-image" style="display:none; width: 140px; height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #aaa;" alt="Profile Preview">
                                </div>

                                <!-- REGISTRATION FORM -->
                                <div class="profile-info">
                                    <br>
                                    <!-- REGISTRATION FORM -->
                                    <form class="reglog-form" method="POST" action="register.php" enctype="multipart/form-data">
                                        <label class="profilename" for="username">
                                            Name:
                                            <input type="text" id="username" name="username" required 
                                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                                        </label>
                                        <label class="profileemail" for="email">
                                            Email:
                                            <input type="email" id="email" name="email" required 
                                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                        </label>
                                        <label class="profilepassword" for="password">
                                            Password:
                                            <input type="password" id="password" name="password" required 
                                                   placeholder="Minimum 6 characters">
                                        </label>
                                        <label for="country">
                                            Country:
                                            <select id="country" name="country" required>
                                                <option value="">SELECT A COUNTRY</option>

                                                <?php foreach ($countries as $c): ?>
                                                    <option value="<?php echo $c['code']; ?>"
                                                        <?php
                                                            if (
                                                                (isset($_POST['country']) && $_POST['country'] === $c['code'])
                                                                || (isset($user['country']) && $user['country'] === $c['code'])
                                                            ) {
                                                                echo 'selected';
                                                            }
                                                        ?>
                                                    >
                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>

                                            </select>
                                        </label>

                                        <!-- CHANGE PHOTO -->
                                        <label class="change-photo-btn">
                                            <i class="fa-solid fa-camera"></i> ADD PHOTO
                                            <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*" style="display: none;">
                                        </label>

                                        <!-- FORM BUTTONS -->
                                        <div class="form-register">
                                            <button type="submit">REGISTER</button>
                                        </div>
                                    </form>

                                </div>
                                <div class="auth-footer">
                                    <p>
                                        already have an account?
                                        <br><a href="login.php">Login here</a>
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

    // Profile Picture Preview
    document.getElementById('profile-pic-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const svg = document.getElementById('profile-preview');
                const img = document.getElementById('preview-image');
                
                svg.style.display = 'none';
                img.style.display = 'block';
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>