// administrator/js/products.js

// Hardcoded product data (unchanged)
const productsData = [
  {
    "name": "Bikes",
    "key": "BIK",
    "subcategories": [
      {"name": "Mountain Bikes", "key": "MTB"},
      {"name": "Road Bikes", "key": "RDB"},
      {"name": "Hybrid/Commuter Bikes", "key": "HYB"},
      {"name": "Electric Bikes (E-Bikes)", "key": "EBI"},
      {"name": "Gravel Bikes", "key": "GRV"},
      {"name": "Cyclocross Bikes", "key": "CYC"},
      {"name": "Folding Bikes", "key": "FLD"},
      {"name": "Kids' Bikes", "key": "KID"},
      {"name": "BMX Bikes", "key": "BMX"},
      {"name": "Cruiser Bikes", "key": "CRS"},
      {"name": "Fat Bikes", "key": "FAT"},
      {"name": "Triathlon/Time Trial Bikes", "key": "TRI"},
      {"name": "Track Bikes", "key": "TRK"},
      {"name": "Touring Bikes", "key": "TNG"},
      {"name": "Recumbent Bikes", "key": "RCB"}
    ]
  },
  {
    "name": "Apparel",
    "key": "APP",
    "subcategories": [
      {"name": "Jerseys", "key": "JRS"},
      {"name": "Shorts & Bibs", "key": "SHB"},
      {"name": "Jackets & Vests", "key": "JKV"},
      {"name": "Gloves", "key": "GLV"},
      {"name": "Socks", "key": "SCK"},
      {"name": "Headwear", "key": "HDW"},
      {"name": "Footwear", "key": "FTW"},
      {"name": "Eyewear", "key": "EYW"}
    ]
  },
  {
    "name": "Components",
    "key": "COM",
    "subcategories": [
      {"name": "Drivetrain", "key": "DRV"},
      {"name": "Brakes", "key": "BRK"},
      {"name": "Wheels & Tires", "key": "WNT"},
      {"name": "Frames & Forks", "key": "FRF"},
      {"name": "Handlebars, Stems & Seatposts", "key": "HSS"},
      {"name": "Saddles", "key": "SDL"},
      {"name": "Pedals", "key": "PDL"},
      {"name": "Bearings & Headsets", "key": "BHS"}
    ]
  },
  {
    "name": "Accessories",
    "key": "ACC",
    "subcategories": [
      {"name": "Helmets", "key": "HLM"},
      {"name": "Lights", "key": "LGT"},
      {"name": "Locks", "key": "LCK"},
      {"name": "Bags & Racks", "key": "BGR"},
      {"name": "Bottles & Cages", "key": "BTC"},
      {"name": "Computers & GPS", "key": "CPG"},
      {"name": "Fenders", "key": "FND"},
      {"name": "Kickstands", "key": "KST"},
      {"name": "Child Seats & Trailers", "key": "CST"}
    ]
  },
  {
    "name": "Maintenance",
    "key": "MNT",
    "subcategories": [
      {"name": "Tools", "key": "TLS"},
      {"name": "Lubricants & Cleaners", "key": "LNC"},
      {"name": "Pumps & Inflators", "key": "PNI"},
      {"name": "Repair Kits & Patches", "key": "RKP"},
      {"name": "Storage Solutions", "key": "STS"}
    ]
  }
];

// Global arrays to manage variations and images for ADD and EDIT modals
let newProductVariations = []; // For Add Product Modal
let newProductImages = new DataTransfer(); // For Add Product Modal images

let editProductCurrentVariations = []; // For Edit Product Modal
let editProductNewFiles = new DataTransfer(); // For Edit Product Modal (for new additions)
let existingImageIdsToDelete = new Set(); // To track images marked for deletion


// --- Helper Functions for Categories/Subcategories ---
function populateCategoryDropdown(selectElement, selectedCategoryKey = null) {
    if (!selectElement) {
        console.error("Category select element not found:", selectElement);
        return;
    }
    selectElement.innerHTML = '<option selected disabled value="">Select Category</option>';
    productsData.forEach(category => {
        const option = document.createElement('option');
        option.value = category.key;
        option.textContent = category.name;
        if (selectedCategoryKey && category.key === selectedCategoryKey) {
            option.selected = true;
        }
        selectElement.appendChild(option);
    });
}

