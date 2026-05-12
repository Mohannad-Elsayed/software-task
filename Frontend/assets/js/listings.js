const API_BASE = 'http://localhost:8000/Backend/index.php?route=/api';

// ============ MARKETPLACE ============
// ============ MARKETPLACE ============
let allProducts = [];

async function loadMarketplace() {
    const grid = document.getElementById("productGrid");
    if (!grid) return;
    try {
        const res = await fetch(`${API_BASE}/listings`);
        let products = await res.json();
        if (products && products.data) products = products.data;
        if (!Array.isArray(products)) { grid.innerHTML = '<p style="text-align:center;padding:40px;color:#94a3b8;">No listings available.</p>'; return; }

        // Filter: only show items from other users
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (user.user_id) {
            products = products.filter(p => parseInt(p.user_id) !== parseInt(user.user_id));
        }

        allProducts = products;
        renderProducts(products);
    } catch (err) {
        console.error("Failed to load listings:", err);
        grid.innerHTML = '<p style="text-align:center;padding:40px;color:#ef4444;">Failed to load listings from server.</p>';
    }
}

function renderProducts(productsToRender) {
    const grid = document.getElementById("productGrid");
    if (!grid) return;
    if (!productsToRender.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:50px;">
            <i class="ti ti-search" style="font-size:48px;color:#ccc;"></i>
            <p>No items found matching your search.</p></div>`;
        return;
    }
    grid.innerHTML = productsToRender.map(p => {
        const isSale = p.listing_type === 'sale';
        const isSwap = p.listing_type === 'swap';

        return `
            <a href="listing-details.html?id=${p.listing_id}" class="product-card" style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <h3 style="font-size:1.2rem; font-weight:700; margin:0; color:var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${p.title || 'Untitled Listing'}
                        </h3>
                        ${isSwap ? '<span class="swap-badge" style="margin:0; flex-shrink:0;">Swap</span>' : ''}
                        ${isSale ? '<span class="status-badge status-active" style="margin:0; flex-shrink:0; background:#dcfce7; color:#166534; font-size:11px;">For Sale</span>' : ''}
                    </div>
                    <div style="display:flex; gap: 16px; font-size:13px; color:var(--muted); margin-top: 8px;">
                        <span><i class="ti ti-tag"></i> ${p.category || 'N/A'}</span>
                        <span><i class="ti ti-star"></i> ${p.condition_status || '—'}</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 24px; flex-shrink: 0;">
                    <div style="font-weight:700; font-size:1.4rem; color:var(--primary); text-align: right;">
                        $${p.price || 0}
                    </div>
                    <div style="display: flex; gap: 10px;">
                        ${isSwap ? `
                            <button class="btn-swap" style="margin:0; padding:10px 20px; width: auto; font-size:14px;" onclick="event.preventDefault();event.stopPropagation();handleSwapRequest(${p.listing_id})">
                                <i class="ti ti-arrows-exchange"></i> Propose Swap
                            </button>
                        ` : ''}
                        ${isSale ? `
                            <button class="btn-buy" style="margin:0; padding:10px 20px; width: auto; font-size:14px; background:#111; color:white; border:none; border-radius:6px; font-weight:600; cursor:pointer;" onclick="event.preventDefault();event.stopPropagation();handleBuyNow(${p.listing_id})">
                                <i class="ti ti-shopping-cart"></i> Buy Now
                            </button>
                        ` : ''}
                        <button class="btn-report" style="margin:0; padding:10px; width: auto; font-size:14px; background:none; color:#ef4444; border:1px solid #ef4444; border-radius:6px; cursor:pointer;" onclick="event.preventDefault();event.stopPropagation();location.href='report.html?listing_id=${p.listing_id}'" title="Report this listing">
                            <i class="ti ti-report"></i>
                        </button>
                    </div>
                </div>
            </a>
        `;
    }).join('');

    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.style.opacity = 1; e.target.style.transform = "translateY(0)"; } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.product-card').forEach(c => {
        c.style.opacity = 0; c.style.transform = "translateY(20px)"; c.style.transition = "all 0.5s ease-out";
        observer.observe(c);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    // 1. Initial Loaders
    loadMarketplace();

    // 2. Search Functionality
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = allProducts.filter(p =>
                (p.title || '').toLowerCase().includes(term) ||
                (p.category || '').toLowerCase().includes(term)
            );
            renderProducts(filtered);
        });
    }

    // 3. Global Action Handlers
    window.handleSwapRequest = (id) => {
        window.location.href = `listing-details.html?id=${id}`;
    };
});

window.handleBuyNow = async (listingId) => {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.user_id) {
        Swal.fire({ title: 'Login Required', text: 'Please log in to buy items.', icon: 'warning', confirmButtonColor: '#111' });
        return;
    }

    try {
        const res = await fetch(`${API_BASE}/orders`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                buyer_id: user.user_id,
                items: [{ listing_id: parseInt(listingId), quantity: 1 }],
                shipping_street: '',
                shipping_city: ''
            })
        });
        const data = await res.json();

        if (data.error) {
            Swal.fire({ title: 'Error', text: data.error, icon: 'error' });
            return;
        }

        Swal.fire({
            title: 'EcoSwap',
            text: 'Order created successfully!',
            icon: 'success',
            confirmButtonColor: '#111'
        }).then(() => {
            window.location.href = 'marketplace.html';
        });
    } catch (err) {
        console.error("Purchase failed:", err);
        Swal.fire({ title: 'Error', text: 'Failed to initiate purchase.', icon: 'error' });
    }
};


// ============ UPCYCLE LOG ============
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("logContainer");
    if (!container) return;

    async function loadUpcycleLogs() {
        try {
            const res = await fetch(`${API_BASE}/listings`);
            let listings = await res.json();
            if (listings && listings.data) listings = listings.data;
            if (!Array.isArray(listings)) return;
            // listing_type === 'upcycle' marks upcycled items
            const upcycled = listings.filter(l => l.listing_type === 'upcycle');
            if (!upcycled.length) {
                container.innerHTML = '<p style="text-align:center;padding:40px;color:#94a3b8;">No upcycle transformations yet.</p>';
                return;
            }
            container.innerHTML = upcycled.map(log => `
                <div class="upcycle-card">
                    <div class="content-body">
                        <div class="meta-info">
                            <h2 class="project-title">${log.title || 'Upcycle Project'}</h2>
                        </div>
                        <p style="color:#475569;font-size:14px;">${log.description || ''}</p>
                        <div class="process-tags">
                            <span class="tag"># ${log.category || 'Upcycled'}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (err) {
            console.error("Failed to load upcycle logs:", err);
            container.innerHTML = '<p style="text-align:center;padding:40px;color:#ef4444;">Failed to load data.</p>';
        }
    }

    loadUpcycleLogs();

    window.openNewLog = () => {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        Swal.fire({
            title: 'Log Upcycle Transformation',
            html: `
                <input id="swal-listing-id" class="swal2-input" placeholder="Listing ID to upcycle" type="number">
                <textarea id="swal-steps" class="swal2-textarea" placeholder="Steps taken..."></textarea>
                <input id="swal-materials" class="swal2-input" placeholder="Materials used">
            `,
            confirmButtonText: 'Submit',
            confirmButtonColor: '#09b1ba',
            showCancelButton: true,
            preConfirm: () => {
                const listingId = document.getElementById('swal-listing-id').value;
                if (!listingId) { Swal.showValidationMessage('Listing ID is required'); return false; }
                return {
                    user_id: user.user_id,
                    steps: document.getElementById('swal-steps').value,
                    materials_used: document.getElementById('swal-materials').value,
                    listingId
                };
            }
        }).then(async result => {
            if (!result.isConfirmed) return;
            const { listingId, ...payload } = result.value;
            try {
                const res = await fetch(`${API_BASE}/listings/${listingId}/upcycle`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                Swal.fire({ title: 'EcoSwap', text: data.message || 'Transformation logged!', icon: 'success', confirmButtonColor: '#09b1ba' });
                loadUpcycleLogs();
            } catch {
                Swal.fire({ title: 'Error', text: 'Failed to log transformation.', icon: 'error' });
            }
        });
    };
});


