<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Ensure this path is correct for your database connection

// Get search term and category filter from URL, if any
$searchTerm = $_GET['search'] ?? '';
$searchTerm = trim($searchTerm); // Trim whitespace
$selectedCategory = $_GET['category'] ?? ''; // Get selected category

// Fetch products with their variations and images
$products = [];
$sql = "SELECT
            p.id AS product_id,
            p.name AS product_name,
            p.category,
            pv.id AS variation_id,
            pv.price,
            pi.image_path,
            pi.is_main
        FROM
            products p
        JOIN
            product_variations pv ON p.id = pv.product_id
        LEFT JOIN
            product_images pi ON p.id = pi.product_id";

$conditions = [];
$params = [];
$paramTypes = '';

if (!empty($searchTerm)) {
    $conditions[] = "p.name LIKE ?";
    $params[] = '%' . $searchTerm . '%';
    $paramTypes .= 's';
}

// Add category filtering (excluding 'Discounted')
if (!empty($selectedCategory) && $selectedCategory !== 'Discounted') {
    // Filter by specific category
    $conditions[] = "p.category = ?";
    $params[] = $selectedCategory;
    $paramTypes .= 's';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY p.name, pv.size, pv.color, pi.is_main DESC, pi.id ASC"; // Order by is_main to get main image first

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($paramTypes)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product_id = $row['product_id'];
            if (!isset($products[$product_id])) {
                $products[$product_id] = [
                    'name' => $row['product_name'],
                    'category' => $row['category'],
                    'variations' => [],
                    'images' => []
                ];
            }

            // Add image paths to the product, ensuring uniqueness
            $cleanedImagePath = ltrim($row['image_path'], './');
            if (!empty($row['image_path']) && !in_array($cleanedImagePath, array_column($products[$product_id]['images'], 'path'))) {
                   $products[$product_id]['images'][] = [
                        'path' => $cleanedImagePath,
                        'is_main' => $row['is_main']
                    ];
            }

            // Store the first variation's price for display
            if (empty($products[$product_id]['variations'])) {
                $products[$product_id]['variations'][] = [
                    'id' => $row['variation_id'],
                    'price' => number_format($row['price'], 2) // Original price, formatted
                ];
            }
        }
    }
    $stmt->close();
}
$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bong Bicycle Shop - Shop</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
    <style>
        .product-card {
            border: 1px solid var(--border-gray);
            border-radius: .25rem;
            background-color: #fff;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-top-left-radius: .25rem;
            border-top-right-radius: .25rem;
        }

        .product-separator {
            border-top: 1px solid var(--border-gray);
            margin: 1rem 0;
        }

        /* Styles for the search header */
        .search-header {
            background-color: #ffffff;
            padding: 2rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
            top: 56px;
            width: 100%;
            z-index: 1020;
        }
        .search-header h1 {
            color: var(--primary);
            font-weight: bold;
        }
        .search-header .form-control {
            border-radius: .25rem;
        }
        .search-header .btn {
            border-radius: .25rem;
        }
        .search-header .btn i {
            font-size: 1.2rem;
        }

        /* Styles for category buttons */
        .category-buttons .btn {
            margin: 0.25rem;
            /* To make buttons the same size, you can use flexbox on their parent container
               and/or give them a min-width. */
            flex-grow: 1; /* Allows buttons to grow to fill space */
            min-width: 120px; /* Example minimum width */
            text-align: center;
        }

        .category-buttons {
            display: flex; /* Enable flexbox */
            flex-wrap: wrap; /* Allow buttons to wrap to the next line */
            justify-content: center; /* Center buttons horizontally */
            gap: 0.5rem; /* Space between buttons */
        }

        .category-buttons .btn.active {
            background-color: var(--primary);
            color: var(--text-light);
            border-color: var(--primary);
        }
    </style>
</head>
<body>

<?php include '../components/navigation.php'; ?>

