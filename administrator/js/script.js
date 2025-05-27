// administrator/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    const contentArea = document.getElementById('content-area'); // The main container for dynamic content
    const sidebarLinks = document.querySelectorAll('#sidebar-wrapper .list-group-item');

    // --- Helper function for displaying messages (reusable) ---
    function displayMessage(targetElement, message, type) {
        if (targetElement) {
            targetElement.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            targetElement.className = 'mt-3'; // Reset or set base class
        }
    }

    // --- Core function to load content via Fetch API ---
    // Modified to accept a URL and optional parameters
    function loadContent(url, contentId, params = {}) {
        let fullUrl = url;
        const urlParams = new URLSearchParams(params);
        if (urlParams.toString()) {
            fullUrl += '?' + urlParams.toString();
        }

        fetch(fullUrl) // Use the full URL with parameters
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const errorData = JSON.parse(text);
                            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                        } catch (e) {
                            console.error('Non-JSON response from API:', text);
                            throw new Error(`HTTP error! status: ${response.status} (Server returned non-JSON data: ${text.substring(0, 100)}...)`);
                        }
                    });
                }
                return response.text();
            })
            .then(html => {
                contentArea.innerHTML = html;
                contentArea.classList.add('content-loaded');
                setTimeout(() => {
                    contentArea.classList.remove('content-loaded');
                }, 300);

                localStorage.setItem('lastActiveContent', contentId);
                // Also update browser history with parameters if present
                const historyStateUrl = new URL(window.location.origin + window.location.pathname);
                historyStateUrl.searchParams.set('tab', contentId);
                for (const key in params) {
                    historyStateUrl.searchParams.set(key, params[key]);
                }
                history.pushState({ contentId: contentId, params: params }, '', historyStateUrl.toString());


                // Initialize Bootstrap Modals within dynamically loaded content
                contentArea.querySelectorAll('[data-bs-toggle="modal"]').forEach(modalTrigger => {
                    const modalId = modalTrigger.getAttribute('data-bs-target');
                    if (modalId) {
                        const modalElement = document.querySelector(modalId);
                        if (modalElement) {
                            if (!bootstrap.Modal.getInstance(modalElement)) {
                                new bootstrap.Modal(modalElement);
                            }
                        }
                    }
                });

                // --- Call setup functions for specific pages after content loads ---
                if (contentId === 'products') {
                    // Setup for Add Product Modal (category/subcategory and variations)
                    if (typeof setupProductCategorySubcategoryLogic === 'function') {
                        setupProductCategorySubcategoryLogic('productCategory', 'productSubCategory'); // For Add Modal
                    }
                    if (typeof setupDynamicVariationLogic === 'function') {
                        setupDynamicVariationLogic('productVariationsContainer', 'addVariationBtn', 'variations', [], false); // For Add Modal
                    }
                }

            })
            .catch(e => {
                console.error('Error loading content:', e);
                displayMessage(contentArea, `Failed to load content for ${contentId}. Please try again.`, 'danger');
            });
    }

    // --- Function to set active link in sidebar (stays the same) ---
    function setActiveLink(contentId) {
        sidebarLinks.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-content-id') === contentId) {
                item.classList.add('active');
                let parentCollapse = item.closest('.submenu.collapse');
                if (parentCollapse) {
                    let bsCollapse = new bootstrap.Collapse(parentCollapse, { toggle: false });
                    bsCollapse.show();
                }
            }
        });
    }

    // --- CENTRALIZED FORM SUBMISSION HANDLER (Event Delegation) ---
    contentArea.addEventListener('submit', function(event) {
        // Handle API forms (forms with data-api-endpoint)
        const apiForm = event.target.closest('form[data-api-endpoint]');
        if (apiForm) {
            event.preventDefault();

            const apiEndpoint = apiForm.getAttribute('data-api-endpoint');
            const formData = new FormData(apiForm); // FormData handles files correctly
            const formMessageElement = apiForm.querySelector('[data-form-message]');

            if (formMessageElement) {
                formMessageElement.innerHTML = '';
                formMessageElement.className = 'mt-3';
            }

            fetch(apiEndpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const errorData = JSON.parse(text);
                            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                        } catch (e) {
                            console.error('Non-JSON response from API:', text);
                            throw new Error(`HTTP error! status: ${response.status} (Server returned non-JSON data: ${text.substring(0, 100)}...)`);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displayMessage(formMessageElement, data.message, 'success');
                    // apiForm.reset(); // Don't reset if we are reloading the page

                    const currentActiveContent = localStorage.getItem('lastActiveContent');
                    // Reload content area if it's one of the affected pages
                    if (currentActiveContent === 'settings_users' || currentActiveContent === 'log_history' || currentActiveContent === 'products') {
                        const modal = bootstrap.Modal.getInstance(apiForm.closest('.modal'));
                        if (modal) modal.hide();

                        setTimeout(() => {
                            // Preserve filter parameters for log_history AND products
                            const currentUrlParams = new URLSearchParams(window.location.search);
                            const filterParams = {};
                            if (currentActiveContent === 'log_history' || currentActiveContent === 'products') {
                                if (currentUrlParams.has('start_date')) {
                                    filterParams.start_date = currentUrlParams.get('start_date');
                                }
                                if (currentUrlParams.has('end_date')) {
                                    filterParams.end_date = currentUrlParams.get('end_date');
                                }
                            }
                            loadContent(`submenu/${currentActiveContent}.php`, currentActiveContent, filterParams);
                        }, 300);
                    }
                } else {
                    displayMessage(formMessageElement, data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                displayMessage(formMessageElement, `An error occurred: ${error.message}`, 'danger');
            });
        }

        // Handle Log Filter Form Submission
        const logFilterForm = event.target.closest('form#logFilterForm');
        if (logFilterForm) {
            event.preventDefault();
            const formData = new FormData(logFilterForm);
            const params = {};
            for (let [key, value] of formData.entries()) {
                if (value) {
                    params[key] = value;
                }
            }
            loadContent('submenu/log_history.php', 'log_history', params);
        }

        // Handle Products Filter Form Submission (NEW)
        const productsFilterForm = event.target.closest('form#productsFilterForm');
        if (productsFilterForm) {
            event.preventDefault();
            const formData = new FormData(productsFilterForm);
            const params = {};
            for (let [key, value] of formData.entries()) {
                if (value) { // Only include parameters that have a value
                    params[key] = value;
                }
            }
            loadContent('submenu/products.php', 'products', params);
        }
    });


    // --- CENTRALIZED MODAL OPEN RESET & DATA POPULATION (Event Delegation) ---
    document.addEventListener('show.bs.modal', function(event) {
        const modalElement = event.target;
        const form = modalElement.querySelector('form[data-api-endpoint]');
        const formMessageElement = form ? form.querySelector('[data-form-message]') : null;

        // Reset form and message area when modal is opened
        if (form) form.reset();
        if (formMessageElement) {
            formMessageElement.innerHTML = '';
            formMessageElement.className = 'mt-3';
        }

        const relatedButton = event.relatedTarget; // The button that triggered the modal
        if (relatedButton) {
            const adminId = relatedButton.dataset.id;
            const adminUsername = relatedButton.dataset.username;
            const productId = relatedButton.dataset.productId; // Get product ID for edit/delete modal

            if (modalElement.id === 'resetPasswordModal') {
                const resetAdminIdInput = modalElement.querySelector('#resetAdminId');
                const resetAdminUsernameSpan = modalElement.querySelector('#resetAdminUsername');
                const resetAdminUsernameConfirmSpan = modalElement.querySelector('#resetAdminUsernameConfirm');

                if (resetAdminIdInput) resetAdminIdInput.value = adminId;
                if (resetAdminUsernameSpan) resetAdminUsernameSpan.textContent = adminUsername;
                if (resetAdminUsernameConfirmSpan) resetAdminUsernameConfirmSpan.textContent = adminUsername;

            } else if (modalElement.id === 'deleteAdminModal') {
                const deleteAdminIdInput = modalElement.querySelector('#deleteAdminId');
                const deleteAdminUsernameSpan = modalElement.querySelector('#deleteAdminUsername');

                if (deleteAdminIdInput) deleteAdminIdInput.value = adminId;
                if (deleteAdminUsernameSpan) deleteAdminUsernameSpan.textContent = adminUsername;
            } else if (modalElement.id === 'addProductModal') {
                // Reset category/subcategory for Add Product Modal
                const productCategorySelect = modalElement.querySelector('#productCategory');
                const productSubCategorySelect = modalElement.querySelector('#productSubCategory');
                if (productCategorySelect) productCategorySelect.value = '';
                if (productSubCategorySelect) {
                    productSubCategorySelect.innerHTML = '<option value="">Select Sub-Category</option>';
                    productSubCategorySelect.disabled = true;
                }
                // Reset variations to only one default row and re-initialize logic
                if (typeof setupDynamicVariationLogic === 'function') {
                    setupDynamicVariationLogic('productVariationsContainer', 'addVariationBtn', 'variations', [], false);
                }
            } else if (modalElement.id === 'editProductModal') {
                if (productId) {
                    fetchAndPopulateEditProductModal(productId);
                }
            } else if (modalElement.id === 'deleteProductModal') {
                const deleteProductIdInput = modalElement.querySelector('#deleteProductId');
                const deleteProductNameSpan = modalElement.querySelector('#deleteProductName');

                if (deleteProductIdInput) deleteProductIdInput.value = productId;
                if (deleteProductNameSpan) deleteProductNameSpan.textContent = productId;
            }
        }
    });

    // --- Function to fetch and populate Edit Product Modal ---
    async function fetchAndPopulateEditProductModal(productId) {
        const modalElement = document.getElementById('editProductModal');
        const formMessageElement = modalElement.querySelector('[data-form-message]');
        const editProductIdInput = modalElement.querySelector('#editProductId');
        const editProductNameInput = modalElement.querySelector('#editProductNameInput');
        const editProductCategorySelect = modalElement.querySelector('#editProductCategory');
        const editProductSubCategorySelect = modalElement.querySelector('#editProductSubCategory');
        const editProductDescriptionInput = modalElement.querySelector('#editProductDescriptionInput');
        const editProductVariationsContainer = modalElement.querySelector('#editProductVariationsContainer');
        const editProductImagesDisplay = modalElement.querySelector('#editProductImagesDisplay');

        // Clear previous content
        if (formMessageElement) {
            formMessageElement.innerHTML = '';
            formMessageElement.className = 'mt-3';
        }
        editProductVariationsContainer.innerHTML = '';
        editProductImagesDisplay.innerHTML = '';

        try {
            const response = await fetch(`api/get_product_details.php?product_id=${productId}`);
            if (!response.ok) {
                const text = await response.text();
                try {
                    const errorData = JSON.parse(text);
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                } catch (e) {
                    console.error('Non-JSON response from API:', text);
                    throw new Error(`HTTP error! status: ${response.status} (Server returned non-JSON data: ${text.substring(0, 100)}...)`);
                }
            }
            const data = await response.json();

            if (data.success && data.data) {
                const product = data.data.product;
                const variations = data.data.variations;
                const images = data.data.images;

                // Populate general product details
                if (editProductIdInput) editProductIdInput.value = product.id;
                if (editProductNameInput) editProductNameInput.value = product.name;
                if (editProductCategorySelect) editProductCategorySelect.value = product.category;
                if (editProductDescriptionInput) editProductDescriptionInput.value = product.description;

                // Set up category/subcategory dropdown for edit modal
                if (typeof setupProductCategorySubcategoryLogic === 'function') {
                    // Temporarily set subcategory value before triggering change, so it can be re-selected
                    if (editProductSubCategorySelect) editProductSubCategorySelect.value = product.subcategory;
                    setupProductCategorySubcategoryLogic('editProductCategory', 'editProductSubCategory');
                }

                // Populate variations
                if (typeof setupDynamicVariationLogic === 'function') {
                    setupDynamicVariationLogic('editProductVariationsContainer', 'editAddVariationBtn', 'variations', variations, true);
                }

                // Populate images
                if (editProductImagesDisplay && typeof renderImagePreview === 'function') {
                    images.forEach(image => {
                        renderImagePreview(image, 'editProductImagesDisplay', false); // false = not a new upload
                    });
                }

            } else {
                displayMessage(formMessageElement, data.message || 'Failed to fetch product details.', 'danger');
            }
        } catch (error) {
            console.error('Error fetching product details:', error);
            displayMessage(formMessageElement, `Error loading product: ${error.message}`, 'danger');
        }
    }


    // --- Reset Filter Button Handler ---
    contentArea.addEventListener('click', function(event) {
        if (event.target.id === 'resetFilterBtn') { // For log_history
            const currentActiveContent = localStorage.getItem('lastActiveContent');
            if (currentActiveContent === 'log_history') {
                loadContent('submenu/log_history.php', 'log_history');
            }
        } else if (event.target.id === 'resetProductsFilterBtn') { // NEW: For products
            const currentActiveContent = localStorage.getItem('lastActiveContent');
            if (currentActiveContent === 'products') {
                loadContent('submenu/products.php', 'products');
            }
        }
    });

    // Sidebar Toggle
    sidebarToggle.addEventListener('click', function() {
        wrapper.classList.toggle('toggled');
    });

    // Handle Sidebar Link Clicks
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            if (this.hasAttribute('data-bs-toggle') && this.getAttribute('data-bs-toggle') === 'collapse') {
                return;
            }
            event.preventDefault();

            const url = this.getAttribute('href');
            const contentId = this.getAttribute('data-content-id');

            if (url && contentId) {
                loadContent(url, contentId); // No parameters initially
                setActiveLink(contentId);
            }

            if (window.innerWidth <= 768 && wrapper.classList.contains('toggled')) {
                wrapper.classList.remove('toggled');
            }
        });
    });

    // Initial content load based on URL or localStorage
    const urlParams = new URLSearchParams(window.location.search);
    const tabFromUrl = urlParams.get('tab');
    const lastActiveContent = localStorage.getItem('lastActiveContent');

    let initialContentIdToLoad = 'dashboard';
    const initialParams = {};

    if (tabFromUrl) {
        initialContentIdToLoad = tabFromUrl;
        // If loading log_history or products from URL, preserve its date parameters
        if (tabFromUrl === 'log_history' || tabFromUrl === 'products') { // MODIFIED
            if (urlParams.has('start_date')) initialParams.start_date = urlParams.get('start_date');
            if (urlParams.has('end_date')) initialParams.end_date = urlParams.get('end_date');
        }
    } else if (lastActiveContent) {
        initialContentIdToLoad = lastActiveContent;
        // If loading log_history or products from localStorage, preserve its date parameters from current URL
        if (lastActiveContent === 'log_history' || lastActiveContent === 'products') { // MODIFIED
            if (urlParams.has('start_date')) initialParams.start_date = urlParams.get('start_date');
            if (urlParams.has('end_date')) initialParams.end_date = urlParams.get('end_date');
        }
    }

    const initialLinkToLoad = document.querySelector(`[data-content-id="${initialContentIdToLoad}"]`);

    if (initialLinkToLoad) {
        loadContent(initialLinkToLoad.getAttribute('href'), initialLinkToLoad.getAttribute('data-content-id'), initialParams);
        setActiveLink(initialLinkToLoad.getAttribute('data-content-id'));
    } else {
        const dashboardLink = document.querySelector('[data-content-id="dashboard"]');
        if (dashboardLink) {
            loadContent(dashboardLink.getAttribute('href'), dashboardLink.getAttribute('data-content-id'));
            setActiveLink(dashboardLink.getAttribute('data-content-id'));
        }
    }
});