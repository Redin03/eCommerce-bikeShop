// js/script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('--- DOMContentLoaded: script.js loaded and executing ---');

    const sidebarToggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    const contentArea = document.getElementById('content-area');
    const sidebarLinks = document.querySelectorAll('#sidebar-wrapper .list-group-item');
    const settingsSubmenu = document.getElementById('settingsSubmenu');

    // Function to initialize events specific to the settings_users.php page
    function initializeSettingsUsersPageEvents() {
        console.log('[settings_users_events] Initializing events for Settings Users page.');

        const addUserForm = document.getElementById('addUserForm');
        const addUserModalElement = document.getElementById('addUserModal');

        if (addUserModalElement) {
            addUserModalElement.addEventListener('hidden.bs.modal', function () {
                if (addUserForm) {
                    addUserForm.reset();
                    console.log('[settings_users_events] Add User form reset.');
                }
            });
        }

        if (addUserForm) {
            addUserForm.addEventListener('submit', function(event) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (password !== confirmPassword) {
                    event.preventDefault();
                    alert('Passwords do not match!');
                    console.warn('[settings_users_events] Passwords do not match on Add User form.');
                }
            });
        }

        const deleteUserModal = document.getElementById('deleteUserModal');
        if (deleteUserModal) {
            deleteUserModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');

                console.log("[settings_users_events] Delete button clicked!");
                console.log("[settings_users_events] Captured User ID:", userId);
                console.log("[settings_users_events] Captured Username:", username);

                const modalUserIdSpan = deleteUserModal.querySelector('#modalUserId');
                const modalUsernameSpan = deleteUserModal.querySelector('#modalUsername');
                const deleteUserIdInput = deleteUserModal.querySelector('#deleteUserId');

                if (modalUserIdSpan) modalUserIdSpan.textContent = userId;
                if (modalUsernameSpan) modalUsernameSpan.textContent = username;
                if (deleteUserIdInput) deleteUserIdInput.value = userId;

                console.log("[settings_users_events] Modal display ID:", modalUserIdSpan ? modalUserIdSpan.textContent : 'N/A (span not found)');
                console.log("[settings_users_events] Modal display Username:", modalUsernameSpan ? modalUsernameSpan.textContent : 'N/A (span not found)');
                console.log("[settings_users_events] Hidden input value set to:", deleteUserIdInput ? deleteUserIdInput.value : 'N/A (input not found)');
            });
            console.log('[settings_users_events] Delete User Modal event listener attached.');
        } else {
            console.warn('[settings_users_events] Delete User Modal element (#deleteUserModal) not found. Delete functionality might not work.');
        }

        // NEW: Reset Password Modal Event Listener
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        if (resetPasswordModal) {
            resetPasswordModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');

                console.log("[settings_users_events] Reset Password button clicked!");
                console.log("[settings_users_events] Captured User ID for reset:", userId);
                console.log("[settings_users_events] Captured Username for reset:", username);

                const modalUserIdSpan = resetPasswordModal.querySelector('#resetModalUserId');
                const modalUsernameSpan = resetPasswordModal.querySelector('#resetModalUsername');
                const hiddenUserIdInput = resetPasswordModal.querySelector('#resetUserId');

                if (modalUserIdSpan) modalUserIdSpan.textContent = userId;
                if (modalUsernameSpan) modalUsernameSpan.textContent = username;
                if (hiddenUserIdInput) hiddenUserIdInput.value = userId;

                console.log("[settings_users_events] Reset Modal display ID:", modalUserIdSpan ? modalUserIdSpan.textContent : 'N/A (span not found)');
                console.log("[settings_users_events] Reset Modal display Username:", modalUsernameSpan ? modalUsernameSpan.textContent : 'N/A (span not found)');
                console.log("[settings_users_events] Reset hidden input value set to:", hiddenUserIdInput ? hiddenUserIdInput.value : 'N/A (input not found)');

                // Clear password fields when modal opens
                resetPasswordModal.querySelector('#new_password').value = '';
                resetPasswordModal.querySelector('#confirm_new_password').value = '';
                console.log('[settings_users_events] Reset Password fields cleared.');
            });
            console.log('[settings_users_events] Reset Password Modal event listener attached.');
        } else {
            console.warn('[settings_users_events] Reset Password Modal element (#resetPasswordModal) not found. Reset functionality might not work.');
        }
    }

    // NEW: Function to initialize events specific to the products.php page
    function initializeProductsPageEvents() {
        console.log('[products_events] Initializing events for Products page.');

        const deleteProductModal = document.getElementById('deleteProductModal');
        if (deleteProductModal) {
            deleteProductModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; // Button that triggered the modal
                const productId = button.getAttribute('data-product-id'); // Get product ID from button
                const productName = button.getAttribute('data-product-name'); // Get product name from button

                console.log("[products_events] Delete Product button clicked!");
                console.log("[products_events] Captured Product ID:", productId);
                console.log("[products_events] Captured Product Name:", productName);

                const modalProductIdSpan = deleteProductModal.querySelector('#modalProductId');
                const modalProductNameSpan = deleteProductModal.querySelector('#modalProductName');
                const deleteProductIdInput = deleteProductModal.querySelector('#deleteProductId');

                if (modalProductIdSpan) modalProductIdSpan.textContent = productId;
                if (modalProductNameSpan) modalProductNameSpan.textContent = productName;
                if (deleteProductIdInput) deleteProductIdInput.value = productId;

                console.log("[products_events] Modal display ID:", modalProductIdSpan ? modalProductIdSpan.textContent : 'N/A (span not found)');
                console.log("[products_events] Modal display Name:", modalProductNameSpan ? modalProductNameSpan.textContent : 'N/A (span not found)');
                console.log("[products_events] Hidden input value set to:", deleteProductIdInput ? deleteProductIdInput.value : 'N/A (input not found)');
            });
            console.log('[products_events] Delete Product Modal event listener attached.');
        } else {
            console.warn('[products_events] Delete Product Modal element (#deleteProductModal) not found. Product delete functionality might not work.');
        }
    }


    // Function to load content into the main area
    function loadContent(url, contentId) {
        console.log(`[loadContent] Attempting to load content for ID: "${contentId}" from URL: "${url}"`);

        if (!contentId || !url) {
            console.error('[loadContent] ERROR: Missing contentId or URL. Aborting content load.');
            contentArea.innerHTML = `<div class="alert alert-danger" role="alert">
                                        Error: Cannot load page. Missing content ID or URL.
                                    </div>`;
            return;
        }

        fetch(url)
            .then(response => {
                console.log(`[loadContent] Fetch response received for ${url}. Status: ${response.status}`);
                if (!response.ok) {
                    const errorMessage = `HTTP error! Status: ${response.status} for URL: ${url}`;
                    if (response.status === 404) {
                        throw new Error(`Content file not found: ${url}. Please check path and file existence.`);
                    }
                    throw new Error(errorMessage);
                }
                return response.text();
            })
            .then(html => {
                contentArea.innerHTML = html;
                contentArea.classList.add('content-loaded');
                setTimeout(() => {
                    contentArea.classList.remove('content-loaded');
                }, 300);

                // --- IMPORTANT: Load and initialize specific scripts based on contentId ---
                if (contentId === 'products') {
                    console.log('[loadContent] Products page loaded. Loading products.js and initializing its logic.');
                    // Remove existing products.js script if it was loaded before
                    const oldScript = document.querySelector('script[src="js/products.js"]');
                    if (oldScript) oldScript.remove();

                    const script = document.createElement('script');
                    script.src = 'js/products.js';
                    script.onload = () => {
                        console.log('[loadContent] products.js loaded. Calling initializeProductForm().');
                        if (typeof window.initializeProductForm === 'function') {
                            window.initializeProductForm();
                        } else {
                            console.error('[loadContent] initializeProductForm not found after loading products.js. Check products.js.');
                        }
                        // Call the products page specific event initializer after products.js is loaded
                        initializeProductsPageEvents();
                    };
                    script.onerror = () => {
                        console.error('[loadContent] Failed to load products.js. Check path.');
                    };
                    document.body.appendChild(script);
                } else if (contentId === 'settings_users') {
                    console.log('[loadContent] Settings Users page loaded. Initializing its specific event handlers.');
                    initializeSettingsUsersPageEvents();
                }
            })
            .catch(e => {
                console.error(`[loadContent] FETCH ERROR for ${url}:`, e);
                contentArea.innerHTML = `<div class="alert alert-danger" role="alert">
                                            Failed to load content for <strong>${contentId}</strong>.
                                            <br>Error: ${e.message}.
                                            <br>Please check your server, file paths, and browser's Network tab.
                                        </div>`;
            });
    }

    // Function to set the active class on sidebar links
    function setActiveLink(contentId) {
        console.log(`[setActiveLink] Setting active link for ID: "${contentId}"`);
        sidebarLinks.forEach(item => item.classList.remove('active'));
        const activeLink = document.querySelector(`#sidebar-wrapper .list-group-item[data-content-id="${contentId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
            if (settingsSubmenu && typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                const bsCollapse = new bootstrap.Collapse(settingsSubmenu, { toggle: false });
                if (settingsSubmenu.contains(activeLink)) {
                    if (!settingsSubmenu.classList.contains('show')) {
                        bsCollapse.show();
                        console.log('[setActiveLink] Opening Settings submenu.');
                    }
                } else {
                    if (settingsSubmenu.classList.contains('show')) {
                        bsCollapse.hide();
                        console.log('[setActiveLink] Closing Settings submenu.');
                    }
                }
            }
        } else {
            console.warn(`[setActiveLink] WARNING: No sidebar link found with data-content-id="${contentId}".`);
        }
    }

    // Sidebar Toggle Button Event Listener
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            console.log('[sidebarToggle] Toggle button clicked.');
            wrapper.classList.toggle('toggled');
        });
    } else {
        console.error('ERROR: Sidebar toggle button (#sidebarToggle) not found.');
    }

    // Event Listeners for all sidebar links
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            console.log('--- Link Clicked Event ---');
            console.log('[Link Clicked] Element:', this);

            if (this.hasAttribute('data-bs-toggle') && this.getAttribute('data-bs-toggle') === 'collapse') {
                console.log('[Link Clicked] Detected as Bootstrap collapse toggle. Letting Bootstrap handle it.');
                return;
            }

            event.preventDefault(); // Prevent default link navigation

            const url = this.getAttribute('href');
            const contentId = this.getAttribute('data-content-id');

            console.log(`[Link Clicked] Extracted URL: "${url}", Extracted Content ID: "${contentId}"`);

            if (url && contentId) {
                // URL Cleaning Logic Here
                const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                const newCleanUrl = baseUrl + '#' + contentId;
                history.replaceState(null, '', newCleanUrl);
                console.log(`[Link Clicked] URL cleaned to: "${newCleanUrl}"`);

                setActiveLink(contentId);
                loadContent(url, contentId);
            } else {
                console.error('[Link Clicked] ERROR: Clicked link is missing "href" or "data-content-id". Link:', this);
            }

            if (window.innerWidth <= 768 && wrapper.classList.contains('toggled')) {
                console.log('[Link Clicked] Small screen detected, closing sidebar.');
                wrapper.classList.remove('toggled');
            }
            console.log('--- Link Clicked Event End ---');
        });
    });

    // Initial Page Load Logic
    console.log('--- Initial Page Load Logic Starting ---');
    const initialHash = window.location.hash.substring(1);
    let pageToLoadId = 'dashboard';

    console.log(`[Initial Load] Current URL hash: "${initialHash}"`);

    // Initial URL Cleaning
    const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    if (window.location.search) { // If there are query parameters
        console.log('[Initial Load] Cleaning URL: Detected existing query parameters.');
        history.replaceState(null, '', baseUrl + window.location.hash);
    }

    if (initialHash) {
        const linkForHash = document.querySelector(`#sidebar-wrapper .list-group-item[data-content-id="${initialHash}"]`);
        console.log(`[Initial Load] Looking for link with data-content-id="${initialHash}". Found:`, linkForHash);

        if (linkForHash) {
            pageToLoadId = initialHash;
            console.log(`[Initial Load] Matching link found for hash: "${initialHash}". Will load this page.`);
        } else {
            console.warn(`[Initial Load] WARNING: URL hash "${initialHash}" does not match any sidebar link's data-content-id. Defaulting to 'dashboard'.`);
        }
    }

    const initialLinkElement = document.querySelector(`#sidebar-wrapper .list-group-item[data-content-id="${pageToLoadId}"]`);
    console.log(`[Initial Load] Determined page ID to load: "${pageToLoadId}". Corresponding link element:`, initialLinkElement);

    if (initialLinkElement) {
        setActiveLink(pageToLoadId);
        loadContent(initialLinkElement.getAttribute('href'), pageToLoadId);
        console.log(`[Initial Load] Successfully initiated load for: "${pageToLoadId}".`);
    } else {
        console.error(`[Initial Load] FATAL ERROR: Could not find the initial link element for data-content-id="${pageToLoadId}". This indicates a problem with your HTML structure or the default 'dashboard' link.`);
        contentArea.innerHTML = `<div class="alert alert-danger" role="alert">
                                    Critical Error: Initial page content could not be determined or loaded.
                                    Please check your HTML structure and console for errors.
                                </div>`;
    }
    console.log('--- Initial Page Load Logic End ---');
});