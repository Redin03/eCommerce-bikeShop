<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Your database connection

$product = null;
$productImages = [];
$productVariations = [];
$error_message = '';

// Get product ID from URL
$product_id = $_GET['id'] ?? null;

if (!$product_id || !is_numeric($product_id)) {
    $error_message = "Invalid product ID provided.";
} else {
    try {
        // Fetch product details
        $sql_product = "SELECT id, name, category_key, subcategory_key, price, description FROM products WHERE id = ?";
        $stmt_product = $conn->prepare($sql_product);
        if ($stmt_product === false) {
            throw new Exception('MySQLi prepare failed: ' . $conn->error);
        }
        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        $product = $result_product->fetch_assoc();
        $stmt_product->close();

        if ($product) {
            // Fetch product images
            $sql_images = "SELECT image_path, is_main, display_order FROM product_images WHERE product_id = ? ORDER BY display_order ASC, id ASC";
            $stmt_images = $conn->prepare($sql_images);
            if ($stmt_images === false) {
                throw new Exception('MySQLi prepare failed: ' . $conn->error);
            }
            $stmt_images->bind_param("i", $product_id);
            $stmt_images->execute();
            $result_images = $stmt_images->get_result();
            while ($row = $result_images->fetch_assoc()) {
                $productImages[] = $row;
            }
            $stmt_images->close();

            // Fetch product variations
            $sql_variations = "SELECT color_name, size_name, quantity FROM product_variations WHERE product_id = ? ORDER BY color_name, size_name";
            $stmt_variations = $conn->prepare($sql_variations);
            if ($stmt_variations === false) {
                throw new Exception('MySQLi prepare failed: ' . $conn->error);
            }
            $stmt_variations->bind_param("i", $product_id);
            $stmt_variations->execute();
            $result_variations = $stmt_variations->get_result();
            while ($row = $result_variations->fetch_assoc()) {
                $productVariations[] = $row;
            }
            $stmt_variations->close();

        } else {
            $error_message = "Product not found.";
        }

    } catch (Exception $e) {
        error_log("Error fetching product details: " . $e->getMessage());
        $error_message = "An error occurred while loading product details. Please try again later.";
    }
}

// Group variations by color for easier display
$colors = [];
$sizesByColor = [];
foreach ($productVariations as $variation) {
    if (!in_array($variation['color_name'], $colors)) {
        $colors[] = $variation['color_name'];
    }
    if (!isset($sizesByColor[$variation['color_name']])) {
        $sizesByColor[$variation['color_name']] = [];
    }
    $sizesByColor[$variation['color_name']][] = [
        'size' => $variation['size_name'],
        'quantity' => $variation['quantity']
    ];
}

