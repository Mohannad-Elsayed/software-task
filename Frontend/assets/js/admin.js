document.addEventListener("DOMContentLoaded", function() {

    // ==========================================
    // 1. Helper Function: Switch Sections (If Single Page)
    // ==========================================
    function switchTab(targetId) {
        const sections = document.querySelectorAll('.section-tab');
        if (sections.length > 0) {
            sections.forEach(s => s.classList.remove('active'));
            const targetSection = document.getElementById(targetId);
            if(targetSection) targetSection.classList.add('active');
        }
    }

    // ==========================================
    // 2. API Configuration
    // ==========================================
    const API_BASE_URL = 'http://localhost:8000/api/'; 

    async function fetchFromAPI(endpoint, method = 'GET', data = null) {
        const url = API_BASE_URL + endpoint;
        const options = {
            method: method,
            headers: { 'Content-Type': 'application/json' }
        };
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (error) {
            console.error("API Error:", error);
            return null;
        }
    }

    // ==========================================
    // 3. Customer Portal Logic (Orders & Payments)
    // ==========================================
    
    // --- FIX: Proceed to Checkout Button ---
    const proceedToCheckoutBtn = document.getElementById('proceedToCheckout');
    if(proceedToCheckoutBtn) {
        proceedToCheckoutBtn.addEventListener('click', () => {
            const checkoutSection = document.getElementById('checkout');
            if (checkoutSection) {
                // لو كل حاجة في صفحة واحدة
                switchTab('checkout');
            } else {
                // لو متقسمين لصفحات منفصلة بناءً على navbar.js
                window.location.href = '/pages/orders/checkout.html';
            }
        });
    }

    // Shipping Cost Logic
    const shippingMethod = document.getElementById('shippingMethod');
    const pickupPoints = document.getElementById('pickupPoints');
    if(shippingMethod) {
        shippingMethod.addEventListener('change', function() {
            const isPickup = this.value === 'pickup';
            pickupPoints.style.display = isPickup ? 'block' : 'none';
            document.getElementById('shippingPrice').innerText = isPickup ? '$2.00' : '$10.00';
            document.getElementById('totalPrice').innerText = isPickup ? '$54.50' : '$62.50';
        });
    }

    // --- FIX: Pay Now Button ---
    const payNowBtn = document.getElementById('payNowBtn');
    if (payNowBtn) {
        payNowBtn.addEventListener('click', async function() {
            payNowBtn.innerText = "Processing...";
            payNowBtn.disabled = true;

            const orderData = {
                buyer_id: 1, 
                shipping_street: "123 Main St",
                shipping_city: "Cairo",
                items: [{ listing_id: 1, quantity: 1 }]
            };

            const response = await fetchFromAPI('orders', 'POST', orderData);

            if(response && response.success) {
                window.currentOrderId = response.order_id;
                await fetchFromAPI('orders/pay', 'POST', { order_id: response.order_id });
                
                alert(`Success! Order #${response.order_id} secured in Escrow.`);
                
                // الانتقال لصفحة تفاصيل الطلب
                const orderDetailsSection = document.getElementById('orderDetails');
                if (orderDetailsSection) {
                    switchTab('orderDetails');
                } else {
                    window.location.href = '/pages/orders/order-details.html';
                }
            } else {
                alert("Order Failed: " + (response ? response.error : "Server Error"));
            }
            payNowBtn.innerText = "Pay Securely";
            payNowBtn.disabled = false;
        });
    }

    // Shipping Label Generation
    const shippingTabLink = document.querySelector('.nav-link[data-target="shipping"]');
    if (shippingTabLink) {
        shippingTabLink.addEventListener('click', async function() {
            if (!window.currentOrderId) return;
            const res = await fetchFromAPI('orders/ship', 'POST', { order_id: window.currentOrderId });
            if (res && res.success) {
                document.getElementById('trackingDisplay').innerText = res.tracking_number;
            }
        });
    }

    // Release Payment (Everything is OK)
    const everythingOkBtn = document.getElementById('everythingOkBtn');
    if (everythingOkBtn) {
        setTimeout(() => everythingOkBtn.disabled = false, 3000); 
        everythingOkBtn.addEventListener('click', async function() {
            if (!window.currentOrderId) return;
            everythingOkBtn.innerText = "Processing...";
            const res = await fetchFromAPI('orders/release', 'POST', { order_id: window.currentOrderId });
            if(res && res.success) {
                alert("Payment released to seller!");
                everythingOkBtn.innerText = "Order Completed";
                everythingOkBtn.disabled = true;
            }
        });
    }

    // ==========================================
    // 4. Admin Portal Logic
    // ==========================================
    async function loadUsers() {
        const usersTableBody = document.getElementById('usersTableBody');
        if(!usersTableBody) return;
        const users = await fetchFromAPI('admin/users', 'GET'); 
        if (users && Array.isArray(users)) {
            usersTableBody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.username || user.name}</td>
                    <td>${user.email}</td>
                    <td><b>${user.trust_score || 0}</b> pts</td>
                    <td><button class="btn btn-danger" onclick="deleteUser(${user.user_id})">Delete</button></td>
                </tr>
            `).join('');
        }
    }

    window.deleteUser = async function(userId) {
        if(confirm("Delete this user?")) {
            const res = await fetchFromAPI(`admin/user?user_id=${userId}`, 'DELETE'); 
            if(res && res.success) loadUsers();
        }
    };

    async function loadDisputes() {
        const disputesTableBody = document.getElementById('disputesTableBody');
        if(!disputesTableBody) return;
        const disputes = await fetchFromAPI('admin/disputes', 'GET');
        if (disputes && Array.isArray(disputes)) {
            disputesTableBody.innerHTML = disputes.map(d => `
                <tr>
                    <td>#${d.dispute_id}</td>
                    <td>${d.reason}</td>
                    <td>Order ID: ${d.order_id || 'N/A'}</td>
                    <td><span style="color: red;">${d.status}</span></td>
                    <td><button class="btn">Take Action</button></td>
                </tr>
            `).join('');
        }
    }

    async function loadReports() {
        const reportsTableBody = document.getElementById('reportsTableBody');
        if(!reportsTableBody) return;
        const reports = await fetchFromAPI('admin/reports', 'GET'); 
        if (reports && Array.isArray(reports)) {
            reportsTableBody.innerHTML = reports.map(report => `
                <tr>
                    <td>#${report.report_id}</td>
                    <td>${report.reason}</td>
                    <td>ID: ${report.listing_id || 'N/A'}</td>
                    <td><span style="font-weight:bold; color: ${report.status === 'pending' ? 'orange' : 'green'};">${report.status}</span></td>
                    <td>
                        <button class="btn btn-outline" onclick="updateReportStatus(${report.report_id}, 'resolved')">Resolve</button>
                    </td>
                </tr>
            `).join('');
        }
    }

    window.updateReportStatus = async function(id, status) {
        await fetchFromAPI(`admin/report?report_id=${id}`, 'PUT', { status: status }); 
        loadReports();
    };

    // Initialize Admin Data
    loadUsers();
    loadDisputes();
    loadReports();
});