// ============ LISTING DETAILS ============
document.addEventListener("DOMContentLoaded", () => {
    const titleEl = document.getElementById('listingTitle');
    if (!titleEl && !document.getElementById('listingPrice')) return; // Check for a valid details page element

    const urlParams = new URLSearchParams(window.location.search);
    const listingId = urlParams.get('id');

    async function loadListingDetails() {
        if (!listingId) return;
        try {
            const res = await fetch(`${API_BASE}/listings/${listingId}`);
            let item = await res.json();
            if (item && item.data) item = item.data;
            if (!item || item.status === 'error') return;

            const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };

            set('listingTitle', item.title);
            set('listingDescription', item.description);
            set('specCategory', item.category);
            set('specCondition', item.condition_status);
            set('specCategoryDetail', item.category);
            set('specConditionDetail', item.condition_status);
            set('specListingType', item.listing_type);
            set('specStatus', item.status);

            // Handle button visibility
            const buyBtn = document.getElementById('buyBtn');
            const swapBtn = document.getElementById('swapBtn');
            const reportBtn = document.getElementById('reportBtn');
            const ecoBenefit = document.querySelector('.eco-benefit');
            const user = JSON.parse(localStorage.getItem('user') || '{}');

            // Show report button only if it's not the user's listing
            if (reportBtn) {
                if (user.user_id && parseInt(item.user_id) !== parseInt(user.user_id)) {
                    reportBtn.style.display = 'flex';
                    reportBtn.onclick = () => { window.location.href = `report.html?listing_id=${listingId}`; };
                } else {
                    reportBtn.style.display = 'none';
                }
            }

            if (item.listing_type === 'sale') {
                if (buyBtn) buyBtn.style.display = 'flex';
                if (swapBtn) swapBtn.style.display = 'none';
                if (ecoBenefit) ecoBenefit.style.display = 'none'; // Only show eco benefit for swaps
            } else if (item.listing_type === 'swap') {
                if (buyBtn) buyBtn.style.display = 'none';
                if (swapBtn) swapBtn.style.display = 'flex';
                if (ecoBenefit) ecoBenefit.style.display = 'block';
            } else {
                if (buyBtn) buyBtn.style.display = 'none';
                if (swapBtn) swapBtn.style.display = 'none';
            }

            const priceEl = document.getElementById('listingPrice');
            if (priceEl) priceEl.textContent = `$${item.price || 0}`;

            if (item.material_id) {
                try {
                    const mRes = await fetch(`${API_BASE}/materials`);
                    const mData = await mRes.json();
                    if (mData.status === 'success' && mData.data) {
                        const mat = mData.data.find(m => m.material_id == item.material_id);
                        set('specMaterial', mat ? mat.name : item.material_id);
                    }
                } catch (e) { }
            } else {
                set('specMaterial', 'N/A');
            }

            if (item.user_id) {
                try {
                    const uRes = await fetch(`${API_BASE}/user/profile&user_id=${item.user_id}`);
                    const uData = await uRes.json();
                    if (uData.success && uData.user) {
                        const sellerNameEl = document.getElementById('sellerName');
                        if (sellerNameEl) sellerNameEl.textContent = uData.user.username;

                        const sellerRoleEl = document.getElementById('sellerRole');
                        if (sellerRoleEl) sellerRoleEl.textContent = `Trust Score: ${uData.user.trust_score || 0}`;
                    } else {
                        set('sellerName', 'Unknown Seller');
                        set('sellerRole', '');
                    }
                } catch (e) {
                    console.error("Failed to load seller info:", e);
                    set('sellerName', 'Unknown Seller');
                    set('sellerRole', '');
                }
            } else {
                set('sellerName', 'Unknown Seller');
                set('sellerRole', '');
            }
        } catch (err) {
            console.error("Failed to load listing details:", err);
        }
    }

    loadListingDetails();

    const swapBtn = document.getElementById('swapBtn');
    if (swapBtn) {
        swapBtn.addEventListener('click', () => { openSwapModal(); });
    }

    const buyBtn = document.getElementById('buyBtn');
    if (buyBtn) {
        buyBtn.addEventListener('click', () => {
            if (listingId) {
                window.handleBuyNow(listingId);
            }
        });
    }

    // Load similar items
    (async function loadSimilarItems() {
        const similarGrid = document.getElementById('similarItemsGrid');
        if (!similarGrid) return;
        try {
            const res = await fetch(`${API_BASE}/listings`);
            let items = await res.json();
            if (items && items.data) items = items.data;
            if (!Array.isArray(items) || !items.length) { similarGrid.innerHTML = '<p style="color:#94a3b8;">No similar items found.</p>'; return; }
            const others = items.filter(i => String(i.listing_id) !== String(listingId)).slice(0, 4);
            if (!others.length) { similarGrid.innerHTML = '<p style="color:#94a3b8;">No similar items found.</p>'; return; }
            similarGrid.innerHTML = others.map(item => `
                <a href="listing-details.html?id=${item.listing_id}" style="text-decoration:none;color:inherit;">
                    <div style="background:white;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;padding:12px;">
                        <div style="font-weight:800;">$${item.price || 0}</div>
                        <div style="font-size:13px;color:var(--muted);">${item.title || ''}</div>
                    </div>
                </a>
            `).join('');
        } catch { similarGrid.innerHTML = '<p style="color:#94a3b8;">Could not load similar items.</p>'; }
    })();
});