function populateSubcategoryDropdown(selectElement, categoryKey, selectedSubcategoryKey = null) {
    if (!selectElement) {
        console.error("Subcategory select element not found:", selectElement);
        return;
    }
    selectElement.innerHTML = '<option selected disabled value="">Select Subcategory</option>';
    selectElement.disabled = true;

    const selectedCategory = productsData.find(cat => cat.key === categoryKey);
    if (selectedCategory && selectedCategory.subcategories) {
        selectedCategory.subcategories.forEach(subcat => {
            const option = document.createElement('option');
            option.value = subcat.key;
            option.textContent = subcat.name;
            if (selectedSubcategoryKey && subcat.key === selectedSubcategoryKey) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });
        selectElement.disabled = false;
    }
}


// --- Functions for Image Management (Shared Logic) ---

// This function now specifically handles adding new files,
// and doesn't implicitly handle "replacement" of existing images.
function handleNewImageUpload(event, imagePreviewContainerId, dataTransferObject) {
    const files = event.target.files;
    const imagePreviewContainer = document.getElementById(imagePreviewContainerId);

    if (!imagePreviewContainer) {
        console.error("Image preview container not found for ID:", imagePreviewContainerId);
        return;
    }

    if (files.length > 0) {
        for (let i = 0; i < files.length; i++) {
            dataTransferObject.items.add(files[i]);
        }
    }
    // Re-render ALL images: existing (if edit modal) + newly added files
    if (imagePreviewContainerId === 'editImagePreviewContainer') {
        // We need the original existing images data from fetch, so pass it along
        // For simplicity, we'll refetch it or store it globally if more complex scenarios arise
        // For now, assume renderImagePreviews will use `product.images` passed from fetchAndPopulateEditModal
        renderImagePreviews(imagePreviewContainer, dataTransferObject, currentProductData.images);
    } else {
        renderImagePreviews(imagePreviewContainer, dataTransferObject);
    }
    updateFileInput(event.target.id, dataTransferObject);
}


// Global variable to store the fetched product data
let currentProductData = {};

function renderImagePreviews(containerElement, newFilesDataTransfer, existingImages = []) {
    containerElement.innerHTML = ''; // Clear previous previews

    // 1. Render existing images (for edit modal only)
    existingImages.forEach(img => {
        // Only render if not marked for deletion
        if (!existingImageIdsToDelete.has(img.id)) {
            const imgDiv = createImagePreviewElement(img.image_path, img.id, containerElement.id);
            containerElement.appendChild(imgDiv);
        }
    });

    // 2. Render newly selected images (from DataTransfer object)
    for (let i = 0; i < newFilesDataTransfer.files.length; i++) {
        const file = newFilesDataTransfer.files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                // Pass a unique identifier for new files (e.g., 'new_idx_' + i)
                const imgDiv = createImagePreviewElement(e.target.result, 'new_idx_' + i, containerElement.id, i);
                containerElement.appendChild(imgDiv);
            };
            reader.readAsDataURL(file);
        }
    }
}


function createImagePreviewElement(src, id, containerId, fileIndex = null) {
    const imgDiv = document.createElement('div');
    imgDiv.classList.add('position-relative', 'me-2', 'mb-2');
    imgDiv.style.width = '100px';
    imgDiv.style.height = '100px';
    imgDiv.style.overflow = 'hidden';
    imgDiv.style.border = '1px solid #ddd';

    const img = document.createElement('img');
    img.src = src.startsWith('uploads/') ? `../../${src}` : src; // Adjust path for existing images
    img.classList.add('img-fluid', 'rounded');
    img.style.objectFit = 'cover';
    img.style.width = '100%';
    img.style.height = '100%';

    const removeBtn = document.createElement('button');
    removeBtn.classList.add('btn', 'btn-danger', 'btn-sm', 'position-absolute', 'top-0', 'end-0', 'translate-middle', 'rounded-circle', 'p-0');
    removeBtn.style.width = '24px';
    removeBtn.style.height = '24px';
    removeBtn.innerHTML = '<i class="bi bi-x"></i>';
    removeBtn.setAttribute('type', 'button'); // Prevent form submission

    removeBtn.addEventListener('click', () => {
        if (typeof id === 'number' && id !== null) { // This is an existing image (has a numeric ID)
            existingImageIdsToDelete.add(id); // Mark for deletion
            imgDiv.remove(); // Remove from UI
            // Update the hidden input for imagesToDelete on the form
            updateImagesToDeleteInput();
            console.log(`Marked image ID ${id} for deletion. Current IDs to delete:`, Array.from(existingImageIdsToDelete));
        } else if (typeof id === 'string' && id.startsWith('new_idx_') && fileIndex !== null) { // This is a newly added image (from DataTransfer)
            const dt = (containerId === 'imagePreviewContainer') ? newProductImages : editProductNewFiles;
            removeFileFromDataTransfer(fileIndex, dt);
            imgDiv.remove(); // Remove from UI
            console.log(`Removed new image at internal index ${fileIndex}.`);
            // Re-render the correct DataTransfer files after removal
            if (containerId === 'editImagePreviewContainer') {
                 renderImagePreviews(document.getElementById(containerId), editProductNewFiles, currentProductData.images);
            } else {
                 renderImagePreviews(document.getElementById(containerId), newProductImages);
            }
        }
    });

    imgDiv.appendChild(img);
    imgDiv.appendChild(removeBtn);
    return imgDiv;
}

