const API_BASE = 'http://localhost:8000/Backend/index.php?route=/api';

// ============ MARKETPLACE ============
document.addEventListener("DOMContentLoaded", () => {
    const grid = document.getElementById("productGrid");
    if (!grid) return;

    async function loadMarketplace() {
        try {
            const res = await fetch(`${API_BASE}/listings`);
            let products = await res.json();
            if (products && products.data) products = products.data;
            if (!Array.isArray(products)) { grid.innerHTML = '<p style="text-align:center;padding:40px;color:#94a3b8;">No listings available.</p>'; return; }
            allProducts = products;
            renderProducts(products);
        } catch (err) {
            console.error("Failed to load listings:", err);
            grid.innerHTML = '<p style="text-align:center;padding:40px;color:#ef4444;">Failed to load listings from server.</p>';
        }
    }

    function renderProducts(productsToRender) {
        if (!productsToRender.length) {
            grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:50px;">
                <i class="ti ti-search" style="font-size:48px;color:#ccc;"></i>
                <p>No items found matching your search.</p></div>`;
            return;
        }
        grid.innerHTML = productsToRender.map(p => `
            <a href="listing-details.html?id=${p.listing_id}" class="product-card">
                <div style="position:relative;">
                    <img src="${p.image || 'https://via.placeholder.com/400x500?text=No+Image'}" class="product-image" alt="${p.title || ''}">
                    <button class="wishlist-btn" onclick="event.preventDefault();event.stopPropagation();"><i class="ti ti-heart"></i></button>
                    ${p.listing_type === 'swap' ? '<span class="swap-badge" style="position:absolute;bottom:10px;right:10px;background:var(--primary);color:white;"><i class="ti ti-refresh"></i> Swap</span>' : ''}
                </div>
                <div class="product-info">
                    <div style="font-weight:bold;font-size:1.1rem;">$${p.price || 0}</div>
                    <div class="brand-name">${p.category || ''}</div>
                    <div style="font-size:12px;color:var(--muted);margin-bottom:8px;">
                        ${p.condition_status || '—'}
                    </div>
                    <button class="btn-swap" onclick="event.preventDefault();event.stopPropagation();handleSwapRequest(${p.listing_id})">
                        <i class="ti ti-arrows-exchange"></i> Propose Swap
                    </button>
                </div>
            </a>
        `).join('');

        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.style.opacity=1; e.target.style.transform="translateY(0)"; }});
        }, {threshold:0.1});
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.opacity=0; c.style.transform="translateY(20px)"; c.style.transition="all 0.5s ease-out";
            observer.observe(c);
        });
    }

    const searchInput = document.getElementById("searchInput");
    let allProducts = [];
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

    window.handleSwapRequest = (id) => {
        window.location.href = `listing-details.html?id=${id}`;
    };

    loadMarketplace();
});


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
                    <div class="transformation-box">
                        <div class="image-container">
                            <span class="label label-before">Before</span>
                            <img src="${log.before_image_url || 'https://via.placeholder.com/500x350?text=Before'}" alt="Before">
                        </div>
                        <div class="image-container">
                            <span class="label label-after">After</span>
                            <img src="${log.after_image_url || 'https://via.placeholder.com/500x350?text=After'}" alt="After">
                        </div>
                    </div>
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
                <input id="swal-before" class="swal2-input" placeholder="Before image URL">
                <input id="swal-after" class="swal2-input" placeholder="After image URL">
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
                    before_image_url: document.getElementById('swal-before').value,
                    after_image_url: document.getElementById('swal-after').value,
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
    const mainDisplay = document.getElementById('mainDisplay');
    if (!mainDisplay) return;

    const urlParams = new URLSearchParams(window.location.search);
    const listingId = urlParams.get('id');

    window.updateImg = (src) => {
        mainDisplay.style.opacity = '0.5';
        setTimeout(() => { mainDisplay.src = src; mainDisplay.style.opacity = '1'; }, 100);
    };

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

            const priceEl = document.getElementById('listingPrice');
            if (priceEl) priceEl.textContent = `$${item.price || 0}`;

            if (item.image) mainDisplay.src = item.image;
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
        buyBtn.addEventListener('click', async () => {
            const user = JSON.parse(localStorage.getItem('user') || '{}');
            if (!user.user_id) {
                Swal.fire({ title: 'Login Required', text: 'Please log in to place an order.', icon: 'warning', confirmButtonColor: '#111' });
                return;
            }
            if (!listingId) {
                Swal.fire({ title: 'EcoSwap', text: 'Proceeding to secure checkout...', icon: 'success', confirmButtonColor: '#111' });
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
                if (data.status === 'error') { Swal.fire({ title: 'Error', text: data.message, icon: 'error' }); return; }
                Swal.fire({ title: 'EcoSwap', text: 'Order placed! Proceeding to checkout...', icon: 'success', confirmButtonColor: '#111' });
            } catch {
                Swal.fire({ title: 'EcoSwap', text: 'Proceeding to secure checkout...', icon: 'success', confirmButtonColor: '#111' });
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
                    <div style="background:white;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                        <img src="${item.image || 'https://via.placeholder.com/400x200'}" style="width:100%;height:200px;object-fit:cover;">
                        <div style="padding:12px;">
                            <div style="font-weight:800;">$${item.price || 0}</div>
                            <div style="font-size:13px;color:var(--muted);">${item.title || ''}</div>
                        </div>
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
    if (!modal || !listContainer) return;

    listContainer.innerHTML = '<p style="text-align:center;padding:20px;color:#94a3b8;">Loading your items...</p>';
    modal.style.display = 'flex';

    fetch(`${API_BASE}/listings`).then(r => r.json()).then(res => {
        const items = res.data || res;
        if (!Array.isArray(items) || !items.length) {
            listContainer.innerHTML = '<p style="text-align:center;padding:20px;color:#94a3b8;">No items in your inventory.</p>';
            return;
        }
        listContainer.innerHTML = items.map(item => `
            <div class="selectable-item" onclick="selectItem(this, ${item.listing_id})">
                <img src="${item.image || 'https://via.placeholder.com/60'}" alt="${item.title || ''}">
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

window.selectItem = function(element, id) {
    document.querySelectorAll('.selectable-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedItemId = id;
    const confirmBtn = document.getElementById('confirmSwapBtn');
    if (confirmBtn) confirmBtn.disabled = false;
};

window.closeSwapModal = function() {
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
                        requested_listing_id: requestedListingId
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

    async function loadInventory() {
        try {
            const res = await fetch(`${API_BASE}/listings`);
            let items = await res.json();
            if (items && items.data) items = items.data;
            if (!Array.isArray(items)) { container.innerHTML = '<p style="padding:20px;text-align:center;color:#94a3b8;">No items found.</p>'; return; }
            allItems = items;
            renderInventory(items);
            updateStats(items);
        } catch (err) {
            console.error("Failed to load inventory:", err);
            container.innerHTML = '<p style="padding:20px;text-align:center;color:#ef4444;">Failed to load inventory.</p>';
        }
    }

    function renderInventory(items) {
        if (!items.length) { container.innerHTML = '<p style="padding:20px;text-align:center;color:#94a3b8;">No items found.</p>'; return; }
        container.innerHTML = items.map(item => `
            <div class="item-row" id="row-${item.listing_id}">
                <div>
                    <img src="${item.image || 'https://via.placeholder.com/60'}" class="item-img" alt="item">
                </div>
                <div>
                    <div style="font-weight:700;color:#1e293b;">${item.title || ''}</div>
                    <div style="font-size:12px;color:#64748b;">
                        <i class="ti ti-tag"></i> ${item.category || '—'} &bull; ID: ${item.listing_id}
                    </div>
                </div>
                <div style="font-size:14px;font-weight:500;">
                    ${item.condition_status || '—'}
                </div>
                <div>
                    <span class="status-badge ${item.status === 'active' ? 'status-active' : 'status-swapped'}">
                        ${item.status === 'active' ? '● Live' : item.status || '—'}
                    </span>
                </div>
                <div class="action-btns">
                    <button class="btn-icon" title="View" onclick="window.location.href='listing-details.html?id=${item.listing_id}'"><i class="ti ti-eye"></i></button>
                    <button class="btn-icon" title="Edit" onclick="window.location.href='edit-listing.html?id=${item.listing_id}'"><i class="ti ti-edit"></i></button>
                    <button class="btn-icon btn-delete" title="Delete" onclick="deleteItem(${item.listing_id})"><i class="ti ti-trash"></i></button>
                </div>
            </div>
        `).join('');
    }

    function updateStats(items) {
        const elActive = document.getElementById('statActive');
        const elSwapped = document.getElementById('statSwapped');
        const elTotal = document.getElementById('statTotal');
        if (elActive) elActive.textContent = items.filter(i => i.status === 'active').length;
        if (elSwapped) elSwapped.textContent = items.filter(i => i.status === 'swapped').length;
        if (elTotal) elTotal.textContent = items.length;
    }

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelector('.tab.active')?.classList.remove('active');
            tab.classList.add('active');
            const filterType = tab.innerText.trim();
            if (filterType === 'All Items') renderInventory(allItems);
            else if (filterType === 'Active') renderInventory(allItems.filter(i => i.status === 'active'));
            else if (filterType === 'Swapped') renderInventory(allItems.filter(i => i.status === 'swapped'));
            else renderInventory(allItems);
        });
    });

    window.deleteItem = async (id) => {
        const result = await Swal.fire({
            title: 'Delete Listing?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Delete'
        });
        if (!result.isConfirmed) return;
        try {
            const res = await fetch(`${API_BASE}/listings/${id}`, { method: 'DELETE' });
            const data = await res.json();
            Swal.fire({ title: 'EcoSwap', text: data.message || 'Item deleted.', icon: 'success', confirmButtonColor: '#111' });
            loadInventory();
        } catch {
            Swal.fire({ title: 'Error', text: 'Failed to delete item.', icon: 'error' });
        }
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
            if (el('editTitle')) el('editTitle').value = item.title || '';
            if (el('editDesc')) el('editDesc').value = item.description || '';
            if (el('editCategory')) el('editCategory').value = item.category || 'Clothing';
            if (el('editCondition')) el('editCondition').value = item.condition_status || 'Good';
            if (el('editPrice')) el('editPrice').value = item.price || '';
            if (el('editListingType')) el('editListingType').value = item.listing_type || 'sale';
            if (el('editStatus')) el('editStatus').value = item.status || 'active';
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
            status: document.getElementById('editStatus')?.value || 'active'
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

    if (fileInput) {
        fileInput.addEventListener("change", (e) => {
            const files = e.target.files;
            if (files.length > 0 && uploadArea) {
                uploadArea.innerHTML = `<div id="preview-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:10px;width:100%"></div>`;
                const previewGrid = document.getElementById('preview-grid');
                Array.from(files).slice(0, 5).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const div = document.createElement('div');
                        div.innerHTML = `<img src="${event.target.result}" style="width:100%;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">`;
                        previewGrid.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }

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
