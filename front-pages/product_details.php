<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // This path for db.php remains correct

$product = null;
$productId = $_GET['id'] ?? null;

if ($productId) {
    // Fetch product details, variations, and images
    $sql = "SELECT
                p.id AS product_id,
                p.name AS product_name,
                p.category,
                p.subcategory,
                p.description,
                pv.id AS variation_id,
                pv.size,
                pv.color,
                pv.stock,
                pv.price,
                pv.discount_percentage,
                pv.discount_expiry_date,
                pi.image_path,
                pi.is_main
            FROM
                products p
            LEFT JOIN
                product_variations pv ON p.id = pv.product_id
            LEFT JOIN
                product_images pi ON p.id = pi.product_id
            WHERE
                p.id = ?
            ORDER BY
                pv.id ASC, pi.is_main DESC, pi.id ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($product === null) {
                    $product = [
                        'id' => $row['product_id'],
                        'name' => $row['product_name'],
                        'category' => $row['category'],
                        'subcategory' => $row['subcategory'],
                        'description' => $row['description'],
                        'variations' => [],
                        'images' => []
                    ];
                }

                // Add unique variations
                $variation_id = $row['variation_id'];
                if ($variation_id && !isset($product['variations'][$variation_id])) {
                    $current_price = (float)$row['price']; // Ensure it's a float
                    $discounted_price = $current_price;
                    $is_discounted = false;

                    if ($row['discount_percentage'] !== null && $row['discount_expiry_date'] !== null) {
                        $discount_expiry_timestamp = strtotime($row['discount_expiry_date']);
                        if (time() <= $discount_expiry_timestamp) { // Check if discount is still valid
                            $discount_amount = $current_price * ($row['discount_percentage'] / 100);
                            $discounted_price = $current_price - $discount_amount;
                            $is_discounted = true;
                        }
                    }

                    $product['variations'][$variation_id] = [
                        'id' => $variation_id,
                        'size' => $row['size'],
                        'color' => $row['color'],
                        'stock' => $row['stock'],
                        'original_price' => $current_price, // Store raw float
                        'display_price' => $discounted_price, // Store raw float
                        'discount_percentage' => $row['discount_percentage'],
                        'discount_expiry_date' => $row['discount_expiry_date'],
                        'is_discounted' => $is_discounted
                    ];
                }

                // Add unique images
                $cleanedImagePath = ltrim($row['image_path'], './');
                if (!empty($row['image_path']) && !in_array($cleanedImagePath, array_column($product['images'], 'path'))) {
                    $product['images'][] = [
                        'path' => $cleanedImagePath,
                        'is_main' => $row['is_main']
                    ];
                }
            }
        }
        $stmt->close();
    }
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
  <style>
    /* Product Details Page Specific Styles */
    .product-image-main {
        max-width: 100%;
        height: auto;
        border-radius: .5rem;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
    }
    .product-thumbnail-wrapper {
        width: 70px; /* Smaller width */
        height: 70px; /* Smaller height */
        margin: 4px; /* Adjusted spacing */
        overflow: hidden;
        border-radius: .25rem;
        border: 2px solid transparent;
        cursor: pointer;
        transition: border-color 0.2s;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .product-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .product-thumbnail-wrapper.active,
    .product-thumbnail-wrapper:hover {
        border-color: var(--primary);
    }

    .price-display .original-price {
        text-decoration: line-through;
        color: #6c757d;
        font-size: 1.1em; /* Adjusted font size */
        margin-right: 8px; /* Adjusted margin */
    }
    .price-display .current-price {
        color: var(--primary);
        font-weight: bold;
        font-size: 1.6em; /* Adjusted font size */
    }
    .price-display .discount-badge {
        background-color: #dc3545; /* Red for discount */
        color: white;
        padding: .2em .5em; /* Adjusted padding */
        border-radius: .2rem; /* Adjusted border-radius */
        font-size: .7em; /* Adjusted font size */
        vertical-align: middle;
        margin-left: 8px; /* Adjusted margin */
    }
    .variation-select {
        margin-top: 12px; /* Adjusted margin */
        margin-bottom: 5px; /* Reduced margin to make space for stock display */
    }
    .variation-select label {
        font-weight: bold;
        margin-bottom: 4px; /* Adjusted margin */
        font-size: 0.9rem; /* Adjusted font size */
    }
    .variation-select select {
        width: 100%;
        padding: .4rem .6rem; /* Adjusted padding */
        border: 1px solid var(--border-gray);
        border-radius: .25rem;
        font-size: 0.9rem; /* Adjusted font size */
    }
    /* New styles for product name */
    .product-details-name {
        font-size: 2.2rem; /* Slightly smaller product name */
        font-weight: 700; /* Bolder */
        color: var(--primary);
        margin-bottom: 0.4rem; /* Adjusted margin */
    }
    .card-text.lead {
        font-size: 0.95rem; /* Adjusted font size for description */
    }
    .btn {
        font-size: 0.9rem; /* Adjusted button font size */
        padding: .6rem 1.2rem; /* Adjusted button padding */
    }
    .stock-display {
        margin-top: 10px;
        margin-bottom: 20px;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-dark);
    }
    .button-group {
        display: flex;
        gap: 10px; /* Space between buttons */
        justify-content: flex-start; /* Align to start */
        flex-wrap: wrap; /* Allow wrapping on smaller screens */
    }
    .button-group .btn {
        flex: 1; /* Allow buttons to grow and shrink */
        min-width: 150px; /* Ensure buttons don't get too small */
    }
    .btn i {
        margin-right: 5px;
    }
  </style>