// ============ SWAP MODAL (listing-details) ============
let selectedItemId = null;

function openSwapModal() {
    const modal = document.getElementById('swapModal');
    const listContainer = document.getElementById('myItemsList');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    if (!user.user_id) {
        Swal.fire({ title: 'Login Required', text: 'Please log in to propose a swap.', icon: 'warning' });
        return;
    }

    if (!modal || !listContainer) return;

    listContainer.innerHTML = '<p style="text-align:center;padding:20px;color:#94a3b8;">Loading your items...</p>';
    modal.style.display = 'flex';

    fetch(`${API_BASE}/listings`).then(r => r.json()).then(res => {
        let items = res.data || res;
        if (!Array.isArray(items)) items = [];

        // Filter: only show CURRENT USER'S items that are ACTIVE
        const myItems = items.filter(i => parseInt(i.user_id) === parseInt(user.user_id) && i.status === 'active');

        if (myItems.length === 0) {
            listContainer.innerHTML = '<p style="text-align:center;padding:20px;color:#94a3b8;">You have no active items to swap.</p>';
            return;
        }
        listContainer.innerHTML = myItems.map(item => `
            <div class="selectable-item" onclick="selectItem(this, ${item.listing_id})">
                <div class="item-details">
                    <h4>${item.title || ''}</h4>
                    <p>${item.condition_status || '—'}</p>
                </div>
            </div>
        `).join('');
    }).catch(() => {
        listContainer.innerHTML = '<p style="text-align:center;padding:20px;color:#ef4444;">Failed to load items.</p>';
    });
}

