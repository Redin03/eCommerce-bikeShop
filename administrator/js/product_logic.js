// administrator/js/product_logic.js

const categoriesAndSubcategories = {
    "Bikes": [
        "Mountain Bikes", "Road Bikes", "Hybrid/Commuter Bikes", "Electric Bikes (E-Bikes)",
        "Gravel Bikes", "Cyclocross Bikes", "Folding Bikes", "Kids' Bikes",
        "BMX Bikes", "Cruiser Bikes", "Fat Bikes", "Triathlon/Time Trial Bikes",
        "Track Bikes", "Touring Bikes", "Recumbent Bikes"
    ],
    "Accessories": [
        "Helmets", "Lights", "Locks", "Bags & Racks", "Bottles & Cages",
        "Computers & GPS", "Fenders", "Kickstands", "Child Seats & Trailers"
    ],
    "Parts & Components": [
        "Drivetrain", "Brakes", "Wheels & Tires", "Frames & Forks",
        "Handlebars, Stems & Seatposts", "Saddles", "Pedals", "Bearings & Headsets"
    ],
    "Apparel": [
        "Jerseys", "Shorts & Bibs", "Jackets & Vests", "Gloves", "Socks",
        "Headwear", "Footwear", "Eyewear"
    ]
};

// Function to set up dynamic subcategory dropdown logic
function setupProductCategorySubcategoryLogic(categorySelectId = 'productCategory', subCategorySelectId = 'productSubCategory') {
    const productCategorySelect = document.getElementById(categorySelectId);
    const productSubCategorySelect = document.getElementById(subCategorySelectId);

    if (productCategorySelect && productSubCategorySelect) {
        // Store the initial value if one is already set (e.g., from editing)
        const initialSubCategoryValue = productSubCategorySelect.value;

        productCategorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;
            productSubCategorySelect.innerHTML = '<option value="">Select Sub-Category</option>'; // Clear existing options
            productSubCategorySelect.disabled = true; // Disable until a category is selected

            if (selectedCategory && categoriesAndSubcategories[selectedCategory]) {
                categoriesAndSubcategories[selectedCategory].forEach(subCategory => {
                    const option = document.createElement('option');
                    option.value = subCategory;
                    option.textContent = subCategory;
                    productSubCategorySelect.appendChild(option);
                });
                productSubCategorySelect.disabled = false; // Enable sub-category dropdown

                // If an initial subcategory was set, try to re-select it
                if (initialSubCategoryValue && Array.from(productSubCategorySelect.options).some(opt => opt.value === initialSubCategoryValue)) {
                    productSubCategorySelect.value = initialSubCategoryValue;
                }
            }
        });

        // Trigger change event on load if a category is already selected (e.g., for edit modal)
        if (productCategorySelect.value) {
            productCategorySelect.dispatchEvent(new Event('change'));
        }
    }
}


/**
 * Renders a single product variation card.
 * @param {Object} variationData - Data for the variation (id, size, color, stock, price).
 * @param {number} index - The index of the variation in the list.
 * @param {string} prefix - Prefix for input names and IDs (e.g., 'variations', 'editVariations').
 * @param {boolean} isEditModal - True if rendering for the edit modal (affects hidden ID field).
 * @returns {HTMLElement} The created variation card element.
 */
function renderVariationCard(variationData, index, prefix, isEditModal = false) {
    const card = document.createElement('div');
    card.classList.add('card', 'mb-2', 'product-variation-card');
    card.innerHTML = `
        <div class="card-body">
            <h7 class="card-title text-muted">Variation #${index + 1}</h7>
            <button type="button" class="btn-close float-end remove-variation-btn" aria-label="Remove variation"></button>
            <div class="row g-2">
                ${isEditModal && variationData.id ? `<input type="hidden" name="${prefix}[${index}][id]" value="${variationData.id}">` : ''}
                <div class="col-md-3">
                    <label for="${prefix}Size_${index}" class="form-label">Size</label>
                    <input type="text" class="form-control" id="${prefix}Size_${index}" name="${prefix}[${index}][size]" placeholder="e.g., Small, 27.5in" value="${variationData.size || ''}" required>
                </div>
                <div class="col-md-3">
                    <label for="${prefix}Color_${index}" class="form-label">Color</label>
                    <input type="text" class="form-control" id="${prefix}Color_${index}" name="${prefix}[${index}][color]" placeholder="e.g., Red, Blue" value="${variationData.color || ''}" required>
                </div>
                <div class="col-md-3">
                    <label for="${prefix}Stock_${index}" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="${prefix}Stock_${index}" name="${prefix}[${index}][stock]" min="0" value="${variationData.stock || 0}" required>
                </div>
                <div class="col-md-3">
                    <label for="${prefix}Price_${index}" class="form-label">Price</label>
                    <input type="number" class="form-control" id="${prefix}Price_${index}" name="${prefix}[${index}][price]" step="0.01" min="0" value="${variationData.price || 0}" required>
                </div>
            </div>
        </div>
    `;
    return card;
}

/**
 * Renders an image preview with a remove button.
 * @param {Object} imageData - Data for the image (id, path).
 * @param {string} containerId - ID of the container where images are displayed.
 * @param {boolean} isNewUpload - True if this is a new image being previewed before upload.
 * @returns {HTMLElement} The created image preview element.
 */
