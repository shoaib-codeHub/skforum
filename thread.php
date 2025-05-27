<?php
require "partials/header.php";
require "partials/dbconnect.php";

$title = '';
$desc = '';
$thread_user_id = null;

$id = isset($_GET['threadid']) && is_numeric($_GET['threadid']) ? intval($_GET['threadid']) : null;

if (!$id) {
    echo '<div class="alert alert-danger container mt-5">Invalid or missing thread ID. Please go back and try again.</div>';
    require "partials/footer.php";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM `threads` WHERE `thread_id` = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $title = $row['thread_title'];
    $desc = $row['thread_desc'];
    $thread_user_id = $row['thread_user_id'];
}
$stmt->close();

// Fetch poster's email
$poster_email = "Unknown User";
if ($thread_user_id !== null) {
    $stmtUser = $conn->prepare("SELECT `user_email` FROM `users` WHERE `sno` = ?");
    $stmtUser->bind_param("i", $thread_user_id);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    if ($userRow = $resUser->fetch_assoc()) {
        $poster_email = htmlspecialchars($userRow['user_email']);
    }
    $stmtUser->close();
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlackCode-hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .ques {
            min-height: 433px;
        }

        #ques {
            background-color: #d3e2e6;
        }
    </style>
</head>

<body>

    <div class="container mt-5" id="ques">
        <div class="p-5 mb-4 rounded-3">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold"><?php echo ucfirst(htmlspecialchars($title)); ?> Forums!</h1>
                <p class="col-md-8 fs-4"><?php echo ucfirst(htmlspecialchars($desc)); ?></p>
                <hr class="my-4">
                <p class="lead mb-2">Welcome! Use this forum to share knowledge, ask questions, and help others grow.</p>
                <p class="text-muted">Posted by: <strong><?php echo $poster_email; ?></strong></p>
            </div>
        </div>
    </div>

    <?php
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true):
        $formMessage = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $content = mysqli_real_escape_string($conn, $_POST['desc']);
            $comment_by = $_SESSION['sno'];

            $stmtComment = $conn->prepare("INSERT INTO `comments` (`content`, `thread_id`, `comment_time`, `comment_by`) VALUES (?, ?, current_timestamp(), ?)");
            $stmtComment->bind_param("sii", $content, $id, $comment_by);
            if ($stmtComment->execute()) {
                $formMessage = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your comment has been posted.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            } else {
                $formMessage = '<div class="alert alert-danger">Error: ' . $stmtComment->error . '</div>';
            }
            $stmtComment->close();
        }
    ?>

        <div class="container mb-4">
            <?php echo $formMessage; ?>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Post a Comment</h5>
                </div>
                <div class="card-body bg-light">
                    <form action="<?php echo $_SERVER['REQUEST_URI'] ?>" method="post">
                        <div class="mb-3">
                            <label for="desc" class="form-label fw-semibold">Your Comment</label>
                            <textarea class="form-control" id="desc" name="desc" rows="4" maxlength="1000"
                                placeholder="Describe your comment in detail..." required></textarea>
                            <div class="form-text text-muted">
                                Provide all relevant information so others understand about this.
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center p-3 rounded-3 container" role="alert">
            <div>
                <strong>Login Required</strong><br>
                <small>You must be logged in to comment.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-dark ms-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </div>
    <?php endif; ?>

    <div class="container ques">
        <h1>Discussions</h1>
        <?php
        // Pagination variables
        $comments_per_page = 20;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $comments_per_page;

        // Total comments count
        $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM comments WHERE thread_id = ?");
        $stmtCount->bind_param("i", $id);
        $stmtCount->execute();
        $resCount = $stmtCount->get_result();
        $total_comments = 0;
        if ($rowCount = $resCount->fetch_assoc()) {
            $total_comments = $rowCount['total'];
        }
        $stmtCount->close();

        $total_pages = ceil($total_comments / $comments_per_page);

        // Fetch comments with limit and offset
        $stmtComments = $conn->prepare("SELECT c.*, u.user_email FROM comments c JOIN users u ON c.comment_by = u.sno WHERE c.thread_id = ? ORDER BY c.comment_time DESC LIMIT ?, ?");
        $stmtComments->bind_param("iii", $id, $offset, $comments_per_page);
        $stmtComments->execute();
        $resComments = $stmtComments->get_result();

        if ($resComments->num_rows === 0) {
            echo '<p class="text-muted">No comments yet. Be the first to start the discussion!</p>';
        } else {
            while ($row = $resComments->fetch_assoc()) {
                $commentTime = new DateTime($row['comment_time']);
                echo '<div class="d-flex align-items-start my-3">';
                echo '<img src="https://via.placeholder.com/50" class="rounded-circle me-3" alt="user">';
                echo '<div>';
                echo '<p class="mb-1 text-secondary small">Posted By <strong class="text-success">' . htmlspecialchars($row['user_email']) . '</strong> &mdash; <span class="text-muted">' . $commentTime->format('M j, Y \a\t g:i A') . '</span></p>';
                echo '<p>' . htmlspecialchars($row['content']) . '</p>';
                echo '</div></div>';
            }
        }
        $stmtComments->close();

        // Pagination navigation
        if ($total_pages > 1) {
            echo '<nav aria-label="Comments Pagination">';
            echo '<ul class="pagination justify-content-center">';

            // Previous link
            if ($page > 1) {
                $prev_page = $page - 1;
                echo '<li class="page-item"><a class="page-link" href="?threadid=' . $id . '&page=' . $prev_page . '">&laquo; Previous</a></li>';
            } else {
                echo '<li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>';
            }

            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                } else {
                    echo '<li class="page-item"><a class="page-link" href="?threadid=' . $id . '&page=' . $i . '">' . $i . '</a></li>';
                }
            }

            // Next link
            if ($page < $total_pages) {
                $next_page = $page + 1;
                echo '<li class="page-item"><a class="page-link" href="?threadid=' . $id . '&page=' . $next_page . '">Next &raquo;</a></li>';
            } else {
                echo '<li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>';
            }

            echo '</ul>';
            echo '</nav>';
        }
        ?>
    </div>

    <?php require "partials/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>