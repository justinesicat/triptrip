<?php
include 'config.php';
include 'auth.php';

// Check if user is admin
$userType = $_SESSION['user_type'] ?? 'guest';
if ($userType !== 'admin') {
    header("Location: index.php");
    exit;
}

$success_message = '';
$error_message = '';

// ===== EDIT MODE FUNCTIONALITY =====
$editMode = false;
$editData = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editMode = true;
    $edit_id = intval($_GET['edit']);
    
    // Fetch destination data
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    
    if (!$editData) {
        $_SESSION['error_message'] = "Destination not found!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit;
    }
    
    // Fetch related data
    $editData['spots'] = $conn->query("SELECT * FROM tourist_spots WHERE destination_id = $edit_id")->fetch_all(MYSQLI_ASSOC);
    $editData['foods'] = $conn->query("SELECT * FROM local_foods WHERE destination_id = $edit_id")->fetch_all(MYSQLI_ASSOC);
    $editData['traditions'] = $conn->query("SELECT * FROM traditions WHERE destination_id = $edit_id")->fetch_all(MYSQLI_ASSOC);
    $editData['events'] = $conn->query("SELECT * FROM events WHERE destination_id = $edit_id")->fetch_all(MYSQLI_ASSOC);
    $editData['tips'] = $conn->query("SELECT * FROM travel_tips WHERE destination_id = $edit_id")->fetch_all(MYSQLI_ASSOC);
    $editData['gallery'] = $conn->query("SELECT * FROM destination_gallery WHERE destination_id = $edit_id")->fetch_all(MYSQLI_ASSOC);
    
    // Fetch trip data if exists
    $tripData = $conn->query("SELECT * FROM trips WHERE destination_id = $edit_id LIMIT 1")->fetch_assoc();
    $editData['trip'] = $tripData;
}

// Handle Update Destination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_destination'])) {
    try {
        $conn->begin_transaction();
        
        $dest_id = intval($_POST['destination_id']);
        $destination_name = strtolower(trim($_POST['destination_name']));
        $slug = strtolower(str_replace(' ', '-', $destination_name));
        $tagline = $_POST['tagline'];
        $intro_image = $_POST['intro_image'];
        $introduction = $_POST['introduction'];
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
        
        // Update main destination
        $stmt = $conn->prepare("UPDATE destinations SET destination_name = ?, slug = ?, tagline = ?, intro_image = ?, introduction = ?, parent_id = ? WHERE id = ?");
        $stmt->bind_param("sssssii", $destination_name, $slug, $tagline, $intro_image, $introduction, $parent_id, $dest_id);
        $stmt->execute();
        
        // Delete and re-insert tourist spots
        $conn->query("DELETE FROM tourist_spots WHERE destination_id = $dest_id");
        if (!empty($_POST['spot_name'])) {
            $stmt = $conn->prepare("INSERT INTO tourist_spots (destination_id, spot_name, description, image_url, entrance_fee_adult, entrance_fee_child, schedule, how_to_get_there, best_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($_POST['spot_name'] as $index => $spot_name) {
                if (!empty($spot_name)) {
                    $description = $_POST['spot_description'][$index] ?? '';
                    $image_url = $_POST['spot_image'][$index] ?? '';
                    $fee_adult = $_POST['spot_fee_adult'][$index] ?? '';
                    $fee_child = $_POST['spot_fee_child'][$index] ?? '';
                    $schedule = $_POST['spot_schedule'][$index] ?? '';
                    $how_to_get = $_POST['spot_how_to_get'][$index] ?? '';
                    $best_time = $_POST['spot_best_time'][$index] ?? '';
                    $stmt->bind_param("issssssss", $dest_id, $spot_name, $description, $image_url, $fee_adult, $fee_child, $schedule, $how_to_get, $best_time);
                    $stmt->execute();
                }
            }
        }
        
        // Delete and re-insert local foods
        $conn->query("DELETE FROM local_foods WHERE destination_id = $dest_id");
        if (!empty($_POST['food_name'])) {
            $stmt = $conn->prepare("INSERT INTO local_foods (destination_id, food_name, description, image_url) VALUES (?, ?, ?, ?)");
            foreach ($_POST['food_name'] as $index => $food_name) {
                if (!empty($food_name)) {
                    $food_desc = $_POST['food_description'][$index] ?? '';
                    $food_image = $_POST['food_image'][$index] ?? '';
                    $stmt->bind_param("isss", $dest_id, $food_name, $food_desc, $food_image);
                    $stmt->execute();
                }
            }
        }
        
        // Delete and re-insert traditions
        $conn->query("DELETE FROM traditions WHERE destination_id = $dest_id");
        if (!empty($_POST['tradition_title'])) {
            $stmt = $conn->prepare("INSERT INTO traditions (destination_id, tradition_title, tradition_description) VALUES (?, ?, ?)");
            foreach ($_POST['tradition_title'] as $index => $tradition_title) {
                if (!empty($tradition_title)) {
                    $tradition_desc = $_POST['tradition_description'][$index] ?? '';
                    $stmt->bind_param("iss", $dest_id, $tradition_title, $tradition_desc);
                    $stmt->execute();
                }
            }
        }
        
        // Delete and re-insert events
        $conn->query("DELETE FROM events WHERE destination_id = $dest_id");
        if (!empty($_POST['event_name'])) {
            $stmt = $conn->prepare("INSERT INTO events (destination_id, event_name, event_description, image_url) VALUES (?, ?, ?, ?)");
            foreach ($_POST['event_name'] as $index => $event_name) {
                if (!empty($event_name)) {
                    $event_desc = $_POST['event_description'][$index] ?? '';
                    $event_image = $_POST['event_image'][$index] ?? '';
                    $stmt->bind_param("isss", $dest_id, $event_name, $event_desc, $event_image);
                    $stmt->execute();
                }
            }
        }
        
        // Delete and re-insert travel tips
        $conn->query("DELETE FROM travel_tips WHERE destination_id = $dest_id");
        if (!empty($_POST['tip_title'])) {
            $stmt = $conn->prepare("INSERT INTO travel_tips (destination_id, tip_title, tip_description) VALUES (?, ?, ?)");
            foreach ($_POST['tip_title'] as $index => $tip_title) {
                if (!empty($tip_title)) {
                    $tip_desc = $_POST['tip_description'][$index] ?? '';
                    $stmt->bind_param("iss", $dest_id, $tip_title, $tip_desc);
                    $stmt->execute();
                }
            }
        }
        
        // Delete and re-insert gallery
        $conn->query("DELETE FROM destination_gallery WHERE destination_id = $dest_id");
        if (!empty($_POST['gallery_image'])) {
            $stmt = $conn->prepare("INSERT INTO destination_gallery (destination_id, image_url, alt_text) VALUES (?, ?, ?)");
            foreach ($_POST['gallery_image'] as $index => $gallery_image) {
                if (!empty($gallery_image)) {
                    $alt_text = $_POST['gallery_alt'][$index] ?? '';
                    $stmt->bind_param("iss", $dest_id, $gallery_image, $alt_text);
                    $stmt->execute();
                }
            }
        }
        
        // Update trips table if parent exists
        if ($parent_id !== NULL) {
            $trip_name = ucwords($destination_name);
            $destination_category = $_POST['destination_category'] ?? 'City';
            $trip_type = $_POST['trip_type'] ?? 'Leisure';
            $location = $_POST['location'] ?? 'Local';
            $price = !empty($_POST['price']) ? floatval($_POST['price']) : 0.00;
            $description = $tagline;
            $image_url = $intro_image;
            
            // Check if trip exists
            $check = $conn->query("SELECT id FROM trips WHERE destination_id = $dest_id");
            if ($check->num_rows > 0) {
                // Update existing trip
                $stmt = $conn->prepare("UPDATE trips SET trip_name = ?, destination_category = ?, trip_type = ?, location = ?, price = ?, description = ?, image_url = ? WHERE destination_id = ?");
                $stmt->bind_param("ssssdssi", $trip_name, $destination_category, $trip_type, $location, $price, $description, $image_url, $dest_id);
                $stmt->execute();
            } else {
                // Insert new trip
                $stmt = $conn->prepare("INSERT INTO trips (destination_id, trip_name, destination_category, trip_type, location, price, rating, description, image_url) VALUES (?, ?, ?, ?, ?, ?, 0.0, ?, ?)");
                $stmt->bind_param("issssdss", $dest_id, $trip_name, $destination_category, $trip_type, $location, $price, $description, $image_url);
                $stmt->execute();
            }
        } else {
            // Remove from trips if changed to country
            $conn->query("DELETE FROM trips WHERE destination_id = $dest_id");
        }
        
        $conn->commit();
        
        $_SESSION['success_message'] = "Destination '$destination_name' updated successfully!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=updated");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating destination: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit;
    }
}

