<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // This now loads your $conn (MySQLi object)

// Define categories with their display names and database keys
// Make sure these category_key values match your 'products' table 'category_key' column
$categories = [
    'ALL' => 'All Products', // For displaying all products
    'ACC' => 'Accessories',
    'APP' => 'Apparel',
    'BIK' => 'Bikes',
    'PAR' => 'Parts & Components',
];

// Determine the currently selected category
$selectedCategory = $_GET['category'] ?? 'ALL'; // Default to 'ALL' if no category is selected

// Initialize search term
$searchTerm = '';
$rawSearchTerm = ''; // Store the raw search term for pre-filling the input
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $rawSearchTerm = $_GET['search']; // Store raw value
    $searchTerm = '%' . $conn->real_escape_string($rawSearchTerm) . '%'; // Sanitize for LIKE query
}

// Prepare and execute the query to fetch products
try {
    $sql = "SELECT
                p.id AS product_id,
                p.name AS product_name,
                p.price,
                p.description,
                p.category_key,
                p.subcategory_key,
                (SELECT pi.image_path
                 FROM product_images pi
                 WHERE pi.product_id = p.id
                 ORDER BY pi.id ASC
                 LIMIT 1) AS main_image_path
            FROM
                products p";

    $whereClauses = [];
    $paramTypes = '';
    $params = [];

    // Add category filter if a specific category is selected (not 'ALL')
    if ($selectedCategory !== 'ALL' && array_key_exists($selectedCategory, $categories)) {
        $whereClauses[] = "p.category_key = ?";
        $paramTypes .= 's';
        $params[] = $selectedCategory;
    }

    // Add search condition if a search term is provided
    if (!empty($searchTerm)) {
        $whereClauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $paramTypes .= 'ss';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Combine WHERE clauses
    if (!empty($whereClauses)) {
        $sql .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $sql .= " ORDER BY p.created_at DESC";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Check if statement preparation failed
    if ($stmt === false) {
        throw new Exception('MySQLi prepare failed: ' . $conn->error);
    }

    // Bind parameters if any
    if (!empty($params)) {
        $bindArgs = array_merge([$paramTypes], $params);
        $stmt->bind_param(...$bindArgs);
    }

    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Fetch all rows as an associative array
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Close the statement
    $stmt->close();

} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    echo "<p class='alert alert-danger'>An error occurred while fetching products. Please try again later.</p>";
    $products = [];
}
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
        :root {
            --primary: #006A4E;
            --secondary: #FFB703;
            --accent: #00BFA6;
            --bg-light: #F4F4F4;
            --bg-dark: #003D33;
            --text-dark: #1E1E1E;
            --text-light: #FFFFFF;
            --border-gray: #D9D9D9;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-dark);
            font-family: 'Montserrat', sans-serif;
        }

        .navbar {
            background-color: var(--primary);
        }

        .navbar-brand,
        .nav-link {
            color: var(--text-light) !important;
            position: relative;
            padding-bottom: 5px;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--secondary) !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--secondary);
        }

        .btn-accent {
            background-color: var(--accent);
            color: var(--text-light);
        }

        .btn-accent:hover {
            background-color: var(--secondary);
            color: var(--text-dark);
        }

        footer {
            background-color: var(--bg-dark);
            color: var(--text-light);
            padding: 40px 0;
        }

        .footer-link {
            color: var(--text-light);
            text-decoration: none;
        }

        .footer-link:hover {
            color: var(--secondary);
        }

        .border-top {
            border-top: 1px solid var(--border-gray);
        }

        .navbar-logo {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            object-fit: contain;
        }

        .navbar-brand-text {
            font-weight: 600;
            font-size: 1.2rem;
        }

        /* Header Section Styles */
        .shop-header {
            background-color: var(--text-light);
            padding: 40px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .shop-header h1 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .shop-header .form-control {
            border-color: var(--primary);
        }

        .shop-header .btn-outline-success {
            color: var(--primary);
            border-color: var(--primary);
        }
        .shop-header .btn-outline-success:hover {
            background-color: var(--primary);
            color: var(--text-light);
        }

        /* Category Tabs Styling */
        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .category-tabs .nav-item {
            margin-bottom: 10px; /* Add margin-bottom to create space between rows */
        }

        .category-tabs .nav-link {
            position: relative;
            color: var(--text-light);
            background-color: var(--accent);
            font-weight: 600;
            border: 1px solid var(--accent);
            border-radius: .25rem;
            padding: 0.75rem 1.25rem;
            transition: transform 0.3s ease;
            margin: 0 5px;
            text-decoration: none;
        }

        /* Hover state for all tabs */
        .category-tabs .nav-link:hover {
            transform: translateY(-2px);
        }

        /* Active tab state */
        .category-tabs .nav-link.active {
            color: var(--text-light) !important;
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 106, 78, 0.1);
        }

        /* Line below active tab */
        .category-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 3px;
            background-color: var(--secondary);
            border-radius: 2px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }


        /* Product Card Specific Styles */
        .product-card {
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            overflow: hidden;
            background-color: var(--text-light);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease-in-out;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            border-bottom: 1px solid var(--border-gray);
        }

        .product-card .card-body {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-card .card-title {
            font-size: 1rem; /* Changed from 1.25rem to 1rem */
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
        }

        /* Price text size now 1rem */
        .product-card .product-price {
            font-size: 1rem; /* Changed from 1.25rem to 1rem */
            font-weight: 700;
            color: var(--accent);
            margin-top: 10px;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php include '../components/navigation.php'; ?>

    <header class="shop-header">
        <div class="container">
            <h1 class="text-center display-4 mb-4">Explore Our Collection</h1>

            <ul class="nav nav-pills justify-content-center mb-4 category-tabs">
                <?php foreach ($categories as $key => $name): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($selectedCategory === $key) ? 'active' : ''; ?>"
                           href="shop.php?category=<?php echo htmlspecialchars($key); ?><?php echo !empty($rawSearchTerm) ? '&search=' . htmlspecialchars($rawSearchTerm) : ''; ?>">
                            <?php echo htmlspecialchars($name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <form action="shop.php" method="GET" class="d-flex">
                        <input class="form-control form-control-lg me-2" type="search" placeholder="Search bikes, apparel, parts..." aria-label="Search" name="search" value="<?php echo htmlspecialchars($rawSearchTerm); ?>">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                        <button class="btn btn-outline-success btn-lg" type="submit">Search</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="container mb-5">
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card product-card">
                            <a href="product_details.php?id=<?php echo $product['product_id']; ?>">
                                <img src="<?php
                                    echo !empty($product['main_image_path']) ? '../' . htmlspecialchars($product['main_image_path']) : '../assets/images/placeholder.webp';
                                ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <div class="product-price">
                                    ₱<?php echo number_format($product['price'], 2); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="lead">No products found at the moment<?php echo !empty($rawSearchTerm) ? " for '" . htmlspecialchars($rawSearchTerm) . "'" : ""; ?> in this category<?php echo ($selectedCategory !== 'ALL' && array_key_exists($selectedCategory, $categories)) ? " for '" . htmlspecialchars($categories[$selectedCategory]) . "'" : ""; ?>.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
</body>
</html>