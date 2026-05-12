/**
 * Shared Navbar & Sidebar Component
 * Include this script in every page to get the consistent header + sidebar.
 * All pages live under Frontend/pages/<module>/<page>.html, so relative
 * paths use the "../<module>/<page>.html" pattern.
 */
(function () {
    // ── Header HTML ──
    const headerHTML = `
    <header class="header" id="mainHeader">
        <!-- Left: hamburger + logo -->
        <div class="header-left">
            <button class="menu-btn" onclick="toggleSidebar()">☰</button>
            <a href="../../index.html" style="text-decoration: none; color: inherit;"><h2>EcoSwap</h2></a>
        </div>

        <!-- Center: main nav links -->
        <nav class="header-nav">
            <a href="../../pages/listings/marketplace.html">Marketplace</a>
            <a href="../../pages/swap/swap-offers.html">Swap</a>
            <a href="../../pages/orders/cart.html">Cart</a>
            <a href="../../pages/community/notification.html">Notification</a>
        </nav>

        <!-- Right: profile + logout -->
        <div class="header-right">
            <a href="../../pages/user/profile.html">Profile</a>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </header>`;

    // ── Sidebar Overlay + Sidebar HTML ──
    const sidebarHTML = `
    <!-- ── Sidebar Overlay ── -->
    <div class="sidebar-overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- ── Sidebar ── -->
    <div class="sidebar" id="sidebar">
        <div class="logo2"><b>Eco System</b></div>

        <div class="module">
            <button onclick="togglePages('navAuth', this)"><b>Auth</b></button>
            <div class="pages" id="navAuth">
                <a href="../../pages/auth/login.html">Login</a>
                <a href="../../pages/auth/register.html">Register</a>
            </div>
        </div>

        <div class="module">
            <button onclick="togglePages('navCommunity', this)"><b>Community</b></button>
            <div class="pages" id="navCommunity">
                <a href="../../pages/community/notification.html">Notification</a>
                <a href="../../pages/community/reviews.html">Reviews Section</a>
            </div>
        </div>

        <div class="module">
            <button onclick="togglePages('navListings', this)"><b>Listings</b></button>
            <div class="pages" id="navListings">
                <a href="../../pages/listings/marketplace.html">Marketplace</a>
                <a href="../../pages/listings/create-listing.html">Create Listing</a>
                <a href="../../pages/listings/inventory.html">Inventory</a>
                <a href="../../pages/listings/upcycle-log.html">Upcycle Log</a>
            </div>
        </div>

        <div class="module">
            <button onclick="togglePages('navOrders', this)"><b>Orders</b></button>
            <div class="pages" id="navOrders">
                <a href="../../pages/orders/cart.html">Cart</a>
                <a href="../../pages/orders/dispute.html">Report Dispute</a>
            </div>
        </div>

        <div class="module">
            <button onclick="togglePages('navSwap', this)"><b>Swap</b></button>
            <div class="pages" id="navSwap">
                <a href="../../pages/swap/swap-offers.html">Swap Offers</a>
            </div>
        </div>

        <div class="module">
            <button onclick="togglePages('navUser', this)"><b>User</b></button>
            <div class="pages" id="navUser">
                <a href="../../pages/user/profile.html">User Profile</a>
                <a href="../../pages/user/eco-impact.html">EcoImpact Dashboard</a>
                <a href="../../pages/user/trust-score.html">Trust Score</a>
            </div>
        </div>
    </div>`;

    // ── Inject into the page ──
    // Insert header at the very beginning of <body>
    document.body.insertAdjacentHTML('afterbegin', headerHTML);

    // Insert sidebar + overlay right before </body>
    document.body.insertAdjacentHTML('beforeend', sidebarHTML);

    // ── Highlight the active nav link ──
    const currentPath = window.location.pathname;
    document.querySelectorAll('#mainHeader .header-nav a, #mainHeader .header-right a').forEach(link => {
        if (currentPath.includes(link.getAttribute('href')?.replace('..', ''))) {
            link.classList.add('active');
        }
    });
})();

// ── Shared functions (global scope) ──

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('visible');
}

function togglePages(id, btn) {
    const pages = document.getElementById(id);
    if (pages) pages.classList.toggle('open');
    if (btn) btn.classList.toggle('open');
}