// Handle Delete Destination
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $delete_id = intval($_GET['delete']);
        $conn->begin_transaction();
        
        // Delete related records first (foreign key constraints)
        $conn->query("DELETE FROM tourist_spots WHERE destination_id = $delete_id");
        $conn->query("DELETE FROM local_foods WHERE destination_id = $delete_id");
        $conn->query("DELETE FROM traditions WHERE destination_id = $delete_id");
        $conn->query("DELETE FROM events WHERE destination_id = $delete_id");
        $conn->query("DELETE FROM travel_tips WHERE destination_id = $delete_id");
        $conn->query("DELETE FROM destination_gallery WHERE destination_id = $delete_id");
        $conn->query("DELETE FROM trips WHERE destination_id = $delete_id");
        
        // Delete the destination itself
        $conn->query("DELETE FROM destinations WHERE id = $delete_id");
        
        $conn->commit();
        $_SESSION['success_message'] = "Destination deleted successfully!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=deleted");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting destination: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit;
    }
}

// PRG Pattern - Handle Parent Destination Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_parent'])) {
    try {
        $parent_name = isset($_POST['parent_dest_name']) ? strtolower(trim($_POST['parent_dest_name'])) : null;
        $parent_slug = $parent_name ? str_replace(' ', '-', $parent_name) : null;
        $parent_tagline = isset($_POST['parent_tagline']) ? trim($_POST['parent_tagline']) : null;
        $parent_intro_image = isset($_POST['parent_intro_image']) ? trim($_POST['parent_intro_image']) : null;
        $parent_introduction = isset($_POST['parent_introduction']) ? trim($_POST['parent_introduction']) : null;

        if (!$parent_name) {
            throw new Exception("Parent destination name is required.");
        }

        $stmt = $conn->prepare("INSERT INTO destinations (destination_name, slug, tagline, intro_image, introduction, parent_id) VALUES (?, ?, ?, ?, ?, NULL)");
        $stmt->bind_param("sssss", $parent_name, $parent_slug, $parent_tagline, $parent_intro_image, $parent_introduction);
        $stmt->execute();

        // PRG: Redirect to prevent duplicate submission
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=parent_created");
        exit;

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error creating parent destination: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit;
    }
}