window.selectItem = function (element, id) {
    document.querySelectorAll('.selectable-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedItemId = id;
    const confirmBtn = document.getElementById('confirmSwapBtn');
    if (confirmBtn) confirmBtn.disabled = false;
};

window.closeSwapModal = function () {
    const modal = document.getElementById('swapModal');
    if (modal) modal.style.display = 'none';
};

document.addEventListener("DOMContentLoaded", () => {
    const confirmSwapBtn = document.getElementById('confirmSwapBtn');
    if (confirmSwapBtn) {
        confirmSwapBtn.addEventListener('click', async () => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            const urlParams = new URLSearchParams(window.location.search);
            const requestedListingId = parseInt(urlParams.get('id') || '0');

            if (!user.user_id) {
                Swal.fire({ title: 'Login Required', text: 'Please log in to propose a swap.', icon: 'warning', confirmButtonColor: '#111' });
                return;
            }

            // We need the partner (seller) user_id — read it from the page or fetch listing
            let partnerId = null;
            try {
                const res = await fetch(`${API_BASE}/listings/${requestedListingId}`);
                const data = await res.json();
                const listing = data.data || data;
                partnerId = listing.user_id;
            } catch { /* proceed without partner_id */ }

            try {
                const res = await fetch(`${API_BASE}/swap-requests`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        initiator_id: user.user_id,
                        partner_id: partnerId,
                        requested_listing_id: requestedListingId,
                        offered_listing_id: selectedItemId
                    })
                });
                const data = await res.json();
                Swal.fire({ title: 'EcoSwap', text: data.message || 'Swap proposal sent!', icon: 'success', confirmButtonColor: '#111' });
                closeSwapModal();
            } catch {
                Swal.fire({ title: 'EcoSwap', text: 'Swap proposal sent!', icon: 'success', confirmButtonColor: '#111' });
                closeSwapModal();
            }
        });
    }
});