<header class="search-header">
    <div class="container">
        <h1 class="text-center display-4 mb-4">Explore Our Collection</h1>
        <div class="row justify-content-center align-items-center g-2">
            <div class="col-12 col-md-8 col-lg-6 mb-3 mb-md-0">
                <form class="d-flex" action="collection.php" method="GET">
                    <input class="form-control me-2" type="search" placeholder="Search products..." aria-label="Search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <button class="btn btn-accent" type="submit">SEARCH</button>
                    <?php if (!empty($selectedCategory)): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-12 col-lg-9 text-center category-buttons">
                <a href="collection.php?search=<?php echo htmlspecialchars($searchTerm); ?>" class="btn btn-outline-secondary <?php echo empty($selectedCategory) ? 'active' : ''; ?>">ALL PRODUCTS</a>
                <a href="collection.php?search=<?php echo htmlspecialchars($searchTerm); ?>&category=Bikes" class="btn btn-outline-secondary <?php echo ($selectedCategory === 'Bikes') ? 'active' : ''; ?>">BIKES</a>
                <a href="collection.php?search=<?php echo htmlspecialchars($searchTerm); ?>&category=Apparel" class="btn btn-outline-secondary <?php echo ($selectedCategory === 'Apparel') ? 'active' : ''; ?>">APPAREL</a>
                <a href="collection.php?search=<?php echo htmlspecialchars($searchTerm); ?>&category=<?php echo urlencode('Parts & Components'); ?>" class="btn btn-outline-secondary <?php echo ($selectedCategory === 'Parts & Components') ? 'active' : ''; ?>">PARTS & COMPONENTS</a>
                <a href="collection.php?search=<?php echo htmlspecialchars($searchTerm); ?>&category=Accessories" class="btn btn-outline-secondary <?php echo ($selectedCategory === 'Accessories') ? 'active' : ''; ?>">ACCESSORIES</a>
            </div>
        </div>
    </div>
</header>

<div class="container my-5">
    <?php if (empty($products)): ?>
    <div class="alert alert-info text-center" role="alert">
        <?php echo !empty($searchTerm) ? 'No products found matching "'.htmlspecialchars($searchTerm).'".' : 'No products found at the moment. Please check back later!'; ?>
    </div>
    <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 d-flex justify-content-center">
        <?php foreach ($products as $productId => $product): ?>
            <div class="col">
                <div class="card h-100 product-card shadow-sm">
                    <?php
                    $displayImagePath = '../assets/images/no_image.png'; // Default local placeholder

                    // Find the main image or the first image
                    if (!empty($product['images'])) {
                        $mainImageFound = false;
                        foreach ($product['images'] as $image) {
                            if (isset($image['is_main']) && $image['is_main'] == 1) {
                                $displayImagePath = '../' . htmlspecialchars($image['path']);
                                $mainImageFound = true;
                                break;
                            }
                        }
                        // If no main image, just use the first one available
                        if (!$mainImageFound && isset($product['images'][0]['path'])) {
                            $displayImagePath = '../' . htmlspecialchars($product['images'][0]['path']);
                        }
                    }
                    ?>
                    <img src="<?php echo $displayImagePath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?> Image">

                    <hr class="product-separator">

                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title" style="color:var(--primary);"><?php echo htmlspecialchars($product['name']); ?></h6>
                        <?php
                        $displayPrice = 'N/A';
                        if (!empty($product['variations'])) {
                            $firstVariation = $product['variations'][0];
                            $displayPrice = '<span class="price">₱' . htmlspecialchars($firstVariation['price']) . '</span>';
                        }
                        ?>
                        <p class="card-text product-price mt-2"><?php echo $displayPrice; ?></p>

                        <div class="mt-auto">
                            <a href="product_details.php?id=<?php echo htmlspecialchars($productId); ?>" class="btn btn-accent btn-sm">VIEW DETAILS</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
</body>
</html>