function removeFileFromDataTransfer(indexToRemove, dataTransferObject) {
    const newDt = new DataTransfer();
    for (let i = 0; i < dataTransferObject.files.length; i++) {
        if (i !== indexToRemove) {
            newDt.items.add(dataTransferObject.files[i]);
        }
    }
    // Update the global DataTransfer object reference
    if (dataTransferObject === newProductImages) {
        newProductImages = newDt;
    } else if (dataTransferObject === editProductNewFiles) {
        editProductNewFiles = newDt;
    }
}

function updateFileInput(inputId, dataTransferObject) {
    const fileInput = document.getElementById(inputId);
    if (fileInput) {
        fileInput.files = dataTransferObject.files;
    }
}

// New function to update the hidden input field for imagesToDelete
function updateImagesToDeleteInput() {
    const form = document.getElementById('editProductForm');
    let hiddenInput = form.querySelector('input[name="imagesToDelete"]');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'imagesToDelete';
        form.appendChild(hiddenInput);
    }
    hiddenInput.value = Array.from(existingImageIdsToDelete).join(',');
}


// --- Functions for Variation Management (Shared Logic) ---
function addVariation(colorInputId, sizeInputId, quantityInputId, priceInputId, variationsArray, renderFunction) {
    const colorInput = document.getElementById(colorInputId);
    const sizeInput = document.getElementById(sizeInputId);
    const quantityInput = document.getElementById(quantityInputId);
    const priceInput = document.getElementById(priceInputId);

    const color = colorInput.value.trim();
    const size = sizeInput.value.trim();
    const quantity = parseInt(quantityInput.value, 10);
    const price = parseFloat(priceInput.value);

    if (!color || !size || isNaN(quantity) || quantity < 0 || isNaN(price) || price < 0) {
        alert('Please enter valid color, size, a non-negative quantity, and a valid non-negative price for the variation.');
        return;
    }

    // Check for duplicates using consistent key names (color_name, size_name)
    const isDuplicate = variationsArray.some(v => v.color_name === color && v.size_name === size);
    if (isDuplicate) {
        alert(`Variation "${color} - ${size}" already added.`);
        return;
    }

    // Store with consistent key names (color_name, size_name)
    variationsArray.push({ color_name: color, size_name: size, quantity, price });
    console.log('Added variation:', { color_name: color, size_name: size, quantity, price }, 'All variations:', variationsArray);

    // Clear inputs
    colorInput.value = '';
    sizeInput.value = '';
    quantityInput.value = '0';
    priceInput.value = '';

    renderFunction();
}

function renderVariations(variationsArray, containerId, noVariationsMessageId) {
    const variationsContainer = document.getElementById(containerId);
    const noVariationsMessage = document.getElementById(noVariationsMessageId);
    variationsContainer.innerHTML = ''; // Clear previous render

    if (variationsArray.length === 0) {
        if (noVariationsMessage) noVariationsMessage.style.display = 'block';
        return;
    } else {
        if (noVariationsMessage) noVariationsMessage.style.display = 'none';
    }

    variationsArray.forEach((variation, index) => {
        const variationDiv = document.createElement('div');
        variationDiv.classList.add('d-flex', 'align-items-center', 'justify-content-between', 'border-bottom', 'py-2');
        variationDiv.innerHTML = `
            <span class="me-auto text-primary">
                <strong>${variation.color_name}</strong> - ${variation.size_name}
                <span class="text-success ms-2">(₱${parseFloat(variation.price).toFixed(2)})</span>
            </span>
            <span class="text-muted me-3">Qty: ${variation.quantity}</span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn" data-index="${index}" data-container-id="${containerId}"><i class="bi bi-x-lg"></i></button>
            <input type="hidden" name="variations[${index}][color]" value="${variation.color_name}">
            <input type="hidden" name="variations[${index}][size]" value="${variation.size_name}">
            <input type="hidden" name="variations[${index}][quantity]" value="${variation.quantity}">
            <input type="hidden" name="variations[${index}][price]" value="${variation.price}">
        `;
        variationsContainer.appendChild(variationDiv);
    });

    // Attach event listeners for new remove buttons
    variationsContainer.querySelectorAll('.remove-variation-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            const indexToRemove = parseInt(event.target.closest('button').dataset.index, 10);
            const targetContainerId = event.target.closest('button').dataset.containerId;
            if (targetContainerId === 'productVariationsContainer') {
                removeVariation(indexToRemove, newProductVariations, renderNewProductVariations);
            } else if (targetContainerId === 'editProductVariationsContainer') {
                removeVariation(indexToRemove, editProductCurrentVariations, renderEditProductVariations);
            }
        });
    });
}

