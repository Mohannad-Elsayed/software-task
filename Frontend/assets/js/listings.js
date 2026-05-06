document.addEventListener("DOMContentLoaded", () => {
        
        // 1. Mock Data - بيانات تجريبية لمحاكاة الداتا اللي هتيجي من باكيند Member 2
        const products = [
            {
                id: 1,
                name: "Vintage Denim Jacket",
                brand: "Levi's",
                price: 45.00,
                size: "L",
                image: "https://images.unsplash.com/photo-1529139574466-a303027c1d8b?q=80&w=1200&auto=format&fit=crop",
                canSwap: true,
                condition: "Good",
                datacategory: "Clothing"
            },
            {
                id: 2,
                name: "Summer Floral Dress",
                brand: "Zara",
                price: 30.00,
                size: "M",
                image: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=1200&auto=format&fit=crop",
                canSwap: false,
                condition: "New with tags",
                datacategory: "Clothing"
            },
            {
                id: 3,
                name: "Classic Sneakers",
                brand: "Nike",
                price: 65.00,
                size: "42",
                image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=400",
                canSwap: true,
                condition: "Upcycled",
                datacategory: "Shoes"
            },
             {
                id: 2,
                name: "Summer Floral Dress",
                brand: "Zara",
                price: 30.00,
                size: "M",
                image: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=1200&auto=format&fit=crop",
                canSwap: false,
                condition: "New with tags"
            },
            {
                id: 4,
                name: "Wool Winter Coat",
                brand: "H&M",
                price: 80.00,
                size: "S",
                image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1200&auto=format&fit=crop",
                canSwap: true,
                condition: "Excellent"
            }
        ];

        const grid = document.getElementById("productGrid");

       
        // مثال لتعديل كود الـ HTML داخل الـ Loop في listings.js
function renderProducts(productsToRender) {
    const grid = document.getElementById('productGrid');
    if (!grid) return;

    if (productsToRender.length === 0) {
        grid.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 50px;">
            <i class="ti ti-search" style="font-size: 48px; color: #ccc;"></i>
            <p>No items found matching your search.</p>
        </div>`;
        return;
    }

    grid.innerHTML = productsToRender.map(product => `
        <div class="product-card">
            <div style="position: relative;">
                <img src="${product.image}" class="product-image" alt="${product.name}">
                <button class="wishlist-btn" onclick="event.stopPropagation();">
                    <i class="ti ti-heart"></i>
                </button>
                ${product.canSwap ? 
                    `<span class="swap-badge" style="position:absolute; bottom:10px; right:10px; background:var(--primary); color:white;">
                        <i class="ti ti-refresh"></i> Swap
                    </span>` : ''}
            </div>
            
            <div class="product-info">
                <div style="font-weight: bold; font-size: 1.1rem;">$${product.price}</div>
                <div class="brand-name">${product.brand}</div>
                <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">
                    Size: ${product.size} • ${product.condition}
                </div>
                
                <button class="btn-swap" onclick="handleSwapRequest(${product.id})">
                    <i class="ti ti-arrows-exchange"></i> Propose Swap
                </button>
            </div>
        </div>
    `).join('');
}

        // 3. Initial Render
        renderProducts(products);



        const searchInput = document.getElementById("searchInput");
        searchInput.addEventListener("input", (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = products.filter(p => 
                p.name.toLowerCase().includes(term) || 
                p.brand.toLowerCase().includes(term)
            );
            renderProducts(filtered);
        });

        // 5. Swap Request Logic (Member 3 Task)
        window.handleSwapRequest = (id) => {
            const item = products.find(p => p.id === id);
            if (!item.canSwap) {
                // alert("This item is for sale only.");
                Swal.fire({
                title: 'Eco Marketplace',
                text: 'This item is for sale only.',
                icon: 'success',
                confirmButtonColor: '#111',
                confirmButtonText: 'OK'
                });
                return;
            }
            
            // هنا المفروض نفتح صفحة الـ SwapNegotiationPage
            // حالياً هنعمل تنبيه كـ Proof of Concept
            const confirmSwap = confirm(`Do you want to offer a swap for: ${item.name}?`);
            if (confirmSwap) {
                // alert("Request Sent! Redirecting to Negotiation Room...");               
            Swal.fire({
            title: 'Eco Marketplace',
            text: 'Request Sent! Redirecting to Negotiation Room...',
            icon: 'success',
            confirmButtonColor: '#111',
            confirmButtonText: 'OK'
            });
                // window.location.href = `swap-negotiation.html?id=${id}`;
            }
        };

        // 6. Animation on Scroll (لمسة الروقان)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = "translateY(0)";
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.product-card').forEach(card => {
            card.style.opacity = 0;
            card.style.transform = "translateY(20px)";
            card.style.transition = "all 0.5s ease-out";
            observer.observe(card);
        });
    });


document.addEventListener("DOMContentLoaded", () => {
        
    // 1. بيانات التحولات (Upcycle Projects)
    const upcycleLogs = [
        {
            id: 1,
            title: "From Ripped Jeans to Tote Bag",
            beforeImg: "https://images.unsplash.com/photo-1542272604-787c3835535d?w=500",
            afterImg: "https://images.unsplash.com/photo-1544816153-39ad44410d19?w=500",
            materials: ["Old Denim", "Cotton Thread"],
            description: "Used the legs of old Levi's to create a durable eco-friendly shopping bag.",
            ecoPoints: 50
        },
        {
            id: 2,
            title: "Vintage Jacket Paint Custom",
            beforeImg: "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500",
            afterImg: "https://images.unsplash.com/photo-1551488831-00ddcb6c6bd3?w=500",
            materials: ["Denim Jacket", "Acrylic Paint"],
            description: "Hand-painted a custom design on the back to cover some old stains.",
            ecoPoints: 35
        }
    ];

    const container = document.getElementById("logContainer");

    // 2. Render Logs
    function renderLogs() {
        container.innerHTML = upcycleLogs.map(log => `
            <div class="upcycle-card">
                <div class="transformation-box">
                    <div class="image-container">
                        <span class="label label-before">Before</span>
                        <img src="${log.beforeImg}" alt="Before">
                    </div>
                    <div class="image-container">
                        <span class="label label-after">After</span>
                        <img src="${log.afterImg}" alt="After">
                    </div>
                </div>
                <div class="content-body">
                    <div class="meta-info">
                        <h2 class="project-title">${log.title}</h2>
                        <div class="impact-score">
                            <i class="ti ti-leaf"></i> +${log.ecoPoints} Eco Points
                        </div>
                    </div>
                    <p style="color: #475569; font-size: 14px;">${log.description}</p>
                    <div class="process-tags">
                        ${log.materials.map(m => `<span class="tag"># ${m}</span>`).join('')}
                    </div>
                </div>
            </div>
        `).join('');
    }

    renderLogs();

    // 3. Open Creation Form (Mockup)
    window.openNewLog = () => {
        // alert("This would open a form to upload Before/After photos and list materials used.");
        Swal.fire({
        title: 'Eco Marketplace Says',
        text: 'This would open a form to upload Before/After photos and list materials used.',
        
        confirmButtonColor: '#111',
        confirmButtonText: 'OK'
        });
        // Redirect to a form page or open a modal
    };
});

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. تبديل الصور (Gallery Logic)
    window.updateImg = (src) => {
        const main = document.getElementById('mainDisplay');
        main.style.opacity = '0.5'; // تأثير بسيط عند التبديل
        setTimeout(() => {
            main.src = src;
            main.style.opacity = '1';
        }, 100);
    };

    // 2. منطق التبديل (Swap Logic - Member 3 Task)
    const swapBtn = document.getElementById('swapBtn');
    
    swapBtn.addEventListener('click', () => {
        // محاكاة التأكد من تسجيل الدخول (عن طريق Member 1 Auth)
        const isLoggedIn = true; 

        if (isLoggedIn) {
            const confirmSwap = confirm("Do you want to open a negotiation for this item?");
            if (confirmSwap) {
                // توجيه لصفحة الـ Swap Negotiation اللي عملناها قبل كدة
                // alert("Redirecting to Swap Negotiation Room...");
                Swal.fire({
                title: 'Eco Marketplace',
                text: 'Redirecting to Swap Negotiation Room...',
                
                confirmButtonColor: '#111',
                confirmButtonText: 'OK'
                });
                // window.location.href = "swap-negotiation.html?item=101";
            }
        } else {
            Swal.fire({
            title: 'Eco Marketplace',
            text: 'Please login first to propose a swap.',
            icon: 'warning',
            confirmButtonColor: '#111',
            confirmButtonText: 'OK'
            });
        }
    });

    // 3. محاكاة الـ Condition Tags بناءً على الداتا
    // لو الـ condition 'Fair'، نغير لون التاج أو نضيف تحذير
});


document.addEventListener("DOMContentLoaded", () => {
    
    // 1. البيانات (Inventory Data)
    // لاحظ إننا ضفنا الـ condition والـ status والـ isUpcycled زي ما مطلوب في موديل MM
    const myItems = [
        {
            id: 101,
            name: "Classic Leather Boots",
            category: "Shoes",
            condition: "Good",
            status: "Active",
            image: "https://images.unsplash.com/photo-1520639889313-727400c7ee10?w=200",
            isUpcycled: true
        },
        {
            id: 102,
            name: "Silk Summer Scarf",
            category: "Accessories",
            condition: "New",
            status: "Swapped",
            image: "https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=200",
            isUpcycled: false
        },
        {
            id: 103,
            name: "Oversized Hoodie",
            category: "Clothing",
            condition: "Worn",
            status: "Active",
            image: "https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=200",
            isUpcycled: false
        }
    ];

    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            // تغيير الـ UI
            document.querySelector('.tab.active').classList.remove('active');
            tab.classList.add('active');

            // الفلترة
            const filterType = tab.innerText.trim();
            if (filterType === 'All Items') {
                renderInventory(myItems);
            } else if (filterType === 'Active') {
                renderInventory(myItems.filter(i => i.status === 'Active'));
            } else if (filterType === 'Swapped') {
                renderInventory(myItems.filter(i => i.status === 'Swapped'));
            }
        });
    });

    window.bumpItem = (id) => {
        // alert("Listing boosted! Your item will now appear at the top of the Marketplace.");
        Swal.fire({
        title: 'Eco Marketplace',
        text: 'Listing boosted! Your item will now appear at the top of the Marketplace.',
        icon: 'success',
        confirmButtonColor: '#111',
        confirmButtonText: 'OK'
        });
    }

    const container = document.getElementById("inventoryBody");


    // تحديث دالة الـ Render لإضافة مميزات Vinted
    function renderInventory(items) {
        const container = document.getElementById("inventoryBody");
        if (!container) return;

        container.innerHTML = items.map(item => `
            <div class="item-row" id="row-${item.id}">
                <div class="img-wrapper" style="position: relative;">
                    <img src="${item.image}" class="item-img" alt="item">
                    ${item.isUpcycled ? '<i class="ti ti-leaf" style="position:absolute; bottom:5px; right:5px; background:white; border-radius:50%; padding:2px; color:green; font-size:12px; border:1px solid #eee;"></i>' : ''}
                </div>
                
                <div>
                    <div style="font-weight: 700; color: #1e293b;">${item.name}</div>
                    <div style="font-size: 12px; color: #64748b;">
                        <i class="ti ti-tag"></i> ${item.category} • <i class="ti ti-package"></i> ${item.id}
                    </div>
                </div>

                <div style="font-size: 14px; font-weight: 500;">
                    <span style="color: ${item.condition === 'New' ? '#10b981' : '#64748b'}">${item.condition}</span>
                </div>

                <div>
                    <span class="status-badge ${item.status === 'Active' ? 'status-active' : 'status-swapped'}">
                        ${item.status === 'Active' ? '● Live' : '✓ Swapped'}
                    </span>
                </div>

               
                <div class="item-stats" style="font-size: 11px; color: #94a3b8; margin-top: 5px;">
                    <span><i class="ti ti-eye"></i> 124 views</span> • 
                    <span><i class="ti ti-heart"></i> 12 likes</span>
                </div>

                <div class="action-btns">
                    <!-- زرار الـ Bump الجديد هنا -->
                    <button class="btn-icon" title="View in Shop" onclick="window.location.href='listing-details.html?id=${item.id}'">
                        <i class="ti ti-eye"></i>
                    </button>
                    <button class="btn-icon" title="Edit" onclick="editItem(${item.id})">
                        <i class="ti ti-edit"></i>
                    </button>
                    <button class="btn-icon btn-bump" title="Boost Item" onclick="bumpItem(${item.id})">
                        <i class="ti ti-rocket"></i> <!-- أيقونة صاروخ عشان الـ Boost -->
                    </button>
                    <button class="btn-icon btn-delete" title="Delete" onclick="deleteItem(${item.id})">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        // تحديث الأرقام في الـ Stats فوق (Dynamic Stats)
        updateStats(items);
    }

    function updateStats(items) {
        const activeCount = items.filter(i => i.status === 'Active').length;
        const swappedCount = items.filter(i => i.status === 'Swapped').length;
        
        // بنفترض إنك عندك الـ IDs دي في الـ HTML
        // لو مش موجودة ضيفها في الـ HTML عشان الـ JS يمسكها
        if(document.getElementById('activeCount')) document.getElementById('activeCount').innerText = activeCount;
    }
    

    // 3. Initial Load
    renderInventory(myItems);

    // 4. Delete Logic
    window.deleteItem = (id) => {
        if(confirm("Are you sure you want to remove this item from your closet?")) {
            const element = document.getElementById(`row-${id}`);
            element.style.opacity = '0';
            setTimeout(() => {
                element.remove();
                // alert("Item deleted successfully.");
                Swal.fire({
                title: 'Eco Marketplace',
                text: 'Item deleted successfully.',
                icon: 'success',
                confirmButtonColor: '#111',
                confirmButtonText: 'OK'
                });
            }, 300);
        }
    };

    // 5. Edit Logic (Redirect to EditListingPage)
    window.editItem = (id) => {
        // alert("Redirecting to Edit Page for Item #" + id);
        Swal.fire({
        title: 'Eco Marketplace',
        text: "Redirecting to Edit Page for Item #" + id,
        icon: 'success',
        confirmButtonColor: '#111',
        confirmButtonText: 'OK'
        });
        // window.location.href = `edit-listing.html?id=${id}`;
    };
});


document.addEventListener("DOMContentLoaded", () => {
    // 1. محاكاة بيانات المنتج القادم من الباكيند (بناءً على الـ ID)
    const existingItem = {
        id: 101,
        title: "Vintage Denim Jacket",
        description: "A classic 90s style jacket in perfect condition. No stains.",
        category: "Clothing",
        condition: "Excellent",
        price: 55,
        brand: "Levi's",
        canSwap: true,
        photos: [
            "https://images.unsplash.com/photo-1576871333019-220f1300c752?w=200",
            "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=200"
        ]
    };

    // 2. تعبئة الحقول بالبيانات الحالية (Populate)
    document.getElementById('editTitle').value = existingItem.title;
    document.getElementById('editDesc').value = existingItem.description;
    document.getElementById('editCategory').value = existingItem.category;
    document.getElementById('editCondition').value = existingItem.condition;
    document.getElementById('editPrice').value = existingItem.price;
    document.getElementById('editBrand').value = existingItem.brand;
    document.getElementById('editSwapToggle').checked = existingItem.canSwap;

    // 3. عرض الصور الحالية مع إمكانية الحذف
    const gallery = document.getElementById('photoGallery');
    existingItem.photos.forEach((url, index) => {
        const thumb = document.createElement('div');
        thumb.className = 'photo-thumb';
        thumb.innerHTML = `
            <img src="${url}" alt="Item photo">
            <button class="remove-photo" onclick="this.parentElement.remove()">×</button>
        `;
        gallery.appendChild(thumb);
    });

    // 4. معالجة التعديل
    const form = document.getElementById('editForm');
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // تجميع البيانات الجديدة
        const updatedData = {
            id: existingItem.id,
            title: document.getElementById('editTitle').value,
            canSwap: document.getElementById('editSwapToggle').checked
        };

        console.log("Sending Updates to Backend:", updatedData);
        
        // alert("Listing updated successfully!");
        Swal.fire({
        title: 'Eco Marketplace',
        text: 'Listing updated successfully!',
        icon: 'success',
        confirmButtonColor: '#111',
        confirmButtonText: 'OK'
        });
        window.location.href = "inventory.html"; // العودة للمخزن بعد التعديل
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("listingForm");
    const fileInput = document.getElementById("fileInput");
    const uploadArea = document.querySelector('.photo-upload'); // مساحة رفع الصور

    // 1. التعامل مع رفع الصور + عرض المعاينة (Preview)
    fileInput.addEventListener("change", (e) => {
        const files = e.target.files;
        
        if (files.length > 0) {
            // مسح المحتوى القديم (الأيقونة والنص) ووضع شبكة الصور
            uploadArea.innerHTML = `<div id="preview-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap:10px; width:100%"></div>`;
            const grid = document.getElementById('preview-grid');
            
            Array.from(files).slice(0, 5).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.style.position = "relative";
                    div.innerHTML = `
                        <img src="${event.target.result}" style="width:100%; height:80px; object-fit:cover; border-radius:8px; border: 1px solid #ddd;">
                    `;
                    grid.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
            
            console.log(`${files.length} photos selected for preview.`);
        }
    });

    // 2. معالجة الفورم عند الإرسال (Submit)
    form.addEventListener("submit", (e) => {
        e.preventDefault();

        // تجميع البيانات
        const formData = {
            title: form.querySelector('input[placeholder*="Title"]').value,
            category: form.querySelector('select').value,
            price: form.querySelector('input[type="number"]').value,
            canSwap: document.getElementById("swapToggle").checked,
            timestamp: new Date().toISOString()
        };

        console.log("Submitting to Backend:", formData);

        // تأثير "تم النجاح" على الزرار
        const btn = form.querySelector('.btn-submit');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="ti ti-loader-2 rotate"></i> Processing...'; // حركة لودينج بسيطة
        btn.disabled = true;

        // محاكاة الإرسال (API Call Simulation)
        setTimeout(() => {
            btn.innerHTML = '<i class="ti ti-check"></i> Listing Created!';
            btn.style.background = "#10b981"; // لون أخضر نجاح

            // alert("Your item is now live in the Marketplace! 🌿");
            Swal.fire({
            title: 'Eco Marketplace',
            text: 'Your item is now live in the Marketplace! 🌿',
            icon: 'success',
            confirmButtonColor: '#111',
            confirmButtonText: 'OK'
            });
            // اختياري: توجيه المستخدم لصفحة المخزن بعد النجاح
            // window.location.href = "inventory.html"; 
        }, 1500);
    });
});

    function updateImg(src) {
    const mainImg = document.getElementById('mainDisplay');
    // إضافة تأثير بسيط عند التغيير
    mainImg.style.opacity = '0';
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);
}

