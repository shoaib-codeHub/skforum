<?php
if (headers_sent()) {
  die("Output started before session_start()");
}
session_start();
echo '
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">BlackCode-hub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="about.php">About</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
           Top Categories
          </a>
          <ul class="dropdown-menu">';

// Make sure to include your database connection before this section
require_once __DIR__ . '/../partials/dbconnect.php';

$sql = "SELECT category_name, category_id FROM `categories`";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
  echo '<li><a class="dropdown-item" href="threadlist.php?catid=' . htmlspecialchars($row['category_id']) . '">' . htmlspecialchars($row['category_name']) . '</a></li>';
}
echo '
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contact.php">Contact</a>
        </li>
      </ul>';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  echo '
      <div class="d-flex align-items-center flex-wrap gap-2 my-2">';
  echo '
        <form class="d-flex" role="search" method="get" action="search.php">
  <input class="form-control me-2" type="search" placeholder="Search" name="search" aria-label="Search">
  <button class="btn btn-outline-success" type="submit">Search</button>
</form>
';
  echo '
        <span class="text-light fw-semibold ms-2">Welcome, ' . htmlspecialchars($_SESSION['email']) . '</span>
        <a href="partials/logout.php" class="btn btn-danger ms-2">Logout</a>
      </div>';
} else {
  echo '
      <div class="d-flex align-items-center flex-wrap gap-2 my-2">
        <form class="d-flex" role="search">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
        <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#signupModal">Sign Up</button>
      </div>';
}
echo '
    </div>
  </div>
</nav>';

// Include modals
require 'partials/loginmodal.php';
require 'partials/signupmodal.php';

// Alert after signup
if (isset($_GET['signupsuccess']) && $_GET['signupsuccess'] == "true") {
  echo '
  <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
    <strong>Success!</strong> You have signed up successfully. You can now log in.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>';
}