function removeVariation(indexToRemove, variationsArray, renderFunction) {
    variationsArray.splice(indexToRemove, 1);
    renderFunction();
}

function handleAddVariationEnter(e, addFunction) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addFunction();
    }
}


// --- Functions for Add Product Modal ---
function renderNewProductVariations() {
    renderVariations(newProductVariations, 'productVariationsContainer', 'noVariationsMessage');
}

function addProductAddVariation() {
    addVariation('newProductColorInput', 'newProductSizeInput', 'newProductQuantityInput', 'newProductVariationPriceInput', newProductVariations, renderNewProductVariations);
}


// --- Functions for Edit Product Modal ---
let currentEditProductId = null; // Store the ID of the product currently being edited

function renderEditProductVariations() {
    renderVariations(editProductCurrentVariations, 'editProductVariationsContainer', 'editNoVariationsMessage');
}

function editProductAddVariation() {
    addVariation('editProductColorInput', 'editProductSizeInput', 'editProductQuantityInput', 'editProductVariationPriceInput', editProductCurrentVariations, renderEditProductVariations);
}

async function fetchAndPopulateEditModal(productId) {
    console.log(`[products.js] Fetching data for product ID: ${productId}`);
    try {
        const response = await fetch(`api/fetch_product_details.php?productId=${productId}`);
        const result = await response.json();

        if (result.success) {
            const product = result.data;
            currentProductData = product; // Store fetched product data globally
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editProductName').value = product.name;
            document.getElementById('editProductDescription').value = product.description;

            // Populate Category and Subcategory
            populateCategoryDropdown(document.getElementById('editProductCategory'), product.category_key);
            // This will re-enable and populate subcategory
            populateSubcategoryDropdown(document.getElementById('editProductSubcategory'), product.category_key, product.subcategory_key);

            // Populate Variations
            editProductCurrentVariations = product.variations || [];
            renderEditProductVariations();

            // Populate Images
            // Reset DataTransfer for new files for this edit session
            editProductNewFiles = new DataTransfer();
            existingImageIdsToDelete = new Set(); // Reset deleted images tracking
            // Render existing images AND an empty DataTransfer for new uploads
            renderImagePreviews(document.getElementById('editImagePreviewContainer'), editProductNewFiles, product.images);

            // Important: Ensure the file input reflects the empty DataTransfer initially
            updateFileInput('editProductImages', editProductNewFiles);


        } else {
            alert('Error fetching product details: ' + result.message);
            console.error('Error fetching product details:', result.message);
        }
    } catch (error) {
        alert('Network or server error fetching product details.');
        console.error('Fetch error:', error);
    }
}