</head>
<body>

<?php include '../components/navigation.php'; // Include your navigation bar ?>

<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    </div>
</div>

<div class="container my-4">
    <?php if ($product): ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="main-image-container mb-3 text-center">
                        <?php
                        $mainImagePath = '../assets/images/no_image.png';
                        if (!empty($product['images'])) {
                            $foundMain = false;
                            foreach ($product['images'] as $image) {
                                if ($image['is_main'] == 1) {
                                    $mainImagePath = '../' . htmlspecialchars($image['path']);
                                    $foundMain = true;
                                    break;
                                }
                            }
                            if (!$foundMain && isset($product['images'][0])) {
                                $mainImagePath = '../' . htmlspecialchars($product['images'][0]['path']);
                            }
                        }
                        ?>
                        <img src="<?php echo $mainImagePath; ?>" class="img-fluid product-image-main" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="d-flex flex-wrap mt-3 justify-content-start">
                        <?php foreach ($product['images'] as $image): ?>
                            <div class="product-thumbnail-wrapper <?php echo ($image['path'] == ltrim($mainImagePath, '../')) ? 'active' : ''; ?>"
                                 onclick="changeMainImage(this.querySelector('img'))">
                                <img src="../<?php echo htmlspecialchars($image['path']); ?>" class="product-thumbnail img-fluid" alt="Thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h1 class="product-details-name"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <div class="price-display my-3">
                        <strong>Price: </strong>
                        <?php
                        $firstVariation = reset($product['variations']); // Get the first variation to display price
                        if ($firstVariation) {
                            $formattedOriginalPrice = number_format($firstVariation['original_price'], 2);
                            $formattedDisplayPrice = number_format($firstVariation['display_price'], 2);

                            if ($firstVariation['is_discounted']) {
                                echo '<span class="original-price">₱' . $formattedOriginalPrice . '</span> ';
                                echo '<span class="current-price">₱' . $formattedDisplayPrice . '</span>';
                                echo '<span class="badge discount-badge">SAVE ' . htmlspecialchars($firstVariation['discount_percentage']) . '%</span>';
                                echo '<p class="text-danger mt-1" style="font-size:0.85rem;">Discount ends: ' . date('M d, Y', strtotime($firstVariation['discount_expiry_date'])) . '</p>';
                            } else {
                                echo '<span class="current-price">₱' . $formattedDisplayPrice . '</span>';
                            }
                        } else {
                            echo '<span class="current-price">Price Not Available</span>';
                        }
                        ?>
                    </div>

                    <p class="card-text lead"><?php echo htmlspecialchars($product['description']); ?></p>

                    <?php if (!empty($product['variations'])): ?>
                    <div class="variation-select">
                        <label for="productVariation">Select Variation:</label>
                        <select class="form-select" id="productVariation">
                            <?php foreach ($product['variations'] as $variation): ?>
                                <option value="<?php echo htmlspecialchars($variation['id']); ?>"
                                        data-original-price="<?php echo htmlspecialchars($variation['original_price']); ?>"
                                        data-display-price="<?php echo htmlspecialchars($variation['display_price']); ?>"
                                        data-discount-percentage="<?php echo htmlspecialchars($variation['discount_percentage']); ?>"
                                        data-discount-expiry-date="<?php echo htmlspecialchars($variation['discount_expiry_date']); ?>"
                                        data-is-discounted="<?php echo $variation['is_discounted'] ? 'true' : 'false'; ?>"
                                        data-stock="<?php echo htmlspecialchars($variation['stock']); ?>">
                                    <?php
                                    $variation_display = [];
                                    if (!empty($variation['size']) && $variation['size'] !== 'Not Available') {
                                        $variation_display[] = 'Size: ' . htmlspecialchars($variation['size']);
                                    }
                                    if (!empty($variation['color']) && $variation['color'] !== 'Not Available') {
                                        $variation_display[] = 'Color: ' . htmlspecialchars($variation['color']);
                                    }
                                    echo implode(' / ', $variation_display);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="stock-display">
                        Stock: <span id="currentStock">
                            <?php
                                if ($firstVariation) {
                                    echo htmlspecialchars($firstVariation['stock'] == 0 ? 'No Stock Available' : $firstVariation['stock']);
                                } else {
                                    echo 'N/A';
                                }
                            ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <label for="quantityInput" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="quantityInput" value="1" min="1" max="<?php echo htmlspecialchars($firstVariation['stock']); ?>" style="width: 100px;">
                    </div>

                    <?php else: ?>
                        <p class="text-warning" style="font-size:0.9rem;">No variations available for this product.</p>
                    <?php endif; ?>

                    <div class="button-group mt-3">
                        <button class="btn btn-primary btn-accent" type="button" id="addToCartBtn"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                        <button class="btn btn-outline-secondary" type="button" id="buyNowBtn"><i class="bi bi-bag-check"></i> Buy Now</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning text-center" role="alert" style="font-size:1rem;">
      Product not found.
    </div>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; // Include your footer ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