// --- Swap Modal Logic (Member 3) ---

// 1. بيانات تجريبية لمنتجاتي (اللي هقايض بيها)
const myInventory = [
    { id: 101, name: "Graphic T-Shirt", size: "M", image: "https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=200" },
    { id: 102, name: "Nike Air Max", size: "42", image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200" },
    { id: 103, name: "Casual Chinos", size: "32", image: "https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=200" }
];

let selectedItemId = null;

// 2. دالة فتح المودال وبناء القائمة
// ملحوظة: هنغير الـ Event Listener عشان يشتغل مع الـ HTML بتاعك
function openSwapModal() {
    const modal = document.getElementById('swapModal');
    const listContainer = document.getElementById('myItemsList');
    
    if (!modal || !listContainer) return;

    listContainer.innerHTML = myInventory.map(item => `
        <div class="selectable-item" onclick="selectItem(this, ${item.id})">
            <img src="${item.image}" alt="${item.name}">
            <div class="item-details">
                <h4>${item.name}</h4>
                <p>Size: ${item.size}</p>
            </div>
        </div>
    `).join('');
    
    modal.style.display = 'flex';
}

// 3. وظيفة اختيار المنتج من القائمة
window.selectItem = function(element, id) {
    document.querySelectorAll('.selectable-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedItemId = id;
    
    const confirmBtn = document.getElementById('confirmSwapBtn');
    if (confirmBtn) confirmBtn.disabled = false;
};

// 4. إغلاق الـ Modal
window.closeSwapModal = function() {
    const modal = document.getElementById('swapModal');
    if (modal) modal.style.display = 'none';
};

// 5. تفعيل الأزرار عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", () => {
    const swapBtn = document.getElementById('swapBtn');
    const confirmSwapBtn = document.getElementById('confirmSwapBtn');

    if (swapBtn) {
        // بنشيل أي Event Listener قديم ونحط الجديد بتاع الـ Modal
        swapBtn.onclick = (e) => {
            e.preventDefault();
            openSwapModal();
        };
    }

    if (confirmSwapBtn) {
        confirmSwapBtn.addEventListener('click', () => {
            // alert(`Success! Proposal sent with item ID: ${selectedItemId}. Waiting for owner's response.`);
            Swal.fire({
            title: 'Eco Marketplace',
            text: `Success! Proposal sent with item ID: ${selectedItemId}. Waiting for owner's response.`,
            icon: 'success',
            confirmButtonColor: '#111',
            confirmButtonText: 'OK'
            });
            closeSwapModal();
        });
    }
});


