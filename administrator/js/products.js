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
      {"name": "Cycling Shorts & Bibs", "key": "SHT"},
      {"name": "Jackets & Vests", "key": "JAC"},
      {"name": "Gloves", "key": "GLV"},
      {"name": "Socks", "key": "SCK"},
      {"name": "Helmets", "key": "HLM"},
      {"name": "Cycling Shoes", "key": "SHS"},
      {"name": "Base Layers", "key": "BSE"},
      {"name": "Arm/Leg Warmers", "key": "WRM"},
      {"name": "Headwear & Bandanas", "key": "HDW"},
      {"name": "Eyewear (Glasses/Goggles)", "key": "EYE"},
      {"name": "Casual Cycling Apparel", "key": "CSP"},
      {"name": "Protective Gear (Pads, Guards)", "key": "PRT"},
      {"name": "Rain Gear", "key": "RNG"}
    ]
  },
  {
    "name": "Accessories",
    "key": "ACC",
    "subcategories": [
      {"name": "Lights (Front & Rear)", "key": "LGT"},
      {"name": "Locks", "key": "LCK"},
      {"name": "Pumps (Floor & Frame)", "key": "PMP"},
      {"name": "Bags & Panniers (Frame, Seat, Handlebar)", "key": "BAG"},
      {"name": "Water Bottles & Cages", "key": "WTR"},
      {"name": "Bike Computers & GPS", "key": "CMP"},
      {"name": "Fenders", "key": "FND"},
      {"name": "Racks (Front & Rear)", "key": "RCK"},
      {"name": "Trainers & Rollers", "key": "TRN"},
      {"name": "Stands & Storage", "key": "STD"},
      {"name": "Tools & Maintenance Kits", "key": "TOL"},
      {"name": "Repair Kits & Patches", "key": "RPK"},
      {"name": "Bike Covers", "key": "CVR"},
      {"name": "Bells & Horns", "key": "BLL"},
      {"name": "Mirrors", "key": "MRR"},
      {"name": "Child Seats", "key": "CHD"},
      {"name": "Trailers", "key": "TRL"},
      {"name": "Action Cameras & Mounts", "key": "CAM"}
    ]
  },
  {
    "name": "Parts & Components",
    "key": "PAR",
    "subcategories": [
      {"name": "Drivetrain (Chains, Cassettes, Derailleurs, Cranks)", "key": "DRV"},
      {"name": "Brakes (Calipers, Levers, Rotors, Pads)", "key": "BRK"},
      {"name": "Wheels & Tires (Rims, Hubs, Spokes, Tubes, Tubeless)", "key": "WHL"},
      {"name": "Handlebars & Stems", "key": "HND"},
      {"name": "Saddles & Seatposts", "key": "SAD"},
      {"name": "Pedals (Flat, Clipless, Toe Clip)", "key": "PED"},
      {"name": "Forks & Shocks", "key": "FOR"},
      {"name": "Frames", "key": "FRM"},
      {"name": "Headsets", "key": "HDS"},
      {"name": "Bottom Brackets", "key": "BBK"},
      {"name": "Grips & Bar Tape", "key": "GRP"},
      {"name": "Cables & Housing", "key": "CBL"},
      {"name": "Bearings", "key": "BRG"},
      {"name": "Small Parts & Hardware", "key": "SMP"},
      {"name": "Electronic Shifting Components", "key": "ELS"}
    ]
  }
];

// Array to store selected variations (color, size, quantity)
let selectedVariations = [];
let currentImages = new DataTransfer(); // To manage files in the input field

// Function to populate categories dropdown
function populateCategories() {
    console.log('[products.js] Populating categories.');
    const newProductCategorySelect = document.getElementById('newProductCategory');
    if (!newProductCategorySelect) {
        console.warn('[products.js] #newProductCategory select element not found. Not on products page?');
        return;
    }

    newProductCategorySelect.innerHTML = '<option selected disabled value="">Select Category</option>';

    if (productsData && productsData.length > 0) {
        productsData.forEach(category => {
            if (category.key && category.name) {
                const option = document.createElement('option');
                option.value = category.key; // Use key for value
                option.textContent = category.name;
                newProductCategorySelect.appendChild(option);
            } else {
                console.warn('[products.js] Skipping malformed category entry:', category);
            }
        });
        console.log('[products.js] Categories dropdown populated.');
    } else {
        console.warn('[products.js] productsData is empty or not an array. No categories to populate.');
        newProductCategorySelect.innerHTML = '<option selected disabled value="">No categories found</option>';
    }
}

