document.addEventListener("DOMContentLoaded", function() {

    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.section-tab');

    if(toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            sections.forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            
            const targetSection = document.getElementById(this.getAttribute('data-target'));
            if(targetSection) targetSection.classList.add('active');
        });
    });

    const proceedToCheckoutBtn = document.getElementById('proceedToCheckout');
    if(proceedToCheckoutBtn) {
        proceedToCheckoutBtn.addEventListener('click', function() {
            const checkoutTab = document.querySelector('.nav-link[data-target="checkout"]');
            if (checkoutTab) checkoutTab.click();
        });
    }
    const shippingMethod = document.getElementById('shippingMethod');
    const pickupPoints = document.getElementById('pickupPoints');
    const shippingPriceLabel = document.getElementById('shippingPrice');
    const totalPriceLabel = document.getElementById('totalPrice');

    if(shippingMethod) {
        shippingMethod.addEventListener('change', function() {
            if(this.value === 'pickup') {
                pickupPoints.style.display = 'block';
                if(shippingPriceLabel) shippingPriceLabel.innerText = '$2.00';
                if(totalPriceLabel) totalPriceLabel.innerText = '$54.50';
            } else {
                pickupPoints.style.display = 'none';
                if(shippingPriceLabel) shippingPriceLabel.innerText = '$10.00';
                if(totalPriceLabel) totalPriceLabel.innerText = '$62.50';
            }
        });
    }
    const payNowBtn = document.getElementById('payNowBtn');
    if (payNowBtn) {
        payNowBtn.addEventListener('click', function() {
            payNowBtn.innerText = "Processing...";
            payNowBtn.disabled = true;
          
            setTimeout(() => {
                alert("Order Placed Successfully!");
                const orderDetailsTab = document.querySelector('.nav-link[data-target="orderDetails"]');
                if (orderDetailsTab) orderDetailsTab.click();
                payNowBtn.innerText = "Pay Securely";
                payNowBtn.disabled = false;
            }, 1000);
        });
    }

  
    const everythingOkBtn = document.getElementById('everythingOkBtn');
    if (everythingOkBtn) {
        setTimeout(() => everythingOkBtn.disabled = false, 3000); 

        everythingOkBtn.addEventListener('click', function() {
            alert("Success! Funds have been released to the seller.");
            everythingOkBtn.innerText = "Order Completed";
            everythingOkBtn.disabled = true;
        });
    }

    const API_BASE_URL = 'http://localhost:8000/software-task/api/'; 

    async function fetchFromAPI(endpoint, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: { 'Content-Type': 'application/json' }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(API_BASE_URL + endpoint, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error("API Error in endpoint:", endpoint, error);
            return null;
        }
    }


    async function loadUsers() {
        const usersTableBody = document.getElementById('usersTableBody');
        if(!usersTableBody) return;

        const users = await fetchFromAPI('admin/users', 'GET');
        
        if (users && Array.isArray(users)) {
            usersTableBody.innerHTML = '';
            users.forEach(user => {
                usersTableBody.innerHTML += `
                    <tr>
                        <td>${user.name || 'User ' + user.user_id}</td>
                        <td>${user.role || 'N/A'}</td>
                        <td>${user.status || 'Active'}</td>
                        <td>
                            <button class="btn btn-danger" onclick="deleteUser(${user.user_id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }
    }

    window.deleteUser = async function(userId) {
        if(confirm("Are you sure you want to delete this user?")) {
            const response = await fetchFromAPI(`admin/user?user_id=${userId}`, 'DELETE');
            if(response && response.success) {
                alert("User deleted successfully!");
                loadUsers();
            } else {
                alert("Failed to delete user.");
            }
        }
    };

    async function loadReports() {
        const disputesTableBody = document.getElementById('disputesTableBody');
        if(!disputesTableBody) return;

        const reports = await fetchFromAPI('admin/reports', 'GET');

        if (reports && Array.isArray(reports)) {
            disputesTableBody.innerHTML = '';
            reports.forEach(report => {
                disputesTableBody.innerHTML += `
                    <tr>
                        <td>#${report.report_id}</td>
                        <td>${report.reason || 'No reason provided'}</td>
                        <td>Listing ID: ${report.listing_id || 'N/A'}</td>
                        <td><strong>${report.status || 'pending'}</strong></td>
                        <td>
                            <button class="btn" onclick="updateReportStatus(${report.report_id}, 'resolved_refund')">Refund Buyer</button>
                            <button class="btn btn-outline" onclick="updateReportStatus(${report.report_id}, 'resolved_release')">Release to Seller</button>
                        </td>
                    </tr>
                `;
            });
        }
    }

    window.updateReportStatus = async function(reportId, newStatus) {
        if(confirm("Are you sure you want to take this action?")) {
            const response = await fetchFromAPI(`admin/report?report_id=${reportId}`, 'PUT', { status: newStatus });
            if(response && response.success) {
                alert("Report status updated successfully!");
                loadReports();
            } else {
                alert("Failed to update report status.");
            }
        }
    };
    loadUsers();
    loadReports();

});