// PRG Pattern - Handle Main Destination Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['create_parent'])) {
    try {
        $conn->begin_transaction();
        
        // Insert main destination
        $destination_name = strtolower(trim($_POST['destination_name']));
        $slug = strtolower(str_replace(' ', '-', $destination_name));
        $tagline = $_POST['tagline'];
        $intro_image = $_POST['intro_image'];
        $introduction = $_POST['introduction'];
        $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;
        
        $stmt = $conn->prepare("INSERT INTO destinations (destination_name, slug, tagline, intro_image, introduction, parent_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $destination_name, $slug, $tagline, $intro_image, $introduction, $parent_id);
        $stmt->execute();
        $destination_id = $conn->insert_id;
        
        // Insert Tourist Spots
        if (!empty($_POST['spot_name'])) {
            $stmt = $conn->prepare("INSERT INTO tourist_spots (destination_id, spot_name, description, image_url, entrance_fee_adult, entrance_fee_child, schedule, how_to_get_there, best_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['spot_name'] as $index => $spot_name) {
                if (!empty($spot_name)) {
                    $description = $_POST['spot_description'][$index] ?? '';
                    $image_url = $_POST['spot_image'][$index] ?? '';
                    $fee_adult = $_POST['spot_fee_adult'][$index] ?? '';
                    $fee_child = $_POST['spot_fee_child'][$index] ?? '';
                    $schedule = $_POST['spot_schedule'][$index] ?? '';
                    $how_to_get = $_POST['spot_how_to_get'][$index] ?? '';
                    $best_time = $_POST['spot_best_time'][$index] ?? '';
                    
                    $stmt->bind_param("issssssss", $destination_id, $spot_name, $description, $image_url, $fee_adult, $fee_child, $schedule, $how_to_get, $best_time);
                    $stmt->execute();
                }
            }
        }
        
        // Insert Local Foods
        if (!empty($_POST['food_name'])) {
            $stmt = $conn->prepare("INSERT INTO local_foods (destination_id, food_name, description, image_url) VALUES (?, ?, ?, ?)");
            
            foreach ($_POST['food_name'] as $index => $food_name) {
                if (!empty($food_name)) {
                    $food_desc = $_POST['food_description'][$index] ?? '';
                    $food_image = $_POST['food_image'][$index] ?? '';
                    
                    $stmt->bind_param("isss", $destination_id, $food_name, $food_desc, $food_image);
                    $stmt->execute();
                }
            }
        }
        
        // Insert Traditions
        if (!empty($_POST['tradition_title'])) {
            $stmt = $conn->prepare("INSERT INTO traditions (destination_id, tradition_title, tradition_description) VALUES (?, ?, ?)");
            
            foreach ($_POST['tradition_title'] as $index => $tradition_title) {
                if (!empty($tradition_title)) {
                    $tradition_desc = $_POST['tradition_description'][$index] ?? '';
                    
                    $stmt->bind_param("iss", $destination_id, $tradition_title, $tradition_desc);
                    $stmt->execute();
                }
            }
        }
        
        // Insert Events
        if (!empty($_POST['event_name'])) {
            $stmt = $conn->prepare("INSERT INTO events (destination_id, event_name, event_description, image_url) VALUES (?, ?, ?, ?)");
            
            foreach ($_POST['event_name'] as $index => $event_name) {
                if (!empty($event_name)) {
                    $event_desc = $_POST['event_description'][$index] ?? '';
                    $event_image = $_POST['event_image'][$index] ?? '';
                    
                    $stmt->bind_param("isss", $destination_id, $event_name, $event_desc, $event_image);
                    $stmt->execute();
                }
            }
        }
        
        // Insert Travel Tips
        if (!empty($_POST['tip_title'])) {
            $stmt = $conn->prepare("INSERT INTO travel_tips (destination_id, tip_title, tip_description) VALUES (?, ?, ?)");
            
            foreach ($_POST['tip_title'] as $index => $tip_title) {
                if (!empty($tip_title)) {
                    $tip_desc = $_POST['tip_description'][$index] ?? '';
                    
                    $stmt->bind_param("iss", $destination_id, $tip_title, $tip_desc);
                    $stmt->execute();
                }
            }
        }
        
        // Insert Gallery Images
        if (!empty($_POST['gallery_image'])) {
            $stmt = $conn->prepare("INSERT INTO destination_gallery (destination_id, image_url, alt_text) VALUES (?, ?, ?)");
            
            foreach ($_POST['gallery_image'] as $index => $gallery_image) {
                if (!empty($gallery_image)) {
                    $alt_text = $_POST['gallery_alt'][$index] ?? '';
                    
                    $stmt->bind_param("iss", $destination_id, $gallery_image, $alt_text);
                    $stmt->execute();
                }
            }
        }
        
        // NEW: Insert into trips table for user reviews (only if NOT a parent/country destination)
        if ($parent_id !== NULL) {
            $trip_name = ucwords($destination_name);
            $destination_category = $_POST['destination_category'] ?? 'City';
            $trip_type = $_POST['trip_type'] ?? 'Leisure';
            $location = $_POST['location'] ?? 'Local';
            $price = !empty($_POST['price']) ? floatval($_POST['price']) : 0.00;
            $description = $tagline;
            $image_url = $intro_image;
            
            $stmt = $conn->prepare("INSERT INTO trips (destination_id, trip_name, destination_category, trip_type, location, price, rating, description, image_url) VALUES (?, ?, ?, ?, ?, ?, 0.0, ?, ?)");
            $stmt->bind_param("issssdss", $destination_id, $trip_name, $destination_category, $trip_type, $location, $price, $description, $image_url);
            $stmt->execute();
        }
        
        $conn->commit();
        
        // Store in session for success message
        $_SESSION['created_destination'] = $destination_name;
        
        // PRG: Redirect to prevent duplicate submission
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=destination_created");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error creating destination: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "?error=1");
        exit;
    }
}

