// js/script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('--- DOMContentLoaded: script.js loaded and executing ---');

    const sidebarToggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    const contentArea = document.getElementById('content-area');
    const sidebarLinks = document.querySelectorAll('#sidebar-wrapper .list-group-item');
    const settingsSubmenu = document.getElementById('settingsSubmenu');

    // Function to initialize events specific to the settings_users.php page
    // This function must be called *after* settings_users.php content is loaded into contentArea
    function initializeSettingsUsersPageEvents() {
        console.log('[settings_users_events] Initializing events for Settings Users page.');

        const addUserForm = document.getElementById('addUserForm');
        const addUserModalElement = document.getElementById('addUserModal');

        // Reset the form when the modal is hidden
        if (addUserModalElement) {
            addUserModalElement.addEventListener('hidden.bs.modal', function () {
                if (addUserForm) {
                    addUserForm.reset();
                    console.log('[settings_users_events] Add User form reset.');
                }
            });
        }

        // Client-side password confirmation check for Add User Form
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(event) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                if (password !== confirmPassword) {
                    event.preventDefault(); // Prevent form submission
                    alert('Passwords do not match!');
                    console.warn('[settings_users_events] Passwords do not match on Add User form.');
                }
            });
        }

        // Handle Delete Modal population
        const deleteUserModal = document.getElementById('deleteUserModal');
        if (deleteUserModal) {
            deleteUserModal.addEventListener('show.bs.modal', function (event) {
                // Button that triggered the modal
                const button = event.relatedTarget;
                // Extract info from data-bs-* attributes
                const userId = button.getAttribute('data-user-id');
                const username = button.getAttribute('data-username');

                // --- DEBUGGING: Check what values are being captured from the button ---
                console.log("[settings_users_events] Delete button clicked!");
                console.log("[settings_users_events] Captured User ID:", userId);
                console.log("[settings_users_events] Captured Username:", username);
                // --- END DEBUGGING ---

                const modalUserIdSpan = deleteUserModal.querySelector('#modalUserId');
                const modalUsernameSpan = deleteUserModal.querySelector('#modalUsername');
                const deleteUserIdInput = deleteUserModal.querySelector('#deleteUserId');

                // Set the text content for display
                if (modalUserIdSpan) modalUserIdSpan.textContent = userId;
                if (modalUsernameSpan) modalUsernameSpan.textContent = username;

                // Set the value of the hidden input for form submission
                if (deleteUserIdInput) deleteUserIdInput.value = userId;

                // --- DEBUGGING: Confirm values are set in the modal elements ---
                console.log("[settings_users_events] Modal display ID:", modalUserIdSpan ? modalUserIdSpan.textContent : 'N/A (span not found)');
                console.log("[settings_users_events] Modal display Username:", modalUsernameSpan ? modalUsernameSpan.textContent : 'N/A (span not found)');
                console.log("[settings_users_events] Hidden input value set to:", deleteUserIdInput ? deleteUserIdInput.value : 'N/A (input not found)');
                // --- END DEBUGGING ---
            });
            console.log('[settings_users_events] Delete User Modal event listener attached.');
        } else {
            console.warn('[settings_users_events] Delete User Modal element (#deleteUserModal) not found. Delete functionality might not work.');
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
                    // Remove any old products.js script if it exists to prevent re-execution issues
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
                    };
                    script.onerror = () => {
                        console.error('[loadContent] Failed to load products.js. Check path.');
                    };
                    document.body.appendChild(script);
                } else if (contentId === 'settings_users') { // <--- NEW: Call the function for settings_users
                    console.log('[loadContent] Settings Users page loaded. Initializing its specific event handlers.');
                    initializeSettingsUsersPageEvents(); // Call the specific setup function
                }
                // --- END: Specific script loading/initialization ---

                history.pushState(null, '', '#' + contentId);
                console.log(`[loadContent] URL hash updated to: "${window.location.hash}"`);
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

    // Function to set the active class on sidebar links (unchanged)
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

    // Sidebar Toggle Button Event Listener (unchanged)
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

            // Check if it's a Bootstrap collapse toggle (e.g., for "Settings" parent link)
            if (this.hasAttribute('data-bs-toggle') && this.getAttribute('data-bs-toggle') === 'collapse') {
                console.log('[Link Clicked] Detected as Bootstrap collapse toggle. Letting Bootstrap handle it.');
                return; // Let Bootstrap handle the collapse, do not preventDefault or load content
            }

            event.preventDefault(); // Prevent default link navigation for content loading links

            const url = this.getAttribute('href');
            const contentId = this.getAttribute('data-content-id');

            console.log(`[Link Clicked] Extracted URL: "${url}", Extracted Content ID: "${contentId}"`);

            if (url && contentId) {
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
    let pageToLoadId = 'dashboard'; // Default page ID

    console.log(`[Initial Load] Current URL hash: "${initialHash}"`);

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