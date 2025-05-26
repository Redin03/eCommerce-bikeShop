// js/script.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('--- DOMContentLoaded: script.js loaded and executing ---');

    const sidebarToggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    const contentArea = document.getElementById('content-area');
    const sidebarLinks = document.querySelectorAll('#sidebar-wrapper .list-group-item');
    const settingsSubmenu = document.getElementById('settingsSubmenu');

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
                    // Dynamically load products.js
                    const script = document.createElement('script');
                    script.src = 'js/products.js';
                    script.onload = () => {
                        console.log('[loadContent] products.js loaded. Calling initializeProductForm().');
                        // Call the global initialization function defined in products.js
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

            if (this.hasAttribute('data-bs-toggle') && this.getAttribute('data-bs-toggle') === 'collapse') {
                console.log('[Link Clicked] Detected as Bootstrap collapse toggle. Letting Bootstrap handle it.');
                return;
            }

            event.preventDefault();

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
    let pageToLoadId = 'dashboard';

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