// --- Buy Now Logic ---

document.addEventListener("DOMContentLoaded", () => {
    const buyBtn = document.getElementById('buyBtn');

    if (buyBtn) {
        buyBtn.addEventListener('click', () => {
            // 1. الحصول على بيانات المنتج من الصفحة (أو من الـ Mock Data)
            // في الحقيقة، بنجيب الـ ID من الـ URL
            const urlParams = new URLSearchParams(window.location.search);
            const productId = urlParams.get('id') || '101'; // افتراضي للتجربة

            // 2. محاكاة فحص حالة المنتج
            const productStatus = "Active"; // دي بتيجي من الباكيند أصلاً

            if (productStatus === "Active") {
                handleBuyNow(productId);
            } else {
                // alert("Sorry, this item is no longer available.");
                Swal.fire({
                title: 'Eco Marketplace',
                text: 'Sorry, this item is no longer available.',
                icon: 'success',
                confirmButtonColor: '#111',
                confirmButtonText: 'OK'
                });
            }
        });
    }
});

function handleBuyNow(id) {
    // 3. تخزين بيانات المنتج مؤقتاً للانتقال لصفحة الدفع
    const purchaseInfo = {
        itemId: id,
        purchaseDate: new Date().toISOString(),
        type: 'direct_buy'
    };
    
    localStorage.setItem('pendingPurchase', JSON.stringify(purchaseInfo));

    // 4. التوجيه لصفحة الـ Checkout
    // alert("Proceeding to secure checkout...");
    Swal.fire({
    title: 'Eco Marketplace',
    text: 'Proceeding to secure checkout...',
    icon: 'success',
    confirmButtonColor: '#111',
    confirmButtonText: 'OK'
    });
    // window.location.href = `checkout.html?id=${id}`;
}