// Display messages after redirect
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'parent_created') {
        $success_message = "Parent destination created successfully! You can now select it from the dropdown.";
    } elseif ($_GET['success'] === 'destination_created' && isset($_SESSION['created_destination'])) {
        $success_message = "Destination '" . htmlspecialchars($_SESSION['created_destination']) . "' created successfully!";
        unset($_SESSION['created_destination']);
    } elseif ($_GET['success'] === 'deleted' && isset($_SESSION['success_message'])) {
        $success_message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    } elseif ($_GET['success'] === 'updated' && isset($_SESSION['success_message'])) {
        $success_message = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    }
}

if (isset($_GET['error']) && isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Get existing destinations for parent dropdown
$destinations_query = "SELECT id, destination_name FROM destinations WHERE parent_id IS NULL OR parent_id = 0 ORDER BY destination_name";
$destinations_result = $conn->query($destinations_query);

// Get all destinations for management table
$manage_query = "
    SELECT 
        d.id,
        d.destination_name,
        d.tagline,
        d.parent_id,
        p.destination_name as parent_name,
        (SELECT COUNT(*) FROM tourist_spots WHERE destination_id = d.id) as spots_count,
        (SELECT COUNT(*) FROM trips WHERE destination_id = d.id) as in_trips
    FROM destinations d
    LEFT JOIN destinations p ON d.parent_id = p.id
    ORDER BY d.destination_name
";
$manage_result = $conn->query($manage_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>create | triptrip</title>
    <link rel="stylesheet" href="css/default.css?v=<?php echo time(); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Luxurious+Script&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<header>
    <div class="logo-search">
        <img class="logo-white" src="images/triptripLOGO.png" alt="logo">
        <div class="search-bar">
            <form id="search-form" action="search.php" method="GET">
                <input type="text" name="q" id="search-input" placeholder="SEARCH...">
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>
    </div>
    <input type="checkbox" id="menu-toggle">
    <label for="menu-toggle" class="menu-icon">
        <i class="fa-solid fa-bars"></i>
    </label>
    <label for="menu-toggle" class="overlay"></label>
    <nav id="nav-drawer" class="nav-drawer">
        <label for="menu-toggle" class="close-icon">
            <i class="fa-solid fa-xmark"></i>
        </label>
        <ul class="nav_links">
            <li><a href="index.php"><i class="fa-solid fa-house"></i> HOME</a></li>
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
    <section class="heading-section">
        <div class="heading-content">
            <p class="heading">modify guides</p>
            <p class="subtext">add, change, delete travel destination guides for everyone.</p>
        </div>
    </section>

    <section class="contact-section">
        <img class="section-top" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">
        <div class="contact-content">
            <p class="heading">add / change</p>
                
                <?php if ($success_message): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="error-message"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php if ($editMode): ?>
                        <!-- Hidden fields for edit mode -->
                        <input type="hidden" name="update_destination" value="1">
                        <input type="hidden" name="destination_id" value="<?php echo $editData['id']; ?>">
                        
                        <div class="edit-mode-banner" style="background: #2196F3; color: white; padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px;">
                            <a><i class="fa-solid fa-edit"></i> <strong>EDIT MODE:</strong> Updating "<?php echo htmlspecialchars(ucwords($editData['destination_name'])); ?>"</a>
                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>" style="float: right; color: white; text-decoration: underline;">Cancel & Return to Create</a>
                        </div>
                    <?php endif; ?>

                    <!-- Basic Information -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-info-circle"></i> Basic Information</h2>
                        
                        <div class="form-group">
                            <label for="destination_name">Destination Name <span class="red">*</span></label>
                            <input type="text" id="destination_name" name="destination_name" 
                                value="<?php echo $editMode ? htmlspecialchars($editData['destination_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="parent_id">Parent Destination (optional - for cities/landmarks)</label>
                            <select id="parent_id" name="parent_id">
                                <option value="" <?php echo ($editMode && !$editData['parent_id']) ? 'selected' : ''; ?>>None (This is a country)</option>
                                <?php 
                                // Re-query destinations to avoid pointer issues
                                $parent_options = $conn->query("SELECT id, destination_name FROM destinations WHERE parent_id IS NULL OR parent_id = 0 ORDER BY destination_name");
                                while ($dest = $parent_options->fetch_assoc()): 
                                    // Don't show the current destination as a parent option when editing
                                    if ($editMode && $dest['id'] == $editData['id']) continue;
                                ?>
                                    <option value="<?php echo $dest['id']; ?>" 
                                            <?php echo ($editMode && $editData['parent_id'] == $dest['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucwords($dest['destination_name'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <label class="create-parent">
                                <i class="fa-solid fa-info-circle"></i> <span class="create-parent-text">if the parent destination doesn't exist yet,</span>
                                <a href="#" class="create" id="create-parent-link">create it first</a>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="tagline">Tagline <span class="red">*</span></label>
                            <input type="text" id="tagline" name="tagline" placeholder="A catchy description" 
                                value="<?php echo $editMode ? htmlspecialchars($editData['tagline']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="intro_image">Introduction Image URL <span class="red">*</span></label>
                            <input type="url" id="intro_image" name="intro_image" placeholder="https://example.com/image.jpg" 
                                value="<?php echo $editMode ? htmlspecialchars($editData['intro_image']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="introduction">Introduction Text <span class="red">*</span></label>
                            <textarea id="introduction" name="introduction" required><?php echo $editMode ? htmlspecialchars($editData['introduction']) : ''; ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Trip Classification -->
                    <div class="form-section" id="trip-classification-section">
                        <h2><i class="fa-solid fa-tag"></i> Trip Classification</h2>
                        <p style="color: #666; margin-bottom: 1rem;">These details will be used for the user reviews section.</p>
                        
                        <div class="form-group">
                            <label for="destination_category">Destination Category</label>
                            <select id="destination_category" name="destination_category">
                                <?php
                                $categories = ['Beach', 'Nature', 'City', 'Heritage'];
                                $selected_category = ($editMode && isset($editData['trip']['destination_category'])) ? $editData['trip']['destination_category'] : 'City';
                                foreach ($categories as $cat):
                                ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($selected_category == $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="trip_type">Trip Type</label>
                            <select id="trip_type" name="trip_type">
                                <?php
                                $types = ['Adventure', 'Leisure', 'Cultural'];
                                $selected_type = ($editMode && isset($editData['trip']['trip_type'])) ? $editData['trip']['trip_type'] : 'Leisure';
                                foreach ($types as $type):
                                ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($selected_type == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location</label>
                            <select id="location" name="location">
                                <?php
                                $locations = ['Local', 'International'];
                                $selected_location = ($editMode && isset($editData['trip']['location'])) ? $editData['trip']['location'] : 'Local';
                                foreach ($locations as $loc):
                                ?>
                                    <option value="<?php echo $loc; ?>" <?php echo ($selected_location == $loc) ? 'selected' : ''; ?>><?php echo $loc; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Estimated Price (PHP)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" placeholder="e.g., 5000.00"
                                value="<?php echo ($editMode && isset($editData['trip']['price'])) ? htmlspecialchars($editData['trip']['price']) : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Tourist Spots -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-map-marker-alt"></i> Tourist Spots</h2>
                        <div id="spots-container">
                            <div class="repeater-item">
                                <h4>Spot #1</h4>
                                <div class="form-group">
                                    <label>Spot Name</label>
                                    <input type="text" name="spot_name[]" placeholder="Name of the tourist spot">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="spot_description[]"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Image URL</label>
                                    <input type="url" name="spot_image[]" placeholder="https://example.com/image.jpg">
                                </div>
                                <div class="form-group">
                                    <label>Entrance Fee (Adult)</label>
                                    <input type="text" name="spot_fee_adult[]" placeholder="e.g., PHP 100">
                                </div>
                                <div class="form-group">
                                    <label>Entrance Fee (Child)</label>
                                    <input type="text" name="spot_fee_child[]" placeholder="e.g., PHP 50">
                                </div>
                                <div class="form-group">
                                    <label>Schedule</label>
                                    <textarea name="spot_schedule[]" placeholder="Opening hours and days"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>How to Get There</label>
                                    <textarea name="spot_how_to_get[]"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Best Time to Visit</label>
                                    <textarea name="spot_best_time[]"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addSpot()"><i class="fa-solid fa-plus"></i> Add Another Spot</button>
                    </div>
                    
                    <!-- Local Foods -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-utensils"></i> Local Foods</h2>
                        <div id="foods-container">
                            <div class="repeater-item">
                                <h4>Food #1</h4>
                                <div class="form-group">
                                    <label>Food Name</label>
                                    <input type="text" name="food_name[]">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="food_description[]"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Image URL</label>
                                    <input type="url" name="food_image[]" placeholder="https://example.com/image.jpg">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addFood()"><i class="fa-solid fa-plus"></i> Add Another Food</button>
                    </div>
                    
                    <!-- Traditions -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-scroll"></i> Traditions</h2>
                        <div id="traditions-container">
                            <div class="repeater-item">
                                <h4>Tradition #1</h4>
                                <div class="form-group">
                                    <label>Tradition Title</label>
                                    <input type="text" name="tradition_title[]">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="tradition_description[]"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addTradition()"><i class="fa-solid fa-plus"></i> Add Another Tradition</button>
                    </div>
                    
                    <!-- Events -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-calendar"></i> Events</h2>
                        <div id="events-container">
                            <div class="repeater-item">
                                <h4>Event #1</h4>
                                <div class="form-group">
                                    <label>Event Name</label>
                                    <input type="text" name="event_name[]">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="event_description[]"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Image URL</label>
                                    <input type="url" name="event_image[]" placeholder="https://example.com/image.jpg">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addEvent()"><i class="fa-solid fa-plus"></i> Add Another Event</button>
                    </div>
                    
                    <!-- Travel Tips -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-lightbulb"></i> Travel Tips</h2>
                        <div id="tips-container">
                            <div class="repeater-item">
                                <h4>Tip #1</h4>
                                <div class="form-group">
                                    <label>Tip Title</label>
                                    <input type="text" name="tip_title[]">
                                </div>
                                <div class="form-group">
                                    <label>Tip Description</label>
                                    <textarea name="tip_description[]"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addTip()"><i class="fa-solid fa-plus"></i> Add Another Tip</button>
                    </div>
                    
                    <!-- Gallery -->
                    <div class="form-section">
                        <h2><i class="fa-solid fa-images"></i> Gallery</h2>
                        <div id="gallery-container">
                            <div class="repeater-item">
                                <h4>Image #1</h4>
                                <div class="form-group">
                                    <label>Image URL</label>
                                    <input type="url" name="gallery_image[]" placeholder="https://example.com/image.jpg">
                                </div>
                                <div class="form-group">
                                    <label>Alt Text / Caption</label>
                                    <input type="text" name="gallery_alt[]">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-add" onclick="addGalleryImage()"><i class="fa-solid fa-plus"></i> Add Another Image</button>
                    </div>
        
        <button type="submit" class="btn-submit">
            <i class="fa-solid fa-<?php echo $editMode ? 'save' : 'plus-circle'; ?>"></i> 
            <?php echo $editMode ? 'Update Destination' : 'Create Destination'; ?>
        </button>
        </form>
        
        <!-- Manage Destinations Section -->
        <?php if (!$editMode): ?>
        <div class="manage-section">
            <p class="heading">manage</p>
        <div class="contact-content">
            
            <div class="messages-table-wrapper">
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Destination Name</th>
                            <th>Tagline</th>
                            <th>Type</th>
                            <th>Parent</th>
                            <th>In Trips</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($manage_result && $manage_result->num_rows > 0): ?>
                            <?php while ($row = $manage_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars(ucwords($row['destination_name'])); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['tagline'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if ($row['parent_id']): ?>
                                            <span class="badge badge-city">City / Landmark</span>
                                        <?php else: ?>
                                            <span class="badge badge-country">Country</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($row['parent_name']) {
                                            echo htmlspecialchars(ucwords($row['parent_name']));
                                        } else {
                                            echo '<em style="color: #999;">None</em>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['in_trips'] > 0): ?>
                                            <span class="badge badge-yes">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-no">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?edit=<?php echo $row['id']; ?>" 
                                               class="btn-action btn-view" 
                                               title="Edit destination">
                                                <i class="fa-solid fa-edit"></i> Edit
                                            </a>
                                            <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars(ucwords($row['destination_name']), ENT_QUOTES); ?>')" 
                                                    class="btn-action btn-delete"
                                                    title="Delete destination">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-destinations">
                                    <i class="fa-solid fa-inbox" style="font-size: 3rem; color: #ccc; display: block; margin-bottom: 1rem;"></i>
                                    No destinations found. Create your first destination above!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
        </div>
        <?php endif; ?>
    </div>
    </section>
    <img class="section-bot" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">
</main>

<!-- Parent Destination Modal -->
<div id="parentModal" class="modal">
    <div class="modal-content">
        <h2><i class="fa-solid fa-plus-circle"></i> Create Parent Destination</h2>
        <p>Create a parent destination (country) first, then you can add cities/landmarks under it.</p>
        
        <form id="parentForm" method="POST" action="">
            <input type="hidden" name="create_parent" value="1">
            
            <div class="form-group">
                <label for="parent_dest_name">Country/Parent Name <span class="red">*</span></label>
                <input type="text" id="parent_dest_name" name="parent_dest_name" required placeholder="e.g., Thailand, Japan">
            </div>
            
            <div class="form-group">
                <label for="parent_tagline">Tagline <span class="red">*</span></label>
                <input type="text" id="parent_tagline" name="parent_tagline" required placeholder="A catchy description">
            </div>
            
            <div class="form-group">
                <label for="parent_intro_image">Introduction Image URL <span class="red">*</span></label>
                <input type="url" id="parent_intro_image" name="parent_intro_image" required placeholder="https://example.com/image.jpg">
            </div>
            
            <div class="form-group">
                <label for="parent_introduction">Introduction Text <span class="red">*</span></label>
                <textarea id="parent_introduction" name="parent_introduction" required placeholder="Brief introduction about this destination..."></textarea>
            </div>
            
            <div class="modal-buttons">
                <button type="button" class="modal-btn btn-cancel" onclick="closeParentModal()">Cancel</button>
                <button type="submit" class="modal-btn btn-reply"><i class="fa-solid fa-check"></i> Create Parent</button>
            </div>
        </form>
    </div>
</div>

<footer>
    <div class="footer-content">
        <img class="logo" src="images/triptripLOGO.png" alt="triptrip logo">
        <div class="footer-links">
            <a href="about.php" title="About">About</a>
            <a href="contact.php" title="Contact">Contact</a>
            <a href="privacypolicy.php" title="Privacy Policy">Privacy Policy</a>
        </div>
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
    <div class="footer-bottom">
        <p>© <span id="year"></span> triptrip. All Rights Reserved.</p>
    </div>
</footer>
    
<script>
    document.getElementById("year").textContent = new Date().getFullYear();

    document.getElementById('search-form').addEventListener('submit', function(e) {
        const query = document.getElementById('search-input').value.trim();
        if (query === '') {
            e.preventDefault();
            alert('Please enter a search term.');
        }
    });

    let spotCount = 1;
    let foodCount = 1;
    let traditionCount = 1;
    let eventCount = 1;
    let tipCount = 1;
    let galleryCount = 1;
    
    function openParentModal() {
        document.getElementById('parentModal').style.display = 'block';
    }
    
    function closeParentModal() {
        document.getElementById('parentModal').style.display = 'none';
    }
    
    window.onclick = function(event) {
        const modal = document.getElementById('parentModal');
        if (event.target == modal) {
            closeParentModal();
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const createParentLink = document.getElementById('create-parent-link');
        if (createParentLink) {
            createParentLink.addEventListener('click', function(e) {
                e.preventDefault();
                openParentModal();
            });
        }
        
        // Handle Trip Classification section enable/disable
        const parentSelect = document.getElementById('parent_id');
        const tripSection = document.getElementById('trip-classification-section');
        const tripInputs = tripSection.querySelectorAll('input, select');
        
        function toggleTripClassification() {
            if (parentSelect.value === '') {
                // Country selected - disable trip classification
                tripSection.classList.add('disabled');
                tripInputs.forEach(input => {
                    input.disabled = true;
                    input.removeAttribute('required');
                });
            } else {
                // City / Landmark selected - enable trip classification
                tripSection.classList.remove('disabled');
                tripInputs.forEach(input => {
                    input.disabled = false;
                });
            }
        }
        
        // Initial check
        toggleTripClassification();
        
        // Listen for changes
        parentSelect.addEventListener('change', toggleTripClassification);
        
        // Initialize edit mode data
        <?php if ($editMode): ?>
            // Load existing spots
            <?php if (!empty($editData['spots'])): ?>
                const spotsContainer = document.getElementById('spots-container');
                spotsContainer.innerHTML = '';
                spotCount = <?php echo count($editData['spots']); ?>;
                <?php foreach ($editData['spots'] as $index => $spot): ?>
                    addSpotWithData(<?php echo $index + 1; ?>, <?php echo json_encode($spot); ?>);
                <?php endforeach; ?>
            <?php endif; ?>
            
            // Load existing foods
            <?php if (!empty($editData['foods'])): ?>
                const foodsContainer = document.getElementById('foods-container');
                foodsContainer.innerHTML = '';
                foodCount = <?php echo count($editData['foods']); ?>;
                <?php foreach ($editData['foods'] as $index => $food): ?>
                    addFoodWithData(<?php echo $index + 1; ?>, <?php echo json_encode($food); ?>);
                <?php endforeach; ?>
            <?php endif; ?>
            
            // Load existing traditions
            <?php if (!empty($editData['traditions'])): ?>
                const traditionsContainer = document.getElementById('traditions-container');
                traditionsContainer.innerHTML = '';
                traditionCount = <?php echo count($editData['traditions']); ?>;
                <?php foreach ($editData['traditions'] as $index => $tradition): ?>
                    addTraditionWithData(<?php echo $index + 1; ?>, <?php echo json_encode($tradition); ?>);
                <?php endforeach; ?>
            <?php endif; ?>
            
            // Load existing events
            <?php if (!empty($editData['events'])): ?>
                const eventsContainer = document.getElementById('events-container');
                eventsContainer.innerHTML = '';
                eventCount = <?php echo count($editData['events']); ?>;
                <?php foreach ($editData['events'] as $index => $event): ?>
                    addEventWithData(<?php echo $index + 1; ?>, <?php echo json_encode($event); ?>);
                <?php endforeach; ?>
            <?php endif; ?>
            
            // Load existing tips
            <?php if (!empty($editData['tips'])): ?>
                const tipsContainer = document.getElementById('tips-container');
                tipsContainer.innerHTML = '';
                tipCount = <?php echo count($editData['tips']); ?>;
                <?php foreach ($editData['tips'] as $index => $tip): ?>
                    addTipWithData(<?php echo $index + 1; ?>, <?php echo json_encode($tip); ?>);
                <?php endforeach; ?>
            <?php endif; ?>
            
            // Load existing gallery
            <?php if (!empty($editData['gallery'])): ?>
                const galleryContainer = document.getElementById('gallery-container');
                galleryContainer.innerHTML = '';
                galleryCount = <?php echo count($editData['gallery']); ?>;
                <?php foreach ($editData['gallery'] as $index => $image): ?>
                    addGalleryImageWithData(<?php echo $index + 1; ?>, <?php echo json_encode($image); ?>);
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    });
    
    // Confirm delete function
    function confirmDelete(id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?\n\nThis will permanently delete:\n• The destination\n• All tourist spots\n• All local foods\n• All traditions\n• All events\n• All travel tips\n• All gallery images\n• Trip listing (if exists)\n\nThis action cannot be undone!`)) {
            window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?delete=' + id;
        }
    }
    
    function addSpot() {
        spotCount++;
        const container = document.getElementById('spots-container');
        const newSpot = document.createElement('div');
        newSpot.className = 'repeater-item';
        newSpot.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Spot #${spotCount}</h4>
            <div class="form-group">
                <label>Spot Name</label>
                <input type="text" name="spot_name[]">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="spot_description[]"></textarea>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="spot_image[]" placeholder="https://example.com/image.jpg">
            </div>
            <div class="form-group">
                <label>Entrance Fee (Adult)</label>
                <input type="text" name="spot_fee_adult[]">
            </div>
            <div class="form-group">
                <label>Entrance Fee (Child)</label>
                <input type="text" name="spot_fee_child[]">
            </div>
            <div class="form-group">
                <label>Schedule</label>
                <textarea name="spot_schedule[]"></textarea>
            </div>
            <div class="form-group">
                <label>How to Get There</label>
                <textarea name="spot_how_to_get[]"></textarea>
            </div>
            <div class="form-group">
                <label>Best Time to Visit</label>
                <textarea name="spot_best_time[]"></textarea>
            </div>
        `;
        container.appendChild(newSpot);
    }
    
    function addFood() {
        foodCount++;
        const container = document.getElementById('foods-container');
        const newFood = document.createElement('div');
        newFood.className = 'repeater-item';
        newFood.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Food #${foodCount}</h4>
            <div class="form-group">
                <label>Food Name</label>
                <input type="text" name="food_name[]">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="food_description[]"></textarea>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="food_image[]" placeholder="https://example.com/image.jpg">
            </div>
        `;
        container.appendChild(newFood);
    }
    
    function addTradition() {
        traditionCount++;
        const container = document.getElementById('traditions-container');
        const newTradition = document.createElement('div');
        newTradition.className = 'repeater-item';
        newTradition.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Tradition #${traditionCount}</h4>
            <div class="form-group">
                <label>Tradition Title</label>
                <input type="text" name="tradition_title[]">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="tradition_description[]"></textarea>
            </div>
        `;
        container.appendChild(newTradition);
    }
    
    function addEvent() {
        eventCount++;
        const container = document.getElementById('events-container');
        const newEvent = document.createElement('div');
        newEvent.className = 'repeater-item';
        newEvent.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Event #${eventCount}</h4>
            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="event_name[]">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="event_description[]"></textarea>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="event_image[]" placeholder="https://example.com/image.jpg">
            </div>
        `;
        container.appendChild(newEvent);
    }
    
    function addTip() {
        tipCount++;
        const container = document.getElementById('tips-container');
        const newTip = document.createElement('div');
        newTip.className = 'repeater-item';
        newTip.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Tip #${tipCount}</h4>
            <div class="form-group">
                <label>Tip Title</label>
                <input type="text" name="tip_title[]">
            </div>
            <div class="form-group">
                <label>Tip Description</label>
                <textarea name="tip_description[]"></textarea>
            </div>
        `;
        container.appendChild(newTip);
    }
    
    function addGalleryImage() {
        galleryCount++;
        const container = document.getElementById('gallery-container');
        const newImage = document.createElement('div');
        newImage.className = 'repeater-item';
        newImage.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Image #${galleryCount}</h4>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="gallery_image[]" placeholder="https://example.com/image.jpg">
            </div>
            <div class="form-group">
                <label>Alt Text / Caption</label>
                <input type="text" name="gallery_alt[]">
            </div>
        `;
        container.appendChild(newImage);
    }
    
    // Helper functions for edit mode
    function addSpotWithData(num, data) {
        const container = document.getElementById('spots-container');
        const newSpot = document.createElement('div');
        newSpot.className = 'repeater-item';
        newSpot.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Spot #${num}</h4>
            <div class="form-group">
                <label>Spot Name</label>
                <input type="text" name="spot_name[]" value="${escapeHtml(data.spot_name || '')}">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="spot_description[]">${escapeHtml(data.description || '')}</textarea>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="spot_image[]" value="${escapeHtml(data.image_url || '')}">
            </div>
            <div class="form-group">
                <label>Entrance Fee (Adult)</label>
                <input type="text" name="spot_fee_adult[]" value="${escapeHtml(data.entrance_fee_adult || '')}">
            </div>
            <div class="form-group">
                <label>Entrance Fee (Child)</label>
                <input type="text" name="spot_fee_child[]" value="${escapeHtml(data.entrance_fee_child || '')}">
            </div>
            <div class="form-group">
                <label>Schedule</label>
                <textarea name="spot_schedule[]">${escapeHtml(data.schedule || '')}</textarea>
            </div>
            <div class="form-group">
                <label>How to Get There</label>
                <textarea name="spot_how_to_get[]">${escapeHtml(data.how_to_get_there || '')}</textarea>
            </div>
            <div class="form-group">
                <label>Best Time to Visit</label>
                <textarea name="spot_best_time[]">${escapeHtml(data.best_time || '')}</textarea>
            </div>
        `;
        container.appendChild(newSpot);
    }
    
    function addFoodWithData(num, data) {
        const container = document.getElementById('foods-container');
        const newFood = document.createElement('div');
        newFood.className = 'repeater-item';
        newFood.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Food #${num}</h4>
            <div class="form-group">
                <label>Food Name</label>
                <input type="text" name="food_name[]" value="${escapeHtml(data.food_name || '')}">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="food_description[]">${escapeHtml(data.description || '')}</textarea>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="food_image[]" value="${escapeHtml(data.image_url || '')}">
            </div>
        `;
        container.appendChild(newFood);
    }
    
    function addTraditionWithData(num, data) {
        const container = document.getElementById('traditions-container');
        const newTradition = document.createElement('div');
        newTradition.className = 'repeater-item';
        newTradition.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Tradition #${num}</h4>
            <div class="form-group">
                <label>Tradition Title</label>
                <input type="text" name="tradition_title[]" value="${escapeHtml(data.tradition_title || '')}">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="tradition_description[]">${escapeHtml(data.tradition_description || '')}</textarea>
            </div>
        `;
        container.appendChild(newTradition);
    }
    
    function addEventWithData(num, data) {
        const container = document.getElementById('events-container');
        const newEvent = document.createElement('div');
        newEvent.className = 'repeater-item';
        newEvent.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Event #${num}</h4>
            <div class="form-group">
                <label>Event Name</label>
                <input type="text" name="event_name[]" value="${escapeHtml(data.event_name || '')}">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="event_description[]">${escapeHtml(data.event_description || '')}</textarea>
            </div>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="event_image[]" value="${escapeHtml(data.image_url || '')}">
            </div>
        `;
        container.appendChild(newEvent);
    }
    
    function addTipWithData(num, data) {
        const container = document.getElementById('tips-container');
        const newTip = document.createElement('div');
        newTip.className = 'repeater-item';
        newTip.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Tip #${num}</h4>
            <div class="form-group">
                <label>Tip Title</label>
                <input type="text" name="tip_title[]" value="${escapeHtml(data.tip_title || '')}">
            </div>
            <div class="form-group">
                <label>Tip Description</label>
                <textarea name="tip_description[]">${escapeHtml(data.tip_description || '')}</textarea>
            </div>
        `;
        container.appendChild(newTip);
    }
    
    function addGalleryImageWithData(num, data) {
        const container = document.getElementById('gallery-container');
        const newImage = document.createElement('div');
        newImage.className = 'repeater-item';
        newImage.innerHTML = `
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()"><i class="fa-solid fa-times"></i></button>
            <h4>Image #${num}</h4>
            <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="gallery_image[]" value="${escapeHtml(data.image_url || '')}">
            </div>
            <div class="form-group">
                <label>Alt Text / Caption</label>
                <input type="text" name="gallery_alt[]" value="${escapeHtml(data.alt_text || '')}">
            </div>
        `;
        container.appendChild(newImage);
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }
</script>
</body>
</html>