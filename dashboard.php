<?php
// dashboard.php
include 'config.php';
include 'auth.php';

// Check if user is admin
$userType = $_SESSION['user_type'] ?? 'guest';
if ($userType !== 'admin') {
    header("Location: index.php");
    exit;
}

// Get dashboard type from URL
$dashType = $_GET['dash'] ?? 'contact';

// Handle delete message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message_id'])) {
    $messageId = intval($_POST['delete_message_id']);
    $deleteSQL = "DELETE FROM contact_messages WHERE id = ?";
    $stmt = $conn->prepare($deleteSQL);
    $stmt->bind_param("i", $messageId);
    
    if ($stmt->execute()) {
        $_SESSION['dashMessage'] = "Message deleted successfully.";
        $_SESSION['dashMessageType'] = "success";
    } else {
        $_SESSION['dashMessage'] = "Failed to delete message.";
        $_SESSION['dashMessageType'] = "error";
    }
    $stmt->close();
    header("Location: dashboard.php?dash=contact");
    exit;
}

// Handle delete review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review_id'])) {
    $reviewId = intval($_POST['delete_review_id']);
    $deleteSQL = "DELETE FROM trip_ratings WHERE id = ?";
    $stmt = $conn->prepare($deleteSQL);
    $stmt->bind_param("i", $reviewId);
    
    if ($stmt->execute()) {
        $_SESSION['dashMessage'] = "Review deleted successfully.";
        $_SESSION['dashMessageType'] = "success";
    } else {
        $_SESSION['dashMessage'] = "Failed to delete review.";
        $_SESSION['dashMessageType'] = "error";
    }
    $stmt->close();
    header("Location: dashboard.php?dash=userrating");
    exit;
}

// Handle approve review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_review_id'])) {
    $reviewId = intval($_POST['approve_review_id']);
    $updateSQL = "UPDATE trip_ratings SET status = 'approved' WHERE id = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("i", $reviewId);
    
    if ($stmt->execute()) {
        $_SESSION['dashMessage'] = "Review approved successfully.";
        $_SESSION['dashMessageType'] = "success";
    } else {
        $_SESSION['dashMessage'] = "Failed to approve review.";
        $_SESSION['dashMessageType'] = "error";
    }
    $stmt->close();
    header("Location: dashboard.php?dash=userrating");
    exit;
}

// Handle reject review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_review_id'])) {
    $reviewId = intval($_POST['reject_review_id']);
    $updateSQL = "UPDATE trip_ratings SET status = 'rejected' WHERE id = ?";
    $stmt = $conn->prepare($updateSQL);
    $stmt->bind_param("i", $reviewId);
    
    if ($stmt->execute()) {
        $_SESSION['dashMessage'] = "Review rejected successfully.";
        $_SESSION['dashMessageType'] = "success";
    } else {
        $_SESSION['dashMessage'] = "Failed to reject review.";
        $_SESSION['dashMessageType'] = "error";
    }
    $stmt->close();
    header("Location: dashboard.php?dash=userrating");
    exit;
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_email'], $_POST['reply_message'])) {
    $to = $_POST['reply_email'];
    $message = $_POST['reply_message'];
    $subject = "Reply from TripTrip Admin";
    $headers = "From: admin@triptrip.com\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        $_SESSION['dashMessage'] = "Reply sent successfully to $to.";
        $_SESSION['dashMessageType'] = "success";
    } else {
        $_SESSION['dashMessage'] = "Failed to send reply.";
        $_SESSION['dashMessageType'] = "error";
    }
    header("Location: dashboard.php?dash=" . $dashType);
    exit;
}

// Check for messages in session (after redirect)
$dashMessage = "";
$dashMessageType = "";
if (isset($_SESSION['dashMessage'])) {
    $dashMessage = $_SESSION['dashMessage'];
    $dashMessageType = $_SESSION['dashMessageType'];
    unset($_SESSION['dashMessage'], $_SESSION['dashMessageType']);
}

// Pagination settings
$itemsPerPage = 5;
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Contact messages query with pagination
$contactCountSQL = "SELECT COUNT(*) as total FROM contact_messages";
$contactCountResult = $conn->query($contactCountSQL);
$totalContactMessages = $contactCountResult->fetch_assoc()['total'];
$totalContactPages = ceil($totalContactMessages / $itemsPerPage);

$contactSQL = "SELECT * FROM contact_messages ORDER BY submitted_at DESC LIMIT $itemsPerPage OFFSET $offset";
$contactResult = $conn->query($contactSQL);

