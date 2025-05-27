<?php
require "partials/header.php";
require "partials/dbconnect.php";

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch category info
$catname = '';
$catdesc = '';
if (isset($_GET['catid']) && is_numeric($_GET['catid'])) {
    $id = intval($_GET['catid']);
    $stmt = $conn->prepare("SELECT * FROM `categories` WHERE `category_id` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $catname = $row['category_name'];
        $catdesc = $row['category_description'];
    }
    $stmt->close();
}

// Handle new thread post
$showalert = false;
$method = $_SERVER['REQUEST_METHOD'];
if ($method == 'POST' && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
    $th_title = mysqli_real_escape_string($conn, $_POST['title']);
    $th_desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $thread_user_id = $_SESSION['sno'];
    // ✅ Correct way to get logged-in user ID

    $sql = "INSERT INTO `threads` (`thread_title`, `thread_desc`, `thread_cat_id`, `thread_user_id`, `timestamp`) 
            VALUES ('$th_title', '$th_desc', '$id', '$thread_user_id', current_timestamp())";

    $result = mysqli_query($conn, $sql);
    $showalert = true;

    if ($showalert) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your question has been posted.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

// Get random user image from Unsplash
$accessKey = "BVyFTh3RaF93Ogq--ffRbY3nDKdjD7iNI59RqhWgqVo";
$fallback = "https://via.placeholder.com/50x50?text=No+Img";
$userImageUrl = $fallback;
$url = "https://api.unsplash.com/photos/random?query=face,portrait,person&orientation=squarish&client_id={$accessKey}";
$response = @file_get_contents($url);
if ($response !== false) {
    $data = json_decode($response, true);
    if (!empty($data['urls']['thumb'])) {
        $userImageUrl = $data['urls']['thumb'];
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlackCode-hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .ques {
            min-height: 40px;
        }
    </style>
</head>

<body>

    <div class="container mt-5">
        <div class="p-5 mb-4 bg-light rounded-3">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold">Welcome to <?php echo ucfirst(htmlspecialchars($catname)); ?> Forums!</h1>
                <p class="col-md-8 fs-4"><?php echo ucfirst(htmlspecialchars($catdesc)); ?></p>
                <hr class="my-4">
                <p>This forum is for sharing knowledge with each other.</p>
                <button class="btn btn-success btn-lg" type="button">Learn more</button>
            </div>
        </div>
    </div>

    <!-- New Discussion Form -->
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
        <div class="container mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-chat-dots-fill me-2"></i> Start a New Discussion
                    </h5>
                </div>
                <div class="card-body bg-light">
                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                        <div class="mb-3">
                            <label for="threadTitle" class="form-label fw-semibold">Thread Title</label>
                            <input type="text" class="form-control" id="title" name="title"
                                placeholder="e.g. How do I connect MySQL with PHP?" required>
                            <div class="form-text text-muted">
                                Use a clear, specific title (max 80 characters).
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="threadDesc" class="form-label fw-semibold">Details</label>
                            <textarea class="form-control" id="desc" name="desc" rows="4" maxlength="1000"
                                placeholder="Describe your question in detail..." required></textarea>
                            <div class="form-text text-muted">
                                Provide all relevant information so others can help.
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-send-fill me-1"></i> Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center p-3 rounded-3" role="alert">
            <div>
                <strong>Login Required</strong><br>
                <small>You must be logged in to start a conversation or reply to a thread.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-dark ms-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </div>
    <?php endif; ?>

    <!-- Browse Questions -->
    <div class="container ques">
        <h1>Browse Questions</h1>

        <?php
        // Pagination variables
        $limit = 5;  // Threads per page
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;

        $offset = ($page - 1) * $limit;

        // Count total threads in this category for pagination
        $stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM threads WHERE thread_cat_id = ?");
        $stmt_count->bind_param("i", $id);
        $stmt_count->execute();
        $res_count = $stmt_count->get_result();
        $total_rows = 0;
        if ($row_count = $res_count->fetch_assoc()) {
            $total_rows = $row_count['total'];
        }
        $stmt_count->close();

        $total_pages = ceil($total_rows / $limit);

        // Fetch threads with limit and offset
        $stmt = $conn->prepare("
            SELECT t.thread_id, t.thread_title, t.thread_desc, t.thread_user_id, t.timestamp
            FROM threads t
            WHERE t.thread_cat_id = ?
            ORDER BY t.timestamp DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $id, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0): ?>
            <div class="alert alert-info mt-4">
                No threads found in this category. Be the first to ask a question!
            </div>
            <?php else:
            while ($row = $result->fetch_assoc()):
            ?>
                <div class="d-flex align-items-start my-3">
                    <img src="<?php echo htmlspecialchars($userImageUrl); ?>" alt="User"
                        class="rounded-circle me-3" style="width:50px; height:50px; object-fit:cover;">

                    <div>
                        <h5 class="mt-0">
                            <small class="text-muted">
                                Posted by <strong class="text-primary">
                                    <?php
                                    $thread_user_id = $row['thread_user_id'];
                                    $sql = "SELECT user_email FROM `users` WHERE `sno` = ?";
                                    $stmt2 = $conn->prepare($sql);
                                    $stmt2->bind_param("i", $thread_user_id);
                                    $stmt2->execute();
                                    $res2 = $stmt2->get_result();
                                    if ($userRow = $res2->fetch_assoc()) {
                                        echo htmlspecialchars($userRow['user_email']);
                                    } else {
                                        echo "Unknown User";
                                    }
                                    $stmt2->close();
                                    ?>
                                </strong> —
                                <?php
                                $dt = new DateTime($row['timestamp']);
                                echo $dt->format('M j, Y \a\t g:i A');
                                ?>
                            </small><br>
                            <a href="thread.php?threadid=<?php echo htmlspecialchars($row['thread_id']); ?>">
                                <?php echo htmlspecialchars(ucfirst($row['thread_title'])); ?>
                            </a>
                        </h5>
                        <p><?php echo htmlspecialchars(ucfirst($row['thread_desc'])); ?></p>
                    </div>
                </div>
        <?php
            endwhile;
        endif;
        $stmt->close();
        ?>

        <!-- Pagination controls -->
        <nav aria-label="Thread pagination">
            <ul class="pagination justify-content-center mt-4">
                <!-- Previous button -->
                <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?catid=<?php echo $id; ?>&page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                </li>

                <!-- Page numbers -->
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php if ($p == $page) echo 'active'; ?>">
                        <a class="page-link" href="?catid=<?php echo $id; ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next button -->
                <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>">
                    <a class="page-link" href="?catid=<?php echo $id; ?>&page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>

    <?php require "partials/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