document.addEventListener("DOMContentLoaded", () => {
    const listingForm = document.getElementById("listingForm");

    if (listingForm) {
        listingForm.addEventListener("submit", (e) => {
            e.preventDefault(); // منع الصفحة من التحميل (Refresh)

            // 1. تجميع البيانات من الفورم
            const formData = {
                title: listingForm.querySelector('input[type="text"]').value,
                description: listingForm.querySelector('textarea').value,
                category: listingForm.querySelectorAll('select')[0].value,
                condition: listingForm.querySelectorAll('select')[1].value,
                ecoContribution: listingForm.querySelectorAll('select')[2].value,
                weight: listingForm.querySelector('input[type="number"]').value,
                canSwap: document.getElementById("swapToggle").checked
            };

            console.log("Data to be sent:", formData);

            // 2. إظهار رسالة نجاح باستخدام SweetAlert المدمج في صفحتك
            Swal.fire({
                title: 'Eco Marketplace',
                text: 'Your item has been listed successfully! 🌿',
                icon: 'success',
                confirmButtonColor: '#09b1ba',
                confirmButtonText: 'Great!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // توجيه المستخدم للماركت بليس بعد النجاح
                    window.location.href = "marketplace.html";
                }
            });
        });
    }
});

function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('overlay').classList.toggle('visible');
        }

        function togglePages(id, btn) {
            const pages = document.getElementById(id);
            pages.classList.toggle('open');
            btn.classList.toggle('open');
        }