<script>
    function changeMainImage(thumbnail) {
        const mainImage = document.querySelector('.product-image-main');
        mainImage.src = thumbnail.src;

        // Remove active class from all thumbnail wrappers
        document.querySelectorAll('.product-thumbnail-wrapper').forEach(wrapper => {
            wrapper.classList.remove('active');
        });
        // Add active class to the clicked thumbnail's wrapper
        thumbnail.closest('.product-thumbnail-wrapper').classList.add('active');
    }

    // Function to show Bootstrap toast messages
    function showToast(message, type = 'success') {
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Toast container not found!');
            return;
        }

        const iconClass = type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-x-circle-fill';
        const bgColorClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';

        const toastHtml = `
            <div class="toast align-items-center ${bgColorClass} border-0" role="alert" aria-live="assertive" aria-atomic="true"
                 data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${iconClass} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = toastHtml;
        const toastElement = tempDiv.firstElementChild;
        toastContainer.appendChild(toastElement);

        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast from DOM after it's hidden to keep DOM clean
        toastElement.addEventListener('hidden.bs.toast', function () {
            toastElement.remove();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const variationSelect = document.getElementById('productVariation');
        const currentStockSpan = document.getElementById('currentStock');
        const quantityInput = document.getElementById('quantityInput'); // Get quantity input
        const addToCartBtn = document.getElementById('addToCartBtn');
        const buyNowBtn = document.getElementById('buyNowBtn');

        function formatCurrency(value) {
            return parseFloat(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function updateProductUI() {
            if (!variationSelect || variationSelect.options.length === 0) {
                if (addToCartBtn) addToCartBtn.disabled = true;
                if (buyNowBtn) buyNowBtn.disabled = true;
                if (currentStockSpan) currentStockSpan.textContent = 'N/A';
                if (quantityInput) { // Disable and set max to 0 if no variations
                    quantityInput.disabled = true;
                    quantityInput.value = 0;
                    quantityInput.setAttribute('max', 0);
                }
                return;
            }

            const selectedOption = variationSelect.options[variationSelect.selectedIndex];
            const originalPrice = parseFloat(selectedOption.dataset.originalPrice);
            const displayPrice = parseFloat(selectedOption.dataset.displayPrice);
            const discountPercentage = selectedOption.dataset.discountPercentage;
            const discountExpiryDate = selectedOption.dataset.discountExpiryDate;
            const isDiscounted = selectedOption.dataset.isDiscounted === 'true';
            const stock = parseInt(selectedOption.dataset.stock);

            const priceDisplay = document.querySelector('.price-display');
            let priceHtml = '<strong>Price: </strong>';

            if (isDiscounted) {
                priceHtml += `<span class="original-price">₱${formatCurrency(originalPrice)}</span> `;
                priceHtml += `<span class="current-price">₱${formatCurrency(displayPrice)}</span>`;
                priceHtml += `<span class="badge discount-badge">SAVE ${discountPercentage}%</span>`;
                priceHtml += `<p class="text-danger mt-1" style="font-size:0.85rem;">Discount ends: ${new Date(discountExpiryDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</p>`;
            } else {
                priceHtml += `<span class="current-price">₱${formatCurrency(displayPrice)}</span>`;
            }
            priceDisplay.innerHTML = priceHtml;

            if (currentStockSpan) {
                currentStockSpan.textContent = stock === 0 ? 'No Stock Available' : stock;
            }

            if (quantityInput) {
                quantityInput.setAttribute('max', stock); // Set max quantity to available stock
                if (parseInt(quantityInput.value) > stock) {
                    quantityInput.value = stock > 0 ? 1 : 0; // Reset quantity if it exceeds new max
                }
                quantityInput.disabled = stock === 0; // Disable quantity input if no stock
            }

            if (stock === 0) {
                if (addToCartBtn) addToCartBtn.disabled = true;
                if (buyNowBtn) buyNowBtn.disabled = true;
            } else {
                if (addToCartBtn) addToCartBtn.disabled = false;
                if (buyNowBtn) buyNowBtn.disabled = false;
            }
        }

        if (variationSelect) {
            variationSelect.addEventListener('change', updateProductUI);
        }

        // Add event listener for Add to Cart button
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const selectedVariationId = variationSelect ? variationSelect.value : null;
                const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

                if (!selectedVariationId) {
                    showToast('Please select a product variation.', 'error');
                    return;
                }
                if (quantity <= 0) {
                    showToast('Quantity must be at least 1.', 'error');
                    return;
                }
                const currentStock = parseInt(variationSelect.options[variationSelect.selectedIndex].dataset.stock);
                if (quantity > currentStock) {
                    showToast('Cannot add more than available stock.', 'error');
                    return;
                }

                // AJAX request to add_to_cart.php
                fetch('../config/add_to_cart.php', { // Adjusted path for add_to_cart.php
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `variation_id=${selectedVariationId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        updateProductUI(); // Re-fetch stock after adding (if stock is dynamic from DB)
                    } else {
                        showToast(data.message, 'error');
                        // if (data.message.includes("log in")) { // No longer redirect on error, just show toast
                        //     window.location.href = '../front-pages/my_account.php?error=' + encodeURIComponent(data.message);
                        // }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An unexpected error occurred while adding to cart.', 'error');
                });
            });
        }


        // Set initial active thumbnail on page load
        const mainImageElement = document.querySelector('.product-image-main');
        if (mainImageElement) {
            const mainImagePath = mainImageElement.src;
            document.querySelectorAll('.product-thumbnail').forEach(thumb => {
                const thumbSrcRelative = thumb.src.replace(/^https?:\/\/[^\/]+\//i, '/');
                const mainSrcRelative = mainImagePath.replace(/^https?:\/\/[^\/]+\//i, '/');

                if (thumbSrcRelative.endsWith(mainSrcRelative.substring(mainSrcRelative.lastIndexOf('/') + 1)) || thumb.src === mainImagePath) {
                    thumb.closest('.product-thumbnail-wrapper').classList.add('active');
                }
            });
        }

        // Call once on DOMContentLoaded to set initial state
        updateProductUI();
    });
</script>
</body>
</html>