<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userType = $_SESSION['user_type'] ?? 'guest';

$message = "";
$messageType = "";
$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT id, username, email, country, profile_pic, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Get countries for dropdown
$countries = [];
$result = $conn->query("SELECT code, name FROM countries ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $countries[] = $row;
}

// Handle profile picture upload
function handleProfilePicUpload($old_pic) {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_pic']['name'];
        $filesize = $_FILES['profile_pic']['size'];
        $tmp_name = $_FILES['profile_pic']['tmp_name'];
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            return ['error' => 'Only JPG, JPEG, PNG & GIF files are allowed.'];
        }
        
        if ($filesize > 5000000) {
            return ['error' => 'File size must be less than 5MB.'];
        }
        
        $new_filename = uniqid('profile_', true) . '.' . $ext;
        $upload_path = 'images/profiles/' . $new_filename;
        
        if (!file_exists('images/profiles')) {
            mkdir('images/profiles', 0755, true);
        }
        
        if (move_uploaded_file($tmp_name, $upload_path)) {
            // Delete old profile pic if it's not default
            if ($old_pic !== 'default.png' && file_exists('images/profiles/' . $old_pic)) {
                unlink('images/profiles/' . $old_pic);
            }
            return ['success' => $new_filename];
        } else {
            return ['error' => 'Failed to upload file.'];
        }
    }
    
    return ['success' => $old_pic];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $country = $_POST['country'];

    // Validation
    if (empty($username) || empty($email) || empty($country)) {
        $message = "Name, email, and country are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "error";
    } else {
        // Check if email is taken by another user
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $user_id);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $message = "Email is already taken by another user.";
            $messageType = "error";
        } else {
            // Handle profile picture
            $uploadResult = handleProfilePicUpload($user['profile_pic']);
            
            if (isset($uploadResult['error'])) {
                $message = $uploadResult['error'];
                $messageType = "error";
            } else {
                $profile_pic = $uploadResult['success'];
                
                // Update profile
                if (!empty($password)) {
                    // Update with new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, country = ?, profile_pic = ? WHERE id = ?");
                    $updateStmt->bind_param("sssssi", $username, $email, $hashedPassword, $country, $profile_pic, $user_id);
                } else {
                    // Update without changing password
                    $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, country = ?, profile_pic = ? WHERE id = ?");
                    $updateStmt->bind_param("ssssi", $username, $email, $country, $profile_pic, $user_id);
                }

                if ($updateStmt->execute()) {
                    $message = "Profile updated successfully!";
                    $messageType = "success";
                    
                    // Refresh user data
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['country'] = $country;
                    $user['profile_pic'] = $profile_pic;
                } else {
                    $message = "Failed to update profile.";
                    $messageType = "error";
                }
                $updateStmt->close();
            }
        }
        $checkEmail->close();
    }
}

// Get country name
$country_name = "Unknown";
foreach ($countries as $c) {
    if ($c['code'] === $user['country']) {
        $country_name = $c['name'];
        break;
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
    <title>account | triptrip</title>
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
                <p class="heading">your account</p>
                <p class="subtext">manage your profile in one place.</p>
            </div>
        </section>

        <!-- triptrip License Section -->
        <section class="account-section">
            <!-- decorative top -->
            <img class="section-top" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">

            <div class="account-content">
                <!-- PROFILE SECTION -->
                <section class="account-section profile-section">
                    <p class="heading">profile information</p>

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
                                    <?php 
                                    $profilePicPath = 'images/profiles/' . $user['profile_pic'];
                                    if ($user['profile_pic'] !== 'default.png' && file_exists($profilePicPath)): 
                                    ?>
                                        <img id="profile-display" src="<?php echo $profilePicPath; ?>" alt="Profile Picture">
                                    <?php else: ?>
                                        <svg id="profile-display" width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="140" height="140" rx="6" fill="#e0e0e0"/>
                                            <circle cx="70" cy="50" r="25" fill="#999"/>
                                            <path d="M 30 140 Q 30 90, 70 90 Q 110 90, 110 140" fill="#999"/>
                                        </svg>
                                    <?php endif; ?>
                                    <img id="preview-image" style="display:none; width: 140px; height: 140px; object-fit: cover; border-radius: 6px; border: 1px solid #aaa;" alt="Profile Preview">
                                </div>

                                <!-- DISPLAYED INFO -->
                                <div class="profile-info">
                                    <div id="display-mode">
                                        <p><strong>Name:</strong> <span id="display-name"><?php echo htmlspecialchars($user['username']); ?></span></p>
                                        <p><strong>Email:</strong> <span id="display-email"><?php echo htmlspecialchars($user['email']); ?></span></p>
                                        <p><strong>Country:</strong> <span id="display-country"><?php echo htmlspecialchars($country_name); ?></span></p>

                                        <!-- EDIT PROFILE BUTTON -->
                                        <button id="edit-btn" class="edit-btn">Edit Profile</button>
                                    </div>

                                    <!-- INLINE EDIT FORM (hidden by default) -->
                                    <form class="edit-form" method="POST" action="account.php" enctype="multipart/form-data">
                                        <label class="profilename" for="username">
                                            Name:
                                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </label>
                                        <label class="profileemail" for="email">
                                            Email:
                                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </label>
                                        <label class="profilepassword" for="password">
                                            Password:
                                            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                                        </label>
                                        <label for="country">
                                            Country:
                                            <select id="country" name="country" required>
                                                <option value="">SELECT A COUNTRY</option>
                                                <?php foreach ($countries as $c): ?>
                                                    <option value="<?php echo $c['code']; ?>" <?php echo ($user['country'] === $c['code']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($c['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>

                                        <!-- CHANGE PHOTO -->
                                        <label class="change-photo-btn">
                                            <i class="fa-solid fa-camera"></i> Change Photo
                                            <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*" style="display: none;">
                                        </label>

                                        <!-- FORM BUTTONS -->
                                        <div class="form-buttons">
                                            <button type="submit">Save</button>
                                            <button type="button" id="cancel-btn">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ADMIN SECTION -->
                     <?php if (($userType ?? '') === 'admin'): ?>
                        <div class="admin-section">
                            <a href="dashboard.php" class="dashboard-btn"><i class="fa-solid fa-screwdriver-wrench"></i> Dashboard</a>
                            <br>
                            <a href="manage.php" class="manage-btn"><i class="fa-solid fa-screwdriver-wrench"></i> Modify Guides</a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- LOGOUT SECTION -->
                    <div class="logout-section">
                        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                    </div>
                </section>
            </div>

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

    // Edit Profile Toggle
    const editBtn = document.getElementById('edit-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const displayMode = document.getElementById('display-mode');
    const editForm = document.querySelector('.edit-form');

    editBtn.addEventListener('click', function () {
        displayMode.style.display = 'none';
        editForm.style.display = 'flex';
    });

    cancelBtn.addEventListener('click', function () {
        displayMode.style.display = 'block';
        editForm.style.display = 'none';

        editForm.reset();
        document.getElementById('profile-display').style.display = 'block';
        document.getElementById('preview-image').style.display = 'none';
    });

    // Profile Picture Preview
    document.getElementById('profile-pic-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const profileDisplay = document.getElementById('profile-display');
                const previewImage = document.getElementById('preview-image');
                
                profileDisplay.style.display = 'none';
                previewImage.style.display = 'block';
                previewImage.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>