// Function to populate subcategories based on selected category
function populateSubcategories(selectedCategoryKey) {
    console.log(`[products.js] Populating subcategories for category key: ${selectedCategoryKey}`);
    const newProductSubcategorySelect = document.getElementById('newProductSubcategory');
    if (!newProductSubcategorySelect) {
        console.warn('[products.js] #newProductSubcategory select element not found. Not on products page?');
        return;
    }

    newProductSubcategorySelect.innerHTML = '<option selected disabled value="">Select Subcategory</option>';
    newProductSubcategorySelect.disabled = true;

    const selectedCategory = productsData.find(cat => cat.key === selectedCategoryKey);

    if (selectedCategory && selectedCategory.subcategories && Array.isArray(selectedCategory.subcategories) && selectedCategory.subcategories.length > 0) {
        selectedCategory.subcategories.forEach(subcategory => {
            if (subcategory.key && subcategory.name) {
                const option = document.createElement('option');
                option.value = subcategory.key; // Use key for value
                option.textContent = subcategory.name;
                newProductSubcategorySelect.appendChild(option);
            } else {
                 console.warn('[products.js] Skipping malformed subcategory entry:', subcategory);
            }
        });
        newProductSubcategorySelect.disabled = false;
        console.log(`[products.js] Subcategories populated for category: ${selectedCategoryKey}`);
    } else {
        newProductSubcategorySelect.innerHTML = '<option selected disabled value="">No Subcategories</option>';
        console.warn(`[products.js] No subcategories found or selected category invalid/empty for: ${selectedCategoryKey}`);
    }
}

// Function to handle image preview
function handleImageUpload(event) {
    console.log('[products.js] Image input changed.');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    imagePreviewContainer.innerHTML = ''; // Clear previous previews
    currentImages = new DataTransfer(); // Reset DataTransfer object for new selection

    const files = event.target.files;
    if (files.length > 0) {
        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                currentImages.items.add(file); // Add valid image file to DataTransfer
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imgDiv = document.createElement('div');
                    imgDiv.classList.add('position-relative', 'me-2', 'mb-2'); // For positioning remove button

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('img-thumbnail');
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.objectFit = 'cover';

                    const removeBtn = document.createElement('button');
                    removeBtn.classList.add('btn', 'btn-danger', 'btn-sm', 'position-absolute', 'top-0', 'start-100', 'translate-middle', 'rounded-circle', 'p-0', 'd-flex', 'align-items-center', 'justify-content-center');
                    removeBtn.style.width = '24px';
                    removeBtn.style.height = '24px';
                    removeBtn.innerHTML = '<i class="bi bi-x"></i>';
                    removeBtn.onclick = () => {
                        imgDiv.remove(); // Remove the image preview from DOM
                        updateFileInput(file); // Call helper to remove the file from the DataTransfer object
                    };

                    imgDiv.appendChild(img);
                    imgDiv.appendChild(removeBtn);
                    imagePreviewContainer.appendChild(imgDiv);
                };
                reader.readAsDataURL(file);
            } else {
                console.warn('[products.js] Non-image file selected:', file.name);
            }
        });
    }
    // Assign the updated DataTransfer files back to the input
    // This is important to ensure the input's .files property is correct for submission
    document.getElementById('newProductImages').files = currentImages.files;
}

// Helper function to remove a file from the DataTransfer object
function updateFileInput(removedFile) {
    const newItems = new DataTransfer();
    Array.from(currentImages.files).forEach(file => {
        if (file !== removedFile) {
            newItems.items.add(file);
        }
    });
    currentImages = newItems; // Update the global DataTransfer object
    document.getElementById('newProductImages').files = currentImages.files;
    console.log('[products.js] File input updated after removal. Current files:', currentImages.files);
}

// Function to add a product variation (color, size, quantity)
function addVariation() {
    const colorInput = document.getElementById('newProductColorInput');
    const sizeInput = document.getElementById('newProductSizeInput');
    const quantityInput = document.getElementById('newProductQuantityInput');
    const variationsContainer = document.getElementById('productVariationsContainer');
    const noVariationsMessage = document.getElementById('noVariationsMessage');

    const color = colorInput.value.trim();
    const size = sizeInput.value.trim();
    const quantity = parseInt(quantityInput.value, 10);

    if (!color || !size || isNaN(quantity) || quantity < 0) {
        alert('Please enter valid color, size, and a non-negative quantity for the variation.');
        return;
    }

    // Check for duplicates
    const isDuplicate = selectedVariations.some(v => v.color === color && v.size === size);
    if (isDuplicate) {
        alert(`Variation "${color} - ${size}" already added.`);
        return;
    }

    selectedVariations.push({ color, size, quantity });
    console.log('Added variation:', { color, size, quantity }, 'All variations:', selectedVariations);

    // Clear inputs
    colorInput.value = '';
    sizeInput.value = '';
    quantityInput.value = '0';

    renderVariations();
    if (noVariationsMessage) {
        noVariationsMessage.style.display = 'none'; // Hide "No variations added yet." message
    }
}