// --- Global Initialization ---
window.initializeProductForm = function() {
    console.log('[products.js] Initializing product form logic.');

    // Add Product Modal Elements
    const addProductModal = document.getElementById('addProductModal');
    const newProductCategorySelect = document.getElementById('newProductCategory');
    const newProductSubcategorySelect = document.getElementById('newProductSubcategory');
    const newProductImagesInput = document.getElementById('newProductImages');
    const addVariationBtn = document.getElementById('addVariationBtn');
    const newProductColorInput = document.getElementById('newProductColorInput');
    const newProductSizeInput = document.getElementById('newProductSizeInput');
    const newProductQuantityInput = document.getElementById('newProductQuantityInput');
    const newProductVariationPriceInput = document.getElementById('newProductVariationPriceInput');

    // Edit Product Modal Elements
    const editProductModal = document.getElementById('editProductModal');
    const editProductCategorySelect = document.getElementById('editProductCategory');
    const editProductSubcategorySelect = document.getElementById('editProductSubcategory');
    const editProductImagesInput = document.getElementById('editProductImages');
    const editAddVariationBtn = document.getElementById('editAddVariationBtn');
    const editProductColorInput = document.getElementById('editProductColorInput');
    const editProductSizeInput = document.getElementById('editProductSizeInput');
    const editProductQuantityInput = document.getElementById('editProductQuantityInput');
    const editProductVariationPriceInput = document.getElementById('editProductVariationPriceInput');


    // --- Add Product Modal Listeners ---
    if (addProductModal) {
        // Populate categories initially for Add Product Modal
        populateCategoryDropdown(newProductCategorySelect);

        newProductCategorySelect.addEventListener('change', () => {
            populateSubcategoryDropdown(newProductSubcategorySelect, newProductCategorySelect.value);
        });

        // Use handleNewImageUpload for the add product modal
        newProductImagesInput.addEventListener('change', (e) => handleNewImageUpload(e, 'imagePreviewContainer', newProductImages));

        addVariationBtn.addEventListener('click', addProductAddVariation);
        newProductColorInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, addProductAddVariation));
        newProductSizeInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, addProductAddVariation));
        newProductQuantityInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, addProductAddVariation));
        newProductVariationPriceInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, addProductAddVariation));

        addProductModal.addEventListener('shown.bs.modal', function () {
            console.log('[products.js] Add Product Modal shown. Resetting form elements.');
            document.getElementById('addProductForm').reset();
            document.getElementById('newProductSubcategory').innerHTML = '<option selected disabled value="">Select Subcategory</option>';
            document.getElementById('newProductSubcategory').disabled = true;
            document.getElementById('imagePreviewContainer').innerHTML = '';
            newProductImages = new DataTransfer();
            document.getElementById('newProductImages').files = newProductImages.files;
            newProductVariations = [];
            renderNewProductVariations();
            document.getElementById('newProductQuantityInput').value = '0';
            document.getElementById('newProductVariationPriceInput').value = '';
            populateCategoryDropdown(newProductCategorySelect); // Repopulate categories
        });
    }

    // --- Edit Product Modal Listeners ---
    if (editProductModal) {
        // Attach listener to all "Edit" buttons
        document.querySelectorAll('.edit-product-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentEditProductId = this.dataset.productId;
                console.log(`Edit button clicked for product ID: ${currentEditProductId}`);
            });
        });

        editProductModal.addEventListener('shown.bs.modal', function () {
            console.log('[products.js] Edit Product Modal shown. Fetching data...');
            if (currentEditProductId) {
                fetchAndPopulateEditModal(currentEditProductId);
            } else {
                console.error("No product ID found for edit modal.");
                alert("Error: Could not determine product to edit.");
                // Optionally close modal or disable form
            }
        });

        editProductModal.addEventListener('hidden.bs.modal', function() {
            console.log('[products.js] Edit Product Modal hidden. Cleaning up.');
            document.getElementById('editProductForm').reset();
            document.getElementById('editProductVariationsContainer').innerHTML = '';
            document.getElementById('editImagePreviewContainer').innerHTML = '';
            editProductCurrentVariations = [];
            editProductNewFiles = new DataTransfer(); // Reset for next edit session
            existingImageIdsToDelete = new Set(); // Reset for next edit session
            document.getElementById('editProductImages').files = editProductNewFiles.files;
            currentEditProductId = null; // Clear the stored product ID
            // Clear the hidden imagesToDelete input
            const imagesToDeleteInput = document.getElementById('editProductForm').querySelector('input[name="imagesToDelete"]');
            if (imagesToDeleteInput) {
                imagesToDeleteInput.value = '';
            }
        });


        editProductCategorySelect.addEventListener('change', () => {
            populateSubcategoryDropdown(editProductSubcategorySelect, editProductCategorySelect.value);
        });

        // Use handleNewImageUpload for the edit product modal as well
        editProductImagesInput.addEventListener('change', (e) => handleNewImageUpload(e, 'editImagePreviewContainer', editProductNewFiles));

        editAddVariationBtn.addEventListener('click', editProductAddVariation);
        editProductColorInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, editProductAddVariation));
        editProductSizeInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, editProductAddVariation));
        editProductQuantityInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, editProductAddVariation));
        editProductVariationPriceInput.addEventListener('keypress', (e) => handleAddVariationEnter(e, editProductAddVariation));
    }


    console.log('[products.js] Product form logic initialized.');
};

// Ensure the initializeProductForm function is called when the DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Check if any product modal (add or edit) exists before calling initializeProductForm
    if (document.getElementById('addProductModal') || document.getElementById('editProductModal')) {
        window.initializeProductForm();
    } else {
        console.warn('No product modals found. Skipping product form initialization.');
    }
});