// ============ INVENTORY ============
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("inventoryBody");
    if (!container) return;

    let allItems = [];

    let myItems = [];

    async function loadInventory() {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!user.user_id) {
            container.innerHTML = '<p style="padding:20px;text-align:center;">Please log in.</p>';
            return;
        }

        try {
            // Fetch ALL listings for this user specifically (including swapped/paid)
            const res = await fetch(`${API_BASE}/listings/user&user_id=${user.user_id}`);
            const result = await res.json();
            myItems = result.data || [];

            renderInventory(myItems);
            updateStats(myItems);
        } catch (err) {
            container.innerHTML = '<p style="padding:20px;text-align:center;color:red;">Error loading items.</p>';
        }
    }

    function renderInventory(items) {
        if (!items || items.length === 0) {
            container.innerHTML = '<p style="padding:20px;text-align:center;color:#94a3b8;">No items found.</p>';
            return;
        }
        container.innerHTML = items.map(item => `
            <div class="item-row" id="row-${item.listing_id}">
                <div>
                    <div style="font-weight:700;">${item.title}</div>
                    <div style="font-size:12px;color:#64748b;">${item.category} • ID: ${item.listing_id}</div>
                </div>
                <div style="font-size:14px;">${item.condition_status}</div>
                <div>
                    <span class="status-badge ${item.status === 'active' ? 'status-active' : 'status-swapped'}">
                        ${item.status === 'active' ? '● Live' : item.status}
                    </span>
                </div>
                <div class="action-btns">
                    <button class="btn-icon" onclick="window.location.href='listing-details.html?id=${item.listing_id}'"><i class="ti ti-eye"></i></button>
                    <button class="btn-icon" onclick="window.location.href='edit-listing.html?id=${item.listing_id}'"><i class="ti ti-edit"></i></button>
                    <button class="btn-icon btn-delete" onclick="deleteItem(${item.listing_id})"><i class="ti ti-trash"></i></button>
                </div>
            </div>
        `).join('');
    }

    function updateStats(items) {
        if (document.getElementById('statActive')) document.getElementById('statActive').textContent = items.filter(i => i.status === 'active').length;
        if (document.getElementById('statSwapped')) document.getElementById('statSwapped').textContent = items.filter(i => i.status === 'swapped' || i.status === 'paid').length;
        if (document.getElementById('statTotal')) document.getElementById('statTotal').textContent = items.length;
    }

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelector('.tab.active')?.classList.remove('active');
            tab.classList.add('active');
            const filter = tab.innerText.trim().toLowerCase();

            if (filter === 'active') {
                renderInventory(myItems.filter(i => i.status === 'active'));
            } else if (filter === 'swapped') {
                renderInventory(myItems.filter(i => i.status === 'swapped' || i.status === 'paid'));
            } else {
                renderInventory(myItems);
            }
        });
    });

    window.deleteItem = async (id) => {
        const confirm = await Swal.fire({ title: 'Delete?', text: 'Cannot undo!', icon: 'warning', showCancelButton: true });
        if (!confirm.isConfirmed) return;
        try {
            await fetch(`${API_BASE}/listings/${id}`, { method: 'DELETE' });
            loadInventory();
        } catch { Swal.fire('Error', 'Failed to delete', 'error'); }
    };

    loadInventory();
});


