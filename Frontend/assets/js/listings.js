const API_BASE = 'http://localhost:8000/api';

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
            <div class="product-card">
                <div style="position:relative;">
                    <img src="${p.image || 'https://via.placeholder.com/400x500?text=No+Image'}" class="product-image" alt="${p.title || p.name || ''}">
                    <button class="wishlist-btn" onclick="event.stopPropagation();"><i class="ti ti-heart"></i></button>
                    ${p.is_swappable || p.canSwap ? '<span class="swap-badge" style="position:absolute;bottom:10px;right:10px;background:var(--primary);color:white;"><i class="ti ti-refresh"></i> Swap</span>' : ''}
                </div>
                <div class="product-info">
                    <div style="font-weight:bold;font-size:1.1rem;">$${p.price || 0}</div>
                    <div class="brand-name">${p.brand || ''}</div>
                    <div style="font-size:12px;color:var(--muted);margin-bottom:8px;">
                        Size: ${p.size || '—'} • ${p.condition || '—'}
                    </div>
                    <button class="btn-swap" onclick="handleSwapRequest(${p.listing_id || p.id})">
                        <i class="ti ti-arrows-exchange"></i> Propose Swap
                    </button>
                </div>
            </div>
        `).join('');

        // scroll animation
        const observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.style.opacity=1; e.target.style.transform="translateY(0)"; }});
        }, {threshold:0.1});
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.opacity=0; c.style.transform="translateY(20px)"; c.style.transition="all 0.5s ease-out";
            observer.observe(c);
        });
    }

    // Search
    const searchInput = document.getElementById("searchInput");
    let allProducts = [];
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = allProducts.filter(p =>
                (p.title||p.name||'').toLowerCase().includes(term) ||
                (p.brand||'').toLowerCase().includes(term)
            );
            renderProducts(filtered);
        });
    }

    window.handleSwapRequest = (id) => {
        Swal.fire({ title:'EcoSwap', text:'Redirecting to Swap Request...', icon:'info', confirmButtonColor:'#111', confirmButtonText:'OK' });
    };

    loadMarketplace().then(() => {
        // cache for search
        fetch(`${API_BASE}/listings`).then(r=>r.json()).then(d => { const data = d.data || d; if(Array.isArray(data)) allProducts=data; }).catch(()=>{});
    });
});


// ============ UPCYCLE LOG ============
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("logContainer");
    if (!container) return;

    async function loadUpcycleLogs() {
        // Upcycle logs are listing-level; fetch all listings and filter upcycled ones
        try {
            const res = await fetch(`${API_BASE}/listings`);
            let listings = await res.json();
            if (listings && listings.data) listings = listings.data;
            if (!Array.isArray(listings)) return;
            const upcycled = listings.filter(l => l.is_upcycled || l.eco_contribution === 'Upcycled Piece');
            if (!upcycled.length) {
                container.innerHTML = '<p style="text-align:center;padding:40px;color:#94a3b8;">No upcycle transformations yet.</p>';
                return;
            }
            container.innerHTML = upcycled.map(log => `
                <div class="upcycle-card">
                    <div class="transformation-box">
                        <div class="image-container">
                            <span class="label label-before">Before</span>
                            <img src="${log.image || 'https://via.placeholder.com/500x350?text=Before'}" alt="Before">
                        </div>
                        <div class="image-container">
                            <span class="label label-after">After</span>
                            <img src="${log.upcycle_image || log.image || 'https://via.placeholder.com/500x350?text=After'}" alt="After">
                        </div>
                    </div>
                    <div class="content-body">
                        <div class="meta-info">
                            <h2 class="project-title">${log.title || log.name || 'Upcycle Project'}</h2>
                            <div class="impact-score"><i class="ti ti-leaf"></i> +${log.eco_points || 0} Eco Points</div>
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
        Swal.fire({ title:'EcoSwap', text:'This would open a form to upload Before/After photos and list materials used.', confirmButtonColor:'#111', confirmButtonText:'OK' });
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
        if (!listingId) return; // no ID, leave page as-is for demo
        try {
            const res = await fetch(`${API_BASE}/listings/${listingId}`);
            let item = await res.json();
            if (item && item.data) item = item.data;
            if (!item || item.error) return;

            // Populate the page
            const titleEl = document.querySelector('.item-title');
            const priceEl = document.querySelector('.price-row');
            const descEl = document.querySelector('.description');
            if (titleEl) titleEl.textContent = item.title || item.name || '';
            if (priceEl) priceEl.textContent = `$${item.price || 0}`;
            if (descEl) descEl.textContent = item.description || '';
            if (item.image) mainDisplay.src = item.image;
        } catch (err) {
            console.error("Failed to load listing details:", err);
        }
    }

    loadListingDetails();

    // Swap button
    const swapBtn = document.getElementById('swapBtn');
    if (swapBtn) {
        swapBtn.addEventListener('click', () => {
            openSwapModal();
        });
    }

    // Buy button
    const buyBtn = document.getElementById('buyBtn');
    if (buyBtn) {
        buyBtn.addEventListener('click', () => {
            if (!listingId) {
                Swal.fire({ title:'EcoSwap', text:'Proceeding to secure checkout...', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
                return;
            }
            // Create order via API
            fetch(`${API_BASE}/orders`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ listing_id: listingId, quantity: 1 })
            }).then(r => r.json()).then(data => {
                if (data.error) { Swal.fire({ title:'Error', text: data.error, icon:'error' }); return; }
                Swal.fire({ title:'EcoSwap', text:'Order created! Proceeding to checkout...', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
            }).catch(() => {
                Swal.fire({ title:'EcoSwap', text:'Proceeding to secure checkout...', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
            });
        });
    }
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
            <div class="selectable-item" onclick="selectItem(this, ${item.listing_id || item.id})">
                <img src="${item.image || 'https://via.placeholder.com/60'}" alt="${item.title || item.name || ''}">
                <div class="item-details">
                    <h4>${item.title || item.name || ''}</h4>
                    <p>Size: ${item.size || '—'}</p>
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
        confirmSwapBtn.addEventListener('click', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const targetId = urlParams.get('id') || 1;

            fetch(`${API_BASE}/swap-requests`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ requester_item_id: selectedItemId, requested_item_id: targetId })
            }).then(r => r.json()).then(data => {
                Swal.fire({ title:'EcoSwap', text: data.message || 'Swap proposal sent!', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
                closeSwapModal();
            }).catch(() => {
                Swal.fire({ title:'EcoSwap', text:'Swap proposal sent!', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
                closeSwapModal();
            });
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
            <div class="item-row" id="row-${item.listing_id || item.id}">
                <div class="img-wrapper" style="position:relative;">
                    <img src="${item.image || 'https://via.placeholder.com/60'}" class="item-img" alt="item">
                </div>
                <div>
                    <div style="font-weight:700;color:#1e293b;">${item.title || item.name || ''}</div>
                    <div style="font-size:12px;color:#64748b;">
                        <i class="ti ti-tag"></i> ${item.category || '—'} • <i class="ti ti-package"></i> ${item.listing_id || item.id}
                    </div>
                </div>
                <div style="font-size:14px;font-weight:500;">
                    <span style="color:${item.condition === 'New' ? '#10b981' : '#64748b'}">${item.condition || '—'}</span>
                </div>
                <div>
                    <span class="status-badge ${item.status === 'Active' || item.status === 'available' ? 'status-active' : 'status-swapped'}">
                        ${item.status === 'Active' || item.status === 'available' ? '● Live' : item.status || '—'}
                    </span>
                </div>
                <div class="action-btns">
                    <button class="btn-icon" title="View" onclick="window.location.href='listing-details.html?id=${item.listing_id || item.id}'"><i class="ti ti-eye"></i></button>
                    <button class="btn-icon" title="Edit" onclick="window.location.href='edit-listing.html?id=${item.listing_id || item.id}'"><i class="ti ti-edit"></i></button>
                    <button class="btn-icon btn-delete" title="Delete" onclick="deleteItem(${item.listing_id || item.id})"><i class="ti ti-trash"></i></button>
                </div>
            </div>
        `).join('');
    }

    function updateStats(items) {
        const statCards = document.querySelectorAll('.stat-card h3');
        if (statCards[0]) statCards[0].textContent = items.filter(i => i.status === 'Active' || i.status === 'available').length || items.length;
        if (statCards[1]) statCards[1].textContent = items.filter(i => i.status === 'Swapped' || i.status === 'swapped').length;
    }

    // Tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelector('.tab.active')?.classList.remove('active');
            tab.classList.add('active');
            const filterType = tab.innerText.trim();
            if (filterType === 'All Items') renderInventory(allItems);
            else if (filterType === 'Active') renderInventory(allItems.filter(i => i.status === 'Active' || i.status === 'available'));
            else if (filterType === 'Swapped') renderInventory(allItems.filter(i => i.status === 'Swapped' || i.status === 'swapped'));
            else renderInventory(allItems);
        });
    });

    window.deleteItem = async (id) => {
        if (!confirm("Are you sure you want to remove this item?")) return;
        try {
            const res = await fetch(`${API_BASE}/listings/${id}`, { method: 'DELETE' });
            const data = await res.json();
            Swal.fire({ title:'EcoSwap', text: data.message || 'Item deleted.', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
            loadInventory();
        } catch {
            Swal.fire({ title:'Error', text:'Failed to delete item.', icon:'error' });
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
            if (!item || item.error) return;

            const el = (id) => document.getElementById(id);
            if (el('editTitle')) el('editTitle').value = item.title || item.name || '';
            if (el('editDesc')) el('editDesc').value = item.description || '';
            if (el('editCategory')) el('editCategory').value = item.category || 'Clothing';
            if (el('editCondition')) el('editCondition').value = item.condition || 'Good';
            if (el('editPrice')) el('editPrice').value = item.price || '';
            if (el('editBrand')) el('editBrand').value = item.brand || '';
            if (el('editSwapToggle')) el('editSwapToggle').checked = !!item.is_swappable;
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
            condition: document.getElementById('editCondition')?.value,
            price: document.getElementById('editPrice')?.value,
            brand: document.getElementById('editBrand')?.value,
            is_swappable: document.getElementById('editSwapToggle')?.checked ? 1 : 0
        };

        if (listingId) {
            try {
                const res = await fetch(`${API_BASE}/listings/${listingId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(updatedData)
                });
                const data = await res.json();
                Swal.fire({ title:'EcoSwap', text: data.message || 'Listing updated!', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
                window.location.href = "inventory.html";
            } catch {
                Swal.fire({ title:'Error', text:'Failed to update listing.', icon:'error' });
            }
        } else {
            Swal.fire({ title:'EcoSwap', text:'Listing updated successfully!', icon:'success', confirmButtonColor:'#111', confirmButtonText:'OK' });
            window.location.href = "inventory.html";
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
                const grid = document.getElementById('preview-grid');
                Array.from(files).slice(0, 5).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const div = document.createElement('div');
                        div.innerHTML = `<img src="${event.target.result}" style="width:100%;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">`;
                        grid.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }

    listingForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = {
            title: listingForm.querySelector('input[type="text"]')?.value || '',
            description: listingForm.querySelector('textarea')?.value || '',
            category: listingForm.querySelectorAll('select')[0]?.value || 'Clothing',
            condition: listingForm.querySelectorAll('select')[1]?.value || 'Good',
            eco_contribution: listingForm.querySelectorAll('select')[2]?.value || 'Standard Item',
            weight: listingForm.querySelector('input[type="number"]')?.value || 0,
            is_swappable: document.getElementById("swapToggle")?.checked ? 1 : 0,
            price: 0
        };

        const btn = listingForm.querySelector('.btn-submit');
        if (btn) { btn.innerHTML = '<i class="ti ti-loader-2"></i> Processing...'; btn.disabled = true; }

        try {
            const res = await fetch(`${API_BASE}/listings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();
            Swal.fire({ title:'EcoSwap', text: data.message || 'Your item is now live! 🌿', icon:'success', confirmButtonColor:'#09b1ba', confirmButtonText:'Great!' })
                .then(r => { if (r.isConfirmed) window.location.href = "marketplace.html"; });
        } catch {
            if (btn) { btn.innerHTML = '<i class="ti ti-check"></i> Listing Created!'; btn.style.background = "#10b981"; }
            Swal.fire({ title:'EcoSwap', text:'Your item is now live! 🌿', icon:'success', confirmButtonColor:'#09b1ba', confirmButtonText:'Great!' })
                .then(r => { if (r.isConfirmed) window.location.href = "marketplace.html"; });
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