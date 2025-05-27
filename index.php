<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BlackCode-hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
</head>

<body>
    <?php require "partials/dbconnect.php"; ?>
    <?php require('partials/header.php'); // ✅ CORRECT ?>
    <!-- Carousel Section -->
    <div id="carouselExampleIndicators" class="carousel slide">
        <div class="carousel-indicators">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $i ?>"
                    class="<?= $i === 0 ? 'active' : '' ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                    aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endfor; ?>
        </div>
        <div class="carousel-inner">
            <?php
            $searchQuery = "coding";
            $accessKey = "BVyFTh3RaF93Ogq--ffRbY3nDKdjD7iNI59RqhWgqVo";
            $fallback = "https://via.placeholder.com/800x400?text=Image+Not+Found";

            for ($i = 0; $i < 3; $i++) {
                $url = "https://api.unsplash.com/photos/random?query=" . urlencode($searchQuery) . "&client_id=" . $accessKey;
                $response = @file_get_contents($url);
                $img = $fallback;
                if ($response !== false) {
                    $data = json_decode($response, true);
                    if (isset($data['urls']['regular'])) {
                        $img = $data['urls']['regular'];
                    }
                }
                echo '
                    <div class="carousel-item ' . ($i === 0 ? 'active' : '') . '">
                        <img src="' . htmlspecialchars($img) . '" alt="Coding ' . ($i + 1) . '" class="d-block w-100" style="height:60vh; object-fit:cover;">
                    </div>
                ';
            }
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
            data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Categories Section -->



    <div class="container mt-5">
        <h2 class="text-center mb-4">BlackCode-hub Browse Categories</h2>
        <div class="row my-4">
            <?php
            $sql = 'SELECT * FROM `categories`';
            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                $category = $row['category_name'];
                $desc = $row['category_description'];
                $catid = $row['category_id'];

                $url = "https://api.unsplash.com/photos/random?query=" . urlencode($category) . "&client_id=" . $accessKey;
                $response = @file_get_contents($url);
                $imageUrl = "https://via.placeholder.com/286x180?text=Image+Not+Found";

                if ($response !== false) {
                    $data = json_decode($response, true);
                    if (isset($data['urls']['regular'])) {
                        $imageUrl = $data['urls']['regular'];
                    }
                }

                echo '
                        <div class="col-md-4 mb-4 d-flex justify-content-center">
                        <div class="card" style="width: 18rem;">
                        <img src="' . htmlspecialchars($imageUrl) . '" class="card-img-top" alt="Image for ' . htmlspecialchars($category) . '" style="height:180px; object-fit:cover;">
                        <div class="card-body">
                        <h5 class="card-title">
                            <a href="threadlist.php?catid=' . ucfirst(htmlspecialchars($catid)) . '" style="text-decoration: none; color: inherit;">
                                ' . ucfirst(htmlspecialchars($category)) . '
                            </a>
                        </h5>
                        <p class="card-text">' . ucfirst(htmlspecialchars(substr($desc, 0, 70))) . '...</p>
                        <a href="threadlist.php?catid=' . ucfirst(htmlspecialchars($catid)) . '" class="btn btn-primary">Explore ' . ucfirst(htmlspecialchars($category)) . '</a>
                        </div>
                        </div>
                        </div>';
            }
            ?>
        </div>
    </div>

    <?php require "partials/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq"
        crossorigin="anonymous"></script>
</body>

</html>