// ============ EDIT LISTING ============
document.addEventListener("DOMContentLoaded", () => {
    const editForm = document.getElementById('editForm');
    if (!editForm) return;

    const urlParams = new URLSearchParams(window.location.search);
    const listingId = urlParams.get('id');

    async function loadEditData() {
        if (!listingId) return;
        try {
            const res = await fetch(`${API_BASE}/listings/${listingId}`);
            let item = await res.json();
            if (item && item.data) item = item.data;
            if (!item || item.status === 'error') return;

            const el = (id) => document.getElementById(id);

            // Fetch and populate materials first so we can select the correct one
            const materialSelect = el('editMaterial');
            if (materialSelect) {
                try {
                    const mRes = await fetch(`${API_BASE}/materials`);
                    const mData = await mRes.json();
                    if (mData.status === 'success' && Array.isArray(mData.data)) {
                        let options = '';
                        mData.data.forEach(m => { options += `<option value="${m.material_id}">${m.name}</option>`; });
                        options += `<option value="null">Other</option>`;
                        materialSelect.innerHTML = options;
                    }
                } catch (e) { }
            }

            if (el('editTitle')) el('editTitle').value = item.title || '';
            if (el('editDesc')) el('editDesc').value = item.description || '';
            if (el('editCategory')) el('editCategory').value = item.category || 'Clothing';
            if (el('editCondition')) el('editCondition').value = item.condition_status || 'Good';
            if (el('editPrice')) el('editPrice').value = item.price || '';
            if (el('editListingType')) el('editListingType').value = item.listing_type || 'sale';
            if (el('editStatus')) el('editStatus').value = item.status || 'active';
            if (materialSelect) materialSelect.value = item.material_id || 'null';
        } catch (err) {
            console.error("Failed to load listing for edit:", err);
        }
    }

    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const updatedData = {
            title: document.getElementById('editTitle')?.value,
            description: document.getElementById('editDesc')?.value,
            category: document.getElementById('editCategory')?.value,
            condition_status: document.getElementById('editCondition')?.value,
            price: parseFloat(document.getElementById('editPrice')?.value) || 0,
            listing_type: document.getElementById('editListingType')?.value || 'sale',
            status: document.getElementById('editStatus')?.value || 'active',
            material_id: document.getElementById('editMaterial')?.value === 'null' ? null : parseInt(document.getElementById('editMaterial')?.value)
        };

        if (!listingId) {
            Swal.fire({ title: 'EcoSwap', text: 'No listing ID provided.', icon: 'error' });
            return;
        }
        try {
            const res = await fetch(`${API_BASE}/listings/${listingId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedData)
            });
            const data = await res.json();
            Swal.fire({ title: 'EcoSwap', text: data.message || 'Listing updated!', icon: 'success', confirmButtonColor: '#111' })
                .then(() => { window.location.href = "inventory.html"; });
        } catch {
            Swal.fire({ title: 'Error', text: 'Failed to update listing.', icon: 'error' });
        }
    });

    loadEditData();
});


// ============ CREATE LISTING ============
document.addEventListener("DOMContentLoaded", () => {
    const listingForm = document.getElementById("listingForm");
    const fileInput = document.getElementById("fileInput");
    const uploadArea = document.querySelector('.photo-upload');
    if (!listingForm) return;

    async function loadMaterials() {
        const materialSelect = document.getElementById('listingMaterial');
        if (!materialSelect) return;
        try {
            const res = await fetch(`${API_BASE}/materials`);
            const data = await res.json();
            if (data.status === 'success' && Array.isArray(data.data)) {
                let options = '';
                data.data.forEach(m => {
                    options += `<option value="${m.material_id}">${m.name}</option>`;
                });
                options += `<option value="null">Other</option>`;
                materialSelect.innerHTML = options;
            }
        } catch (e) {
            console.error('Failed to load materials:', e);
        }
    }
    loadMaterials();

    listingForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (!user.user_id) {
            Swal.fire({ title: 'Login Required', text: 'Please log in to create a listing.', icon: 'warning', confirmButtonColor: '#111' });
            return;
        }

        // Required by API: user_id, title, price
        // Optional: material_id, description, category, condition_status, listing_type, status
        const formData = {
            user_id: user.user_id,
            title: document.getElementById('listingTitle')?.value || '',
            description: document.getElementById('listingDesc')?.value || '',
            category: document.getElementById('listingCategory')?.value || 'Clothing',
            condition_status: document.getElementById('listingCondition')?.value || 'Good',
            listing_type: document.getElementById('listingType')?.value || 'sale',
            price: parseFloat(document.getElementById('listingPrice')?.value) || 0,
            material_id: (document.getElementById('listingMaterial')?.value && document.getElementById('listingMaterial')?.value !== 'null') ? parseInt(document.getElementById('listingMaterial').value) : null,
            status: 'active'
        };

        if (!formData.title) {
            Swal.fire({ title: 'Validation Error', text: 'Item title is required.', icon: 'error' });
            return;
        }

        const btn = listingForm.querySelector('.btn-submit');
        if (btn) { btn.innerHTML = '<i class="ti ti-loader-2"></i> Processing...'; btn.disabled = true; }

        try {
            const res = await fetch(`${API_BASE}/listings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();
            if (data.status === 'error') {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error' });
                if (btn) { btn.innerHTML = 'List Item Now'; btn.disabled = false; }
                return;
            }
            const notificationRes = await fetch(`${API_BASE}/community/notifications/all`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    type: "New Listing",
                    message: `${user.username || "A user"} added a new listing: ${formData.title}`,
                    exclude_user_id: user.user_id
                })
            });

            const notificationResult = await notificationRes.json();
            console.log("Notification result:", notificationResult);
            Swal.fire({ title: 'EcoSwap', text: data.message || 'Your item is now live!', icon: 'success', confirmButtonColor: '#09b1ba', confirmButtonText: 'Great!' })
                .then(r => { if (r.isConfirmed) window.location.href = "marketplace.html"; });
        } catch {
            if (btn) { btn.innerHTML = 'List Item Now'; btn.disabled = false; }
            Swal.fire({ title: 'Error', text: 'Failed to create listing.', icon: 'error' });
        }
    });
});


// ============ SHARED: SIDEBAR ============
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('visible');
}

function togglePages(id, btn) {
    const pages = document.getElementById(id);
    if (pages) pages.classList.toggle('open');
    if (btn) btn.classList.toggle('open');
}