// Trip ratings/reviews query with pagination
$ratingCountSQL = "SELECT COUNT(*) as total FROM trip_ratings";
$ratingCountResult = $conn->query($ratingCountSQL);
$totalRatingMessages = $ratingCountResult->fetch_assoc()['total'];
$totalRatingPages = ceil($totalRatingMessages / $itemsPerPage);

$ratingSQL = "SELECT * FROM trip_ratings ORDER BY created_at DESC LIMIT $itemsPerPage OFFSET $offset";
$ratingResult = $conn->query($ratingSQL);

// Get Search Query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin dashboard | triptrip</title>
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
                <p class="heading">dashboard</p>
                <p class="subtext">manage contact messages and rating submissions here.</p>
            </div>
        </section>

        
        <section class="contact-section">
            <img class="section-top" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">

            <div class="contact-content">
                <div class="admin-messages-container">
                    <?php if (!empty($dashMessage)): ?>
                        <div class="<?php echo $dashMessageType; ?>-message">
                            <?php echo htmlspecialchars($dashMessage); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Dashboard Tabs -->
                    <div class="dashboard-tabs">
                        <a href="dashboard.php?dash=contact" class="dashboard-tab <?php echo $dashType === 'contact' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-envelope"></i> Contact Messages
                        </a>
                        <a href="dashboard.php?dash=userrating" class="dashboard-tab <?php echo $dashType === 'userrating' ? 'active' : ''; ?>">
                            <i class="fa-solid fa-star"></i> User Ratings
                        </a>
                    </div>
                    
                    <!-- Contact Messages Section -->
                    <div id="contactSection" class="dashboard-section <?php echo $dashType === 'contact' ? 'active' : ''; ?>">
                        <p class="heading">[ <?php echo $totalContactMessages; ?> ] contact messages</p>
                        <?php if ($contactResult && $contactResult->num_rows > 0): ?>
                            <div class="messages-table-wrapper">
                                <table class="messages-table">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $contactResult->fetch_assoc()): ?>
                                        <tr>
                                            <td data-label="Subject"><strong><?php echo htmlspecialchars($row['subject']); ?></strong></td>
                                            <td data-label="Name"><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td data-label="Date"><?php echo date("M d, Y H:i", strtotime($row['submitted_at'])); ?></td>
                                            <td data-label="Action">
                                                <button class="mview-btn" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                                    data-email="<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>"
                                                    data-subject="<?php echo htmlspecialchars($row['subject'], ENT_QUOTES); ?>"
                                                    data-message="<?php echo htmlspecialchars($row['message'], ENT_QUOTES); ?>"
                                                    data-submitted="<?php echo htmlspecialchars($row['submitted_at'], ENT_QUOTES); ?>"
                                                    onclick="viewMessageFromButton(this)">
                                                    <i class="fa-solid fa-eye"></i> View
                                                </button>
                                                <button class="delete-btn" onclick="showDeleteModal(<?php echo $row['id']; ?>, 'contact')">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalContactPages > 1): ?>
                            <div class="pagination">
                                <?php if ($currentPage > 1): ?>
                                    <a href="dashboard.php?dash=contact&page=<?php echo $currentPage - 1; ?>">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalContactPages; $i++): ?>
                                    <?php if ($i == $currentPage): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="dashboard.php?dash=contact&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalContactPages): ?>
                                    <a href="dashboard.php?dash=contact&page=<?php echo $currentPage + 1; ?>">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="no-messages">No messages found.</p>
                        <?php endif; ?>
                    </div>

                    <!-- User Ratings Section -->
                    <div id="ratingSection" class="dashboard-section <?php echo $dashType === 'userrating' ? 'active' : ''; ?>">
                        <p class="heading">[ <?php echo $totalRatingMessages; ?> ] user ratings</p>
                        <?php if ($ratingResult && $ratingResult->num_rows > 0): ?>
                            <div class="messages-table-wrapper">
                                <table class="messages-table">
                                    <thead>
                                        <tr>
                                            <th>Trip ID</th>
                                            <th>Reviewer</th>
                                            <th>Rating</th>
                                            <th>Review Title</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $ratingResult->fetch_assoc()): ?>
                                        <tr>
                                            <td data-label="Trip ID"><?php echo htmlspecialchars($row['trip_id']); ?></td>
                                            <td data-label="Reviewer"><?php echo htmlspecialchars($row['reviewer_name']); ?></td>
                                            <td data-label="Rating"><?php echo htmlspecialchars($row['rating']); ?> / 10</td>
                                            <td data-label="Review Title"><?php echo htmlspecialchars($row['review_title']); ?></td>
                                            <td data-label="Status">
                                                <?php 
                                                $status = htmlspecialchars($row['status']);
                                                $statusClass = 'status-' . $status;
                                                echo "<span class='status-badge $statusClass'>" . ucfirst($status) . "</span>";
                                                ?>
                                            </td>
                                            <td data-label="Created At"><?php echo date("M d, Y H:i", strtotime($row['created_at'])); ?></td>
                                            <td data-label="Actions">
                                                <button class="mview-btn" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-trip-id="<?php echo htmlspecialchars($row['trip_id'], ENT_QUOTES); ?>"
                                                    data-reviewer="<?php echo htmlspecialchars($row['reviewer_name'], ENT_QUOTES); ?>"
                                                    data-rating="<?php echo htmlspecialchars($row['rating'], ENT_QUOTES); ?>"
                                                    data-title="<?php echo htmlspecialchars($row['review_title'], ENT_QUOTES); ?>"
                                                    data-review="<?php echo htmlspecialchars($row['review_text'] ?? '', ENT_QUOTES); ?>"
                                                    data-status="<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>"
                                                    data-created="<?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?>"
                                                    onclick="viewReviewFromButton(this)">
                                                    <i class="fa-solid fa-eye"></i> View
                                                </button>
                                                <button class="delete-btn" onclick="showDeleteModal(<?php echo $row['id']; ?>, 'review')">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalRatingPages > 1): ?>
                            <div class="pagination">
                                <?php if ($currentPage > 1): ?>
                                    <a href="dashboard.php?dash=userrating&page=<?php echo $currentPage - 1; ?>">
                                        <i class="fa-solid fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalRatingPages; $i++): ?>
                                    <?php if ($i == $currentPage): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="dashboard.php?dash=userrating&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalRatingPages): ?>
                                    <a href="dashboard.php?dash=userrating&page=<?php echo $currentPage + 1; ?>">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="no-messages">No reviews found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <img class="section-bot" src="images/sectiontop/foldertop.webp" alt="" aria-hidden="true">
        </section>
    </main>

    <!-- Message Detail Modal -->
    <div id="messageDetailModal" class="modal">
        <div class="modal-content detail-modal">
            <div class="message-detail-body" id="messageDetailContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="message-actions">
                <button class="action-btn btn-reply" onclick="openReplyModalFromDetail()">
                    <i class="fa-solid fa-reply"></i> Reply
                </button>
                <button class="action-btn btn-delete-action" onclick="showDeleteModalFromDetail()">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
                <button class="action-btn btn-close-detail" onclick="closeMessageDetailModal()">
                    <i class="fa-solid fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Review Detail Modal -->
    <div id="reviewDetailModal" class="modal">
        <div class="modal-content detail-modal">
            <div class="message-detail-body" id="reviewDetailContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="message-actions" id="reviewActions">
                <!-- Actions will be populated by JavaScript based on status -->
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div id="replyModal" class="modal">
        <div class="modal-content reply-modal">
            <div class="message-detail-header">
                <h2>Reply to Message</h2>
            </div>
            <form method="POST" id="replyForm">
                <input type="hidden" name="reply_email" id="reply_email">
                <input type="hidden" name="reply_subject" id="reply_subject">
                <div class="reply-info">
                    <p><strong>To:</strong> <span id="display_email"></span></p>
                    <p><strong>Re:</strong> <span id="display_subject"></span></p>
                </div>
                <textarea name="reply_message" id="reply_message" placeholder="Type your reply here..." required></textarea>
                <div class="message-actions">
                    <button type="submit" class="action-btn btn-reply">
                        <i class="fa-solid fa-reply"></i> Send Reply
                    </button>
                    <button type="button" class="action-btn btn-close-detail" onclick="closeReplyModal()">
                        <i class="fa-solid fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content delete-modal">
            <div class="modal-icon">
                <i class="fa-solid fa-exclamation-triangle"></i>
            </div>
            <h2>Confirm Delete</h2>
            <p id="deleteConfirmText">Are you sure you want to delete this item? This action cannot be undone.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="delete_message_id" id="delete_message_id">
                <input type="hidden" name="delete_review_id" id="delete_review_id">
                <div class="modal-buttons">
                    <button type="submit" class="modal-btn btn-delete">
                        <i class="fa-solid fa-trash"></i> DELETE
                    </button>
                    <button type="button" onclick="closeDeleteModal()" class="modal-btn btn-cancel">
                        <i class="fa-solid fa-times"></i> CANCEL
                    </button>
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

    // dashboard.js - Admin message management
    
    let currentMessageData = null;
    let currentDeleteId = null;
    let currentDeleteType = null;

    // View Message Detail from Button with data attributes
    function viewMessageFromButton(button) {
        const messageData = {
            id: button.getAttribute('data-id'),
            name: button.getAttribute('data-name'),
            email: button.getAttribute('data-email'),
            subject: button.getAttribute('data-subject'),
            message: button.getAttribute('data-message'),
            submitted_at: button.getAttribute('data-submitted')
        };
        
        viewMessage(messageData);
    }

    // View Message Detail
    function viewMessage(messageData) {
        currentMessageData = messageData;
        currentDeleteId = messageData.id;
        currentDeleteType = 'contact';
        
        const date = new Date(messageData.submitted_at).toLocaleString();
        
        const detailHTML = `
            <div class="message-detail-header">
                <h2>${escapeHtml(messageData.subject)}</h2>
                <span class="message-date">${date}</span>
            </div>
            
            <div class="message-info">
                <div class="info-item">
                    <i class="fa-solid fa-user"></i>
                    <strong>FROM :</strong> ${escapeHtml(messageData.name)}
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-envelope"></i>
                    <strong>EMAIL :</strong> ${escapeHtml(messageData.email)}
                </div>
            </div>
            
            <div class="message-body">
                <h3>Message :</h3>
                <div class="message-full-text">
                        ${escapeHtml(messageData.message)}
                </div>
            </div>
        `;
        
        document.getElementById('messageDetailContent').innerHTML = detailHTML;
        document.getElementById('messageDetailModal').style.display = 'block';
    }

    // View Review Detail from Button with data attributes
    function viewReviewFromButton(button) {
        const reviewData = {
            id: button.getAttribute('data-id'),
            trip_id: button.getAttribute('data-trip-id'),
            reviewer: button.getAttribute('data-reviewer'),
            rating: button.getAttribute('data-rating'),
            title: button.getAttribute('data-title'),
            review: button.getAttribute('data-review'),
            status: button.getAttribute('data-status'),
            created_at: button.getAttribute('data-created')
        };
        
        viewReview(reviewData);
    }

    // View Review Detail
    function viewReview(reviewData) {
        currentMessageData = reviewData;
        currentDeleteId = reviewData.id;
        currentDeleteType = 'review';
        
        const date = new Date(reviewData.created_at).toLocaleString();
        
        // Create star rating display
        const stars = '★'.repeat(parseInt(reviewData.rating)) + '☆'.repeat(10 - parseInt(reviewData.rating));
        
        // Status badge
        const statusClass = 'status-' + reviewData.status;
        const statusBadge = `<span class="status-badge ${statusClass}">${escapeHtml(reviewData.status).charAt(0).toUpperCase() + escapeHtml(reviewData.status).slice(1)}</span>`;
        
        const detailHTML = `
            <div class="message-detail-header">
                <h2>${escapeHtml(reviewData.title)}</h2>
                <span class="message-date">${date}</span>
            </div>
            
            <div class="message-info">
                <div class="info-item">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <strong>TRIP ID :</strong> ${escapeHtml(reviewData.trip_id)}
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-user"></i>
                    <strong>REVIEWER :</strong> ${escapeHtml(reviewData.reviewer)}
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-star"></i>
                    <strong>RATING :</strong> ${stars} (${escapeHtml(reviewData.rating)} / 10)
                </div>
                <div class="info-item">
                    <i class="fa-solid fa-circle-info"></i>
                    <strong>STATUS :</strong> ${statusBadge}
                </div>
            </div>
            
            ${reviewData.review ? `
            <div class="message-body">
                <h3>Review :</h3>
                <div class="message-full-text">
                    ${escapeHtml(reviewData.review)}
                </div>
            </div>
            ` : ''}
        `;
        
        document.getElementById('reviewDetailContent').innerHTML = detailHTML;
        
        // Create action buttons based on status
        let actionsHTML = '';
        
        if (reviewData.status === 'pending') {
            actionsHTML = `
                <button class="action-btn btn-approve" onclick="approveReview(${reviewData.id})">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
                <button class="action-btn btn-reject" onclick="rejectReview(${reviewData.id})">
                    <i class="fa-solid fa-times"></i> Reject
                </button>
            `;
        } else if (reviewData.status === 'approved') {
            actionsHTML = `
                <button class="action-btn btn-reject" onclick="rejectReview(${reviewData.id})">
                    <i class="fa-solid fa-times"></i> Reject
                </button>
            `;
        } else if (reviewData.status === 'rejected') {
            actionsHTML = `
                <button class="action-btn btn-approve" onclick="approveReview(${reviewData.id})">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
            `;
        }
        
        actionsHTML += `
            <button class="action-btn btn-delete-action" onclick="showDeleteModalFromDetail()">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
            <button class="action-btn btn-close-detail" onclick="closeReviewDetailModal()">
                <i class="fa-solid fa-times"></i> Close
            </button>
        `;
        
        document.getElementById('reviewActions').innerHTML = actionsHTML;
        document.getElementById('reviewDetailModal').style.display = 'block';
    }

    // Close Message Detail Modal
    function closeMessageDetailModal() {
        document.getElementById('messageDetailModal').style.display = 'none';
        currentMessageData = null;
    }

    // Close Review Detail Modal
    function closeReviewDetailModal() {
        document.getElementById('reviewDetailModal').style.display = 'none';
        currentMessageData = null;
    }

    // Open Reply Modal from Detail View
    function openReplyModalFromDetail() {
        if (!currentMessageData) return;
        
        showReplyModal(currentMessageData.email, currentMessageData.subject);
    }

    // Show Reply Modal
    function showReplyModal(email, subject) {
        document.getElementById('reply_email').value = email;
        document.getElementById('reply_subject').value = subject;
        document.getElementById('display_email').textContent = email;
        document.getElementById('display_subject').textContent = subject;
        document.getElementById('reply_message').value = '';
        document.getElementById('replyModal').style.display = 'block';
    }

    // Close Reply Modal
    function closeReplyModal() {
        document.getElementById('replyModal').style.display = 'none';
        document.getElementById('replyForm').reset();
    }

    // Show Delete Modal
    function showDeleteModal(id, type) {
        currentDeleteId = id;
        currentDeleteType = type;
        
        const text = type === 'contact' 
            ? 'Are you sure you want to delete this message? This action cannot be undone.'
            : 'Are you sure you want to delete this review? This action cannot be undone.';
        
        document.getElementById('deleteConfirmText').textContent = text;
        
        // Set the appropriate hidden field
        if (type === 'contact') {
            document.getElementById('delete_message_id').value = id;
            document.getElementById('delete_review_id').value = '';
        } else {
            document.getElementById('delete_review_id').value = id;
            document.getElementById('delete_message_id').value = '';
        }
        
        document.getElementById('deleteConfirmModal').style.display = 'block';
    }

    // Show Delete Modal from Detail View
    function showDeleteModalFromDetail() {
        if (!currentDeleteId || !currentDeleteType) return;
        
        // Close the detail modal first
        if (currentDeleteType === 'contact') {
            closeMessageDetailModal();
        } else {
            closeReviewDetailModal();
        }
        
        showDeleteModal(currentDeleteId, currentDeleteType);
    }

    // Close Delete Modal
    function closeDeleteModal() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
        currentDeleteId = null;
        currentDeleteType = null;
    }

    // Approve Review
    function approveReview(reviewId) {
        if (!confirm('Are you sure you want to approve this review?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="approve_review_id" value="${reviewId}">`;
        document.body.appendChild(form);
        form.submit();
    }

    // Reject Review
    function rejectReview(reviewId) {
        if (!confirm('Are you sure you want to reject this review?')) return;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="reject_review_id" value="${reviewId}">`;
        document.body.appendChild(form);
        form.submit();
    }

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const detailModal = document.getElementById('messageDetailModal');
        const reviewModal = document.getElementById('reviewDetailModal');
        const replyModal = document.getElementById('replyModal');
        const deleteModal = document.getElementById('deleteConfirmModal');
        
        if (event.target === detailModal) {
            closeMessageDetailModal();
        }
        if (event.target === reviewModal) {
            closeReviewDetailModal();
        }
        if (event.target === replyModal) {
            closeReplyModal();
        }
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    };

    // Auto Scroll (DESKTOP ONLY)
    window.addEventListener('load', function () {
        const urlParams = new URLSearchParams(window.location.search);

        // Mobile Disabled
        if (window.innerWidth <= 768) return;

        if (urlParams.has('page')) {
            const section = document.querySelector('.dashboard-section.active');
            if (section) {
                section.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    });
</script>
</body>
</html>