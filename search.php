<?php
require 'partials/dbconnect.php';
require 'partials/header.php';

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query_escaped = mysqli_real_escape_string($conn, $search_query);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 5;
$offset = ($page - 1) * $results_per_page;

function highlightSearchTerm($text, $term)
{
    return preg_replace("/(" . preg_quote($term, '/') . ")/i", '<mark class="custom-highlight">$1</mark>', $text);
}

if (strlen($search_query) >= 4) {
    $sql_base = "SELECT t.*, c.category_name, c.category_id 
                 FROM threads t
                 JOIN categories c ON t.thread_cat_id = c.category_id
                 WHERE MATCH(t.thread_title, t.thread_desc) 
                 AGAINST ('$search_query_escaped')";
} else {
    $sql_base = "SELECT t.*, c.category_name, c.category_id 
                 FROM threads t
                 JOIN categories c ON t.thread_cat_id = c.category_id
                 WHERE t.thread_title LIKE '%$search_query_escaped%' 
                 OR t.thread_desc LIKE '%$search_query_escaped%'";
}

$total_result = mysqli_query($conn, $sql_base);
$total_rows = mysqli_num_rows($total_result);
$total_pages = ceil($total_rows / $results_per_page);

$sql_paginated = $sql_base . " ORDER BY t.timestamp DESC LIMIT $offset, $results_per_page";
$result = mysqli_query($conn, $sql_paginated);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search - BlackCode-hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        mark.custom-highlight {
            background-color: #cfe2ff;
            color: #084298;
            padding: 2px 4px;
            border-radius: 3px;
        }
    </style>
</head>

<body>

    <div class="container my-4" style="min-height: 80vh;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
            <h2 class="mb-0">
                Search results for <em class="text-primary">"<?= htmlspecialchars($search_query) ?>"</em>
            </h2>
        </div>



        <?php
        if ($total_rows > 0) {
            echo "<p class='text-muted'>Found <strong>$total_rows</strong> results.</p>";

            while ($row = mysqli_fetch_assoc($result)) {
                $highlightedTitle = highlightSearchTerm(htmlspecialchars($row['thread_title']), $search_query);
                $highlightedDesc = highlightSearchTerm(htmlspecialchars($row['thread_desc']), $search_query);
                $categoryLink = 'threadlist.php?catid=' . $row['category_id'];

                echo '<div class="card mb-3 shadow-sm">
              <div class="card-body">
                  <h5 class="card-title">
                      <a href="thread.php?threadid=' . $row['thread_id'] . '" class="text-decoration-none text-dark">'
                    . $highlightedTitle . '</a>
                  </h5>
                  <p class="card-text">' . $highlightedDesc . '</p>
                  <p class="mb-2">
                      <small class="text-muted">Posted in 
                          <a href="' . $categoryLink . '" class="text-decoration-underline text-primary fw-semibold">
                              ' . htmlspecialchars($row['category_name']) . '
                          </a>
                      </small>
                  </p>
                  <a href="thread.php?threadid=' . $row['thread_id'] . '" class="btn btn-outline-primary btn-sm">View Thread</a>
              </div>
          </div>';
            }

            // Pagination
            echo '<nav aria-label="Search pagination"><ul class="pagination flex-wrap">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<li class="page-item ' . $active . '">
                  <a class="page-link" href="?search=' . urlencode($search_query) . '&page=' . $i . '">' . $i . '</a>
                </li>';
            }
            echo '</ul></nav>';
        } else {
            echo '<div class="alert alert-warning">No results found. Try a different keyword.</div>';
        }
        ?>
    </div>

    <?php require 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>