// Function to render/re-render the variations in the container
function renderVariations() {
    const variationsContainer = document.getElementById('productVariationsContainer');
    variationsContainer.innerHTML = ''; // Clear previous render

    if (selectedVariations.length === 0) {
        variationsContainer.innerHTML = '<p class="text-muted text-center" id="noVariationsMessage">No variations added yet.</p>';
        return;
    }

    selectedVariations.forEach((variation, index) => {
        const variationDiv = document.createElement('div');
        variationDiv.classList.add('d-flex', 'align-items-center', 'justify-content-between', 'border-bottom', 'py-2');
        variationDiv.innerHTML = `
            <span class="me-auto text-primary"><strong>${variation.color}</strong> - ${variation.size}</span>
            <span class="text-muted me-3">Qty: ${variation.quantity}</span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-variation-btn" data-index="${index}"><i class="bi bi-x-lg"></i></button>
            <input type="hidden" name="variations[${index}][color]" value="${variation.color}">
            <input type="hidden" name="variations[${index}][size]" value="${variation.size}">
            <input type="hidden" name="variations[${index}][quantity]" value="${variation.quantity}">
        `;
        variationsContainer.appendChild(variationDiv);
    });

    // Add event listeners for new remove buttons
    variationsContainer.querySelectorAll('.remove-variation-btn').forEach(button => {
        button.addEventListener('click', (event) => {
            const indexToRemove = parseInt(event.target.closest('button').dataset.index, 10);
            removeVariation(indexToRemove);
        });
    });
}

// Function to remove a variation
function removeVariation(index) {
    if (index > -1 && index < selectedVariations.length) {
        selectedVariations.splice(index, 1);
        console.log('Removed variation. All variations:', selectedVariations);
        renderVariations(); // Re-render the list after removal
    }
}


// Global function to initialize product form logic
window.initializeProductForm = function() {
    console.log('[products.js] Initializing product form logic.');
    const addProductModal = document.getElementById('addProductModal');
    if (!addProductModal) {
        console.warn('[products.js] Products modal not found. Skipping product form JS initialization.');
        return;
    }

    const newProductCategorySelect = document.getElementById('newProductCategory');
    const newProductSubcategorySelect = document.getElementById('newProductSubcategory');
    const newProductImagesInput = document.getElementById('newProductImages');
    const addProductForm = document.getElementById('addProductForm'); // Get the form reference

    // Variation input elements and button
    const addVariationBtn = document.getElementById('addVariationBtn');
    const newProductColorInput = document.getElementById('newProductColorInput');
    const newProductSizeInput = document.getElementById('newProductSizeInput');
    const newProductQuantityInput = document.getElementById('newProductQuantityInput');

    // Populate categories initially
    populateCategories();

    // Attach change listener for category
    if (newProductCategorySelect) {
        newProductCategorySelect.removeEventListener('change', handleCategoryChange);
        newProductCategorySelect.addEventListener('change', handleCategoryChange);
    }

    // Attach change listener for image input
    if (newProductImagesInput) {
        newProductImagesInput.removeEventListener('change', handleImageUpload);
        newProductImagesInput.addEventListener('change', handleImageUpload);
    }

    // Attach click listener for Add Variation button
    if (addVariationBtn) {
        addVariationBtn.removeEventListener('click', addVariation);
        addVariationBtn.addEventListener('click', addVariation);

        // Allow pressing Enter in any variation input to add variation
        newProductColorInput.removeEventListener('keypress', handleAddVariationEnter);
        newProductColorInput.addEventListener('keypress', handleAddVariationEnter);
        newProductSizeInput.removeEventListener('keypress', handleAddVariationEnter);
        newProductSizeInput.addEventListener('keypress', handleAddVariationEnter);
        newProductQuantityInput.removeEventListener('keypress', handleAddVariationEnter);
        newProductQuantityInput.addEventListener('keypress', handleAddVariationEnter);
    }

    // IMPORTANT: Remove previous submit listener as form action will handle it
    if (addProductForm) {
        // We are no longer using fetch, so we remove the submit listener
        // The form will naturally submit to the action URL.
        // If you had any client-side validation logic you wanted to run *before* submission,
        // you would put it here and potentially call event.preventDefault() and then submit manually if validation passes.
        // For now, we rely on browser's 'required' and PHP's server-side validation.
        addProductForm.removeEventListener('submit', (e) => { /* old handler */ });
    }


    // Helper function for category change, scoped to allow removal
    function handleCategoryChange() {
        const selectedCategoryKey = this.value;
        console.log(`[products.js] Category dropdown changed. Selected: ${selectedCategoryKey}`);
        populateSubcategories(selectedCategoryKey);
    }

    // Helper function to add a variation on Enter key
    function handleAddVariationEnter(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addVariation();
        }
    }


    // Reset dynamic elements and arrays when modal is shown or initialized
    addProductModal.addEventListener('shown.bs.modal', function () {
        console.log('[products.js] Add Product Modal shown. Resetting form elements.');
        // Reset dynamic elements here
        document.getElementById('addProductForm').reset();
        document.getElementById('newProductSubcategory').innerHTML = '<option selected disabled value="">Select Subcategory</option>';
        document.getElementById('newProductSubcategory').disabled = true;
        document.getElementById('imagePreviewContainer').innerHTML = '';
        currentImages = new DataTransfer(); // Reset image DataTransfer
        document.getElementById('newProductImages').files = currentImages.files; // Clear file input visually

        selectedVariations = [];   // Reset variations array
        renderVariations(); // Clear variations display
        document.getElementById('noVariationsMessage').style.display = 'block'; // Show "No variations added yet." message
    });

    console.log('[products.js] Product form logic initialized.');
};