function renderImagePreview(imageData, containerId, isNewUpload = false) {
    const imageContainer = document.createElement('div');
    imageContainer.classList.add('position-relative', 'd-inline-block', 'me-2', 'mb-2');
    imageContainer.style.width = '80px';
    imageContainer.style.height = '80px';
    imageContainer.style.overflow = 'hidden';
    imageContainer.style.borderRadius = '5px';
    imageContainer.style.border = '1px solid #ddd';

    const img = document.createElement('img');
    // Adjust path for display: database stores 'uploads/product_images/...'
    // From products.php (which is loaded into index.php), the base is administrator/
    // so we need to go up one level to project_root/ and then into uploads/
    img.src = `../${imageData.path}`;
    img.alt = 'Product Image';
    img.classList.add('img-fluid');
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';
    imageContainer.appendChild(img);

    // Add hidden input to track existing images that should be kept
    if (!isNewUpload && imageData.id) {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'existing_images_to_keep[]';
        hiddenInput.value = imageData.id; // Store the image ID
        imageContainer.appendChild(hiddenInput);
    }

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.classList.add('btn-close', 'btn-close-white', 'position-absolute', 'top-0', 'end-0', 'm-1');
    removeBtn.style.fontSize = '0.7rem';
    removeBtn.style.backgroundColor = 'rgba(0,0,0,0.5)';
    removeBtn.style.borderRadius = '50%';
    removeBtn.setAttribute('aria-label', 'Remove image');

    removeBtn.addEventListener('click', function() {
        // For existing images, we remove the container and the hidden input
        // For newly selected images (previews), we just remove the container.
        // The actual deletion from DB happens on form submission via existing_images_to_keep[]
        imageContainer.remove();
        // If it's a new upload preview, also remove it from the file input's internal list (complex, usually handled by form data)
        // For simplicity, we assume the user will re-select files if they remove a preview.
    });

    imageContainer.appendChild(removeBtn);
    document.getElementById(containerId).appendChild(imageContainer);
    return imageContainer;
}


/**
 * Sets up the dynamic variation adding/removing logic for a given modal.
 * @param {string} containerId - The ID of the div that holds variation cards (e.g., 'productVariationsContainer', 'editProductVariationsContainer').
 * @param {string} addBtnId - The ID of the button to add new variations (e.g., 'addVariationBtn', 'editAddVariationBtn').
 * @param {string} inputPrefix - The prefix for input names (e.g., 'variations', 'editVariations').
 * @param {Array} initialVariations - Optional array of variation data to pre-populate.
 * @param {boolean} isEditModal - True if this is for the edit modal.
 */
function setupDynamicVariationLogic(containerId, addBtnId, inputPrefix, initialVariations = [], isEditModal = false) {
    const addVariationBtn = document.getElementById(addBtnId);
    const variationsContainer = document.getElementById(containerId);

    if (!variationsContainer || !addVariationBtn) {
        console.warn(`Variation elements for ${containerId} not found. Variation logic not initialized.`);
        return;
    }

    // Clear existing content if any and add initial variations
    variationsContainer.innerHTML = '';
    if (initialVariations.length > 0) {
        initialVariations.forEach((variation, index) => {
            const card = renderVariationCard(variation, index, inputPrefix, isEditModal);
            variationsContainer.appendChild(card);
        });
    } else {
        // Add one default empty variation card if no initial variations
        variationsContainer.appendChild(renderVariationCard({}, 0, inputPrefix, isEditModal));
    }

    // Function to re-index all variation cards and attach/re-attach remove listeners
    function updateAndAttachListeners() {
        const variationCards = variationsContainer.querySelectorAll('.product-variation-card');
        variationCards.forEach((card, index) => {
            // Update title
            const titleElement = card.querySelector('.card-title');
            if (titleElement) {
                titleElement.textContent = `Variation #${index + 1}`;
            }

            // Update input names and IDs
            card.querySelectorAll('input').forEach(input => {
                const oldName = input.getAttribute('name');
                if (oldName) {
                    // Regex to replace the index in variations[index][field]
                    // Handles both 'variations[0][size]' and 'editVariations[0][size]'
                    const newName = oldName.replace(/\[\d+\]/, `[${index}]`);
                    input.setAttribute('name', newName);
                }
                const oldId = input.getAttribute('id');
                if (oldId) {
                    // Regex to replace the index in variationField_index or editVariationField_index
                    const newId = oldId.replace(/_(\d+)$/, `_${index}`);
                    input.setAttribute('id', newId);
                    // Update label's for attribute as well
                    const label = card.querySelector(`label[for="${oldId}"]`);
                    if (label) {
                        label.setAttribute('for', newId);
                    }
                }
            });

            // Re-attach remove listener (important for newly added or re-indexed cards)
            const removeBtn = card.querySelector('.remove-variation-btn');
            if (removeBtn) {
                // Remove existing listener to prevent duplicates
                const oldListener = removeBtn._listener; // Store listener reference
                if (oldListener) {
                    removeBtn.removeEventListener('click', oldListener);
                }

                const newListener = function() {
                    if (variationsContainer.querySelectorAll('.product-variation-card').length > 1) {
                        card.remove();
                        updateAndAttachListeners(); // Re-index and re-attach for remaining
                    } else {
                        // Optionally provide feedback if trying to remove the last card
                        alert('At least one product variation is required.');
                    }
                };
                removeBtn.addEventListener('click', newListener);
                removeBtn._listener = newListener; // Store reference for future removal
            }
        });
    }

    // Add Variation Button Click Handler
    addVariationBtn.addEventListener('click', function() {
        const newCard = renderVariationCard({}, variationsContainer.children.length, inputPrefix, isEditModal);
        variationsContainer.appendChild(newCard);
        updateAndAttachListeners(); // Re-index and attach listeners for all cards
    });

    // Initial call to set up indices and listeners for any pre-existing cards
    updateAndAttachListeners();
}