// Determine the main image for display
$mainDisplayImage = '../assets/images/placeholder.webp'; // Default placeholder
if (!empty($productImages)) {
    // Try to find an image marked as main
    foreach ($productImages as $img) {
        if ($img['is_main'] == 1) {
            $mainDisplayImage = '../' . htmlspecialchars($img['image_path']);
            break;
        }
    }
    // If no main image, use the first one from the list (ordered by display_order/id)
    if ($mainDisplayImage === '../assets/images/placeholder.webp') {
        $mainDisplayImage = '../' . htmlspecialchars($productImages[0]['image_path']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        <?php echo $product ? htmlspecialchars($product['name']) . ' - Bong Bicycle Shop' : 'Product Not Found - Bong Bicycle Shop'; ?>
    </title>

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

        /* Product Details Page Specific Styles */
        .product-details-container {
            background-color: var(--text-light);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .main-product-image-container {
            height: 450px; /* Fixed height for main image */
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .main-product-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Ensure entire image is visible */
        }

        .thumbnail-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .thumbnail-gallery img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border: 2px solid var(--border-gray);
            border-radius: 4px;
            cursor: pointer;
            transition: border-color 0.2s, transform 0.2s;
        }

        .thumbnail-gallery img:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .thumbnail-gallery img.active-thumbnail {
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(0, 106, 78, 0.5);
        }

        .product-title {
            color: var(--primary);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .product-price-details {
            color: var(--accent);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .product-description {
            color: var(--text-dark);
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .variation-section h5 {
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .variation-options .btn {
            margin-right: 10px;
            margin-bottom: 10px;
            background-color: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--border-gray);
            transition: all 0.2s ease;
        }

        .variation-options .btn:hover:not(.active) {
            background-color: var(--primary);
            color: var(--text-light);
            border-color: var(--primary);
        }

        .variation-options .btn.active {
            background-color: var(--primary);
            color: var(--text-light);
            border-color: var(--primary);
            box-shadow: 0 2px 5px rgba(0, 106, 78, 0.2);
        }

        .quantity-selector .form-control {
            width: 80px;
            text-align: center;
            border-color: var(--primary);
        }

        .add-to-cart-btn {
            background-color: var(--secondary);
            color: var(--text-dark);
            font-weight: 600;
            padding: 12px 25px;
            border-radius: .3rem;
            transition: background-color 0.2s, transform 0.2s;
        }

        .add-to-cart-btn:hover {
            background-color: var(--accent);
            color: var(--text-light);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../components/navigation.php'; ?>

    <main class="container my-5">
        <?php if ($error_message): ?>
            <div class="alert alert-danger text-center" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <br><a href="shop.php" class="btn btn-primary mt-3 btn-accent">Back to Shop</a>
            </div>
        <?php elseif ($product): ?>
            <div class="product-details-container">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="main-product-image-container">
                            <img id="mainProductImage" src="<?php echo htmlspecialchars($mainDisplayImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-product-image">
                        </div>
                        <?php if (count($productImages) > 1): ?>
                            <div class="thumbnail-gallery">
                                <?php foreach ($productImages as $index => $img): ?>
                                    <img src="../<?php echo htmlspecialchars($img['image_path']); ?>"
                                         alt="Thumbnail <?php echo $index + 1; ?>"
                                         class="product-thumbnail <?php echo ($mainDisplayImage === '../' . htmlspecialchars($img['image_path'])) ? 'active-thumbnail' : ''; ?>"
                                         data-full-image="../<?php echo htmlspecialchars($img['image_path']); ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-6">
                        <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                        <p class="product-price-details">₱<?php echo number_format($product['price'], 2); ?></p>

                        <div class="product-description mb-4">
                            <h4>Description</h4>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>

                        <?php if (!empty($colors)): ?>
                            <div class="variation-section mb-3">
                                <h5>Color: <span id="selectedColor" class="fw-bold text-primary"></span></h5>
                                <div class="variation-options" id="colorOptions">
                                    <?php foreach ($colors as $color): ?>
                                        <button type="button" class="btn btn-outline-secondary" data-color="<?php echo htmlspecialchars($color); ?>">
                                            <?php echo htmlspecialchars($color); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($sizesByColor)): ?>
                            <div class="variation-section mb-3">
                                <h5>Size: <span id="selectedSize" class="fw-bold text-primary"></span></h5>
                                <div class="variation-options" id="sizeOptions">
                                    </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center mb-4">
                            <h5 class="me-3 mb-0">Quantity:</h5>
                            <input type="number" class="form-control quantity-selector" id="quantityInput" value="1" min="1" max="1" disabled>
                            <span class="ms-2 text-muted" id="availableStock"></span>
                        </div>

                        <button type="button" class="btn add-to-cart-btn w-100" id="addToCartBtn" disabled>
                            <i class="bi bi-cart-plus me-2"></i> Add to Cart
                        </button>

                        <div id="cartMessage" class="mt-3 alert d-none" role="alert"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainProductImage = document.getElementById('mainProductImage');
            const productThumbnails = document.querySelectorAll('.product-thumbnail');
            const colorOptionsContainer = document.getElementById('colorOptions');
            const sizeOptionsContainer = document.getElementById('sizeOptions');
            const quantityInput = document.getElementById('quantityInput');
            const availableStockSpan = document.getElementById('availableStock');
            const addToCartBtn = document.getElementById('addToCartBtn');
            const selectedColorSpan = document.getElementById('selectedColor');
            const selectedSizeSpan = document.getElementById('selectedSize');
            const cartMessage = document.getElementById('cartMessage');

            let selectedColor = null;
            let selectedSize = null;
            let currentAvailableStock = 0;

            // PHP data passed to JavaScript
            const variationsData = <?php echo json_encode($productVariations); ?>;

            // --- Image Gallery Logic ---
            productThumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    mainProductImage.src = this.dataset.fullImage;
                    productThumbnails.forEach(t => t.classList.remove('active-thumbnail'));
                    this.classList.add('active-thumbnail');
                });
            });

            // Set initial active thumbnail if it matches main image
            const initialMainImageSrc = mainProductImage.src;
            productThumbnails.forEach(thumbnail => {
                if (thumbnail.dataset.fullImage === initialMainImageSrc) {
                    thumbnail.classList.add('active-thumbnail');
                }
            });


            // --- Variation Selection Logic ---

            // Function to update available sizes based on selected color
            function updateSizes() {
                sizeOptionsContainer.innerHTML = ''; // Clear previous sizes
                selectedSize = null; // Reset selected size
                selectedSizeSpan.textContent = ''; // Clear displayed size

                if (!selectedColor) {
                    quantityInput.value = 1;
                    quantityInput.max = 1;
                    quantityInput.disabled = true;
                    availableStockSpan.textContent = '';
                    addToCartBtn.disabled = true;
                    return;
                }

                const sizesForColor = variationsData.filter(v => v.color_name === selectedColor);
                if (sizesForColor.length > 0) {
                    sizesForColor.forEach(variation => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.classList.add('btn', 'btn-outline-secondary');
                        button.dataset.size = variation.size_name;
                        button.dataset.quantity = variation.quantity;
                        button.textContent = variation.size_name;
                        sizeOptionsContainer.appendChild(button);

                        button.addEventListener('click', function() {
                            sizeOptionsContainer.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
                            this.classList.add('active');
                            selectedSize = this.dataset.size;
                            currentAvailableStock = parseInt(this.dataset.quantity);
                            selectedSizeSpan.textContent = selectedSize;
                            updateQuantityInput();
                            addToCartBtn.disabled = false;
                        });
                    });
                } else {
                    sizeOptionsContainer.innerHTML = '<p class="text-muted">No sizes available for this color.</p>';
                    quantityInput.value = 1;
                    quantityInput.max = 1;
                    quantityInput.disabled = true;
                    availableStockSpan.textContent = '';
                    addToCartBtn.disabled = true;
                }
            }

            // Function to update quantity input and stock display
            function updateQuantityInput() {
                quantityInput.max = currentAvailableStock;
                quantityInput.value = Math.min(quantityInput.value, currentAvailableStock); // Ensure value doesn't exceed max
                quantityInput.disabled = currentAvailableStock === 0;
                availableStockSpan.textContent = `(${currentAvailableStock} in stock)`;

                if (currentAvailableStock === 0) {
                    addToCartBtn.disabled = true;
                    quantityInput.value = 0;
                } else {
                    addToCartBtn.disabled = false;
                }
            }

            // Event listener for color selection
            if (colorOptionsContainer) {
                colorOptionsContainer.addEventListener('click', function(event) {
                    if (event.target.tagName === 'BUTTON') {
                        colorOptionsContainer.querySelectorAll('.btn').forEach(btn => btn.classList.remove('active'));
                        event.target.classList.add('active');
                        selectedColor = event.target.dataset.color;
                        selectedColorSpan.textContent = selectedColor;
                        updateSizes(); // Update sizes when color changes
                    }
                });
            }

            // Event listener for quantity input changes
            quantityInput.addEventListener('input', function() {
                let val = parseInt(this.value);
                if (isNaN(val) || val < 1) {
                    val = 1;
                }
                if (val > currentAvailableStock) {
                    val = currentAvailableStock;
                }
                this.value = val;
            });

            // --- Add to Cart Logic (Placeholder) ---
            addToCartBtn.addEventListener('click', function() {
                if (!selectedColor || !selectedSize || quantityInput.value < 1 || quantityInput.value > currentAvailableStock) {
                    displayMessage('Please select a color and size, and ensure a valid quantity.', 'alert-warning');
                    return;
                }

                const productId = <?php echo json_encode($product['id'] ?? null); ?>;
                const productName = <?php echo json_encode($product['name'] ?? null); ?>;
                const price = <?php echo json_encode($product['price'] ?? null); ?>;
                const quantity = parseInt(quantityInput.value);

                // In a real application, you would send this data to a server-side script
                // via AJAX to add to a session cart or database cart.
                console.log({
                    productId: productId,
                    productName: productName,
                    selectedColor: selectedColor,
                    selectedSize: selectedSize,
                    quantity: quantity,
                    pricePerItem: price
                });

                displayMessage(`Added ${quantity} of ${productName} (${selectedColor}, ${selectedSize}) to cart!`, 'alert-success');

                // Optionally, reset selections or clear message after a delay
                // setTimeout(() => {
                //     // Reset UI or redirect
                // }, 2000);
            });

            function displayMessage(message, type) {
                cartMessage.textContent = message;
                cartMessage.className = `mt-3 alert ${type}`; // Reset classes
                cartMessage.classList.remove('d-none');
                setTimeout(() => {
                    cartMessage.classList.add('d-none');
                }, 3000); // Hide after 3 seconds
            }

            // Initial state update
            updateSizes(); // Call once to set initial quantity/stock state
        });
    </script>
</body>
</html>