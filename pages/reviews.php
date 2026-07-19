<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Initialize reviews file
$reviews_file = __DIR__ . '/../reviews.json';
if (!file_exists($reviews_file)) {
    file_put_contents($reviews_file, json_encode([]));
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $name = trim($_POST['name']) ?: 'Anonymous';
    $email = trim($_POST['email']) ?: '';
    $review = trim($_POST['review']);
    $rating = intval($_POST['rating'] ?? 5);
    
    if (!empty($review)) {
        $reviews = json_decode(file_get_contents($reviews_file), true);
        $reviews[] = [
            'id' => count($reviews) + 1,
            'name' => htmlspecialchars($name),
            'email' => htmlspecialchars($email),
            'review' => htmlspecialchars($review),
            'rating' => $rating,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'approved' // Auto-approved
        ];
        file_put_contents($reviews_file, json_encode($reviews));
        $_SESSION['success'] = ['type' => 'success', 'message' => 'Thank you for your review! It has been published.'];
        redirect('/pages/reviews.php');
    }
}

// Handle delete (admin only)
if (isset($_GET['delete']) && isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $id = intval($_GET['delete']);
    $reviews = json_decode(file_get_contents($reviews_file), true);
    $reviews = array_filter($reviews, function($r) use ($id) {
        return $r['id'] != $id;
    });
    file_put_contents($reviews_file, json_encode(array_values($reviews)));
    $_SESSION['success'] = ['type' => 'success', 'message' => 'Review deleted!'];
    redirect('/pages/reviews.php');
}

$reviews = json_decode(file_get_contents($reviews_file), true);
$approved_reviews = array_filter($reviews, function($r) { return $r['status'] == 'approved'; });
// Sort by newest first
usort($approved_reviews, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});
?>
<!DOCTYPE html>
<html>
<head><title>Reviews – <?php echo SITE_NAME; ?></title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    .review-section { background: #f8faff; padding: 40px 0; min-height: 80vh; }
    .review-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 16px; border: 1px solid #e9edf2; transition: all 0.3s ease; }
    .review-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .review-card .stars { color: #ffc107; font-size: 0.9rem; }
    .review-card .reviewer { font-weight: 600; color: #0d1a2b; }
    .review-card .date { font-size: 0.8rem; color: #6b7a93; }
    .review-card .delete-btn { opacity: 0.5; transition: opacity 0.2s; }
    .review-card .delete-btn:hover { opacity: 1; }
    .rating-input { font-size: 2rem; color: #dce3ec; cursor: pointer; transition: color 0.2s; }
    .rating-input.active { color: #ffc107; }
    .rating-input:hover { color: #ffc107; }
    .review-count-badge { background: #e8f4fd; padding: 2px 12px; border-radius: 30px; font-size: 0.85rem; color: #0d6efd; }
</style>
</head>
<body>
    <nav class="navbar navbar-expand-lg modern-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <li class="nav-item"><a href="/" class="nav-btn nav-btn-login"><i class="fas fa-home"></i> Home</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item"><a href="/pages/dashboard.php" class="nav-btn nav-btn-login"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="/pages/login.php" class="nav-btn nav-btn-login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li class="nav-item"><a href="/pages/register.php" class="nav-btn nav-btn-register"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="review-section">
        <div class="container" style="max-width:800px;">
            <div class="text-center mb-4">
                <h1>What Our Investors Say</h1>
                <p class="text-muted">Read reviews from our community of investors</p>
                <span class="review-count-badge"><i class="fas fa-star text-warning"></i> <?php echo count($approved_reviews); ?> Reviews</span>
            </div>
            
            <?php displayFlash('error'); displayFlash('success'); ?>
            
            <!-- Submit Review Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Share Your Experience</h5>
                    <p class="text-muted small">Your review will be published immediately.</p>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter your name (optional)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email (optional)">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Rating</label>
                                <div>
                                    <span class="rating-input" data-value="1" onclick="setRating(1)">★</span>
                                    <span class="rating-input" data-value="2" onclick="setRating(2)">★</span>
                                    <span class="rating-input" data-value="3" onclick="setRating(3)">★</span>
                                    <span class="rating-input" data-value="4" onclick="setRating(4)">★</span>
                                    <span class="rating-input" data-value="5" onclick="setRating(5)">★</span>
                                    <input type="hidden" name="rating" id="ratingInput" value="5">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Your Review</label>
                                <textarea name="review" class="form-control" rows="4" placeholder="Write your review here..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="submit_review" class="btn btn-success"><i class="fas fa-paper-plane"></i> Submit Review</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Display Reviews -->
            <?php if (empty($approved_reviews)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-comment-dots" style="font-size: 3rem; color: #dce3ec; margin-bottom: 15px;"></i>
                    <h5>No reviews yet</h5>
                    <p class="text-muted">Be the first to share your experience!</p>
                </div>
            <?php else: ?>
                <?php foreach($approved_reviews as $review): ?>
                    <div class="review-card" id="review-<?php echo $review['id']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="reviewer"><?php echo htmlspecialchars($review['name']); ?></span>
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div>
                                <span class="date"><?php echo date('M d, Y', strtotime($review['timestamp'])); ?></span>
                                <?php if (isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                                    <a href="?delete=<?php echo $review['id']; ?>" class="delete-btn text-danger ms-2" onclick="return confirm('Delete this review?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="mt-2" style="color:#3a4b5e;"><?php echo htmlspecialchars($review['review']); ?></p>
                        <?php if (isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <div class="mt-1">
                                <small class="text-muted">ID: <?php echo $review['id']; ?> | Email: <?php echo htmlspecialchars($review['email']); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer mt-auto">
        <div class="container-fluid">
            <span>&copy; 2026 <?php echo SITE_NAME; ?></span>
            <span class="float-end">📧 <?php echo ADMIN_EMAIL; ?></span>
        </div>
    </footer>

    <script>
    let selectedRating = 5;
    
    function setRating(value) {
        selectedRating = value;
        document.getElementById('ratingInput').value = value;
        document.querySelectorAll('.rating-input').forEach(function(el) {
            el.classList.remove('active');
            if (parseInt(el.dataset.value) <= value) {
                el.classList.add('active');
            }
        });
    }
    
    // Set initial rating
    setRating(5);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
