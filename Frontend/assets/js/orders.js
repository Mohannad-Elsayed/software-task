const API_BASE = 'http://localhost:8000/Backend/index.php?route=/api';

document.addEventListener("DOMContentLoaded", () => {
    const pendingOrdersBody = document.getElementById("pendingOrdersBody");
    const loadingState = document.getElementById("loadingState");
    const errorState = document.getElementById("errorState");
    const emptyState = document.getElementById("emptyState");
    const contentWrapper = document.getElementById("contentWrapper");

    const user = JSON.parse(localStorage.getItem('user') || '{}');

    async function loadPendingOrders() {
        if (!user.user_id) {
            window.location.href = '../auth/login.html';
            return;
        }

        try {
            loadingState.classList.remove("hidden");
            errorState.classList.add("hidden");
            emptyState.classList.add("hidden");
            contentWrapper.classList.add("hidden");

            const response = await fetch(`${API_BASE}/orders/pending&user_id=${user.user_id}`);
            if (!response.ok) throw new Error("Failed to fetch orders");

            const result = await response.json();
            const orders = result.data || [];

            if (orders.length === 0) {
                loadingState.classList.add("hidden");
                emptyState.classList.remove("hidden");
                return;
            }

            renderOrders(orders);
            loadingState.classList.add("hidden");
            contentWrapper.classList.remove("hidden");

        } catch (error) {
            console.error("Load failed:", error);
            loadingState.classList.add("hidden");
            errorState.classList.remove("hidden");
        }
    }

    function renderOrders(orders) {
        pendingOrdersBody.innerHTML = "";
        orders.forEach(order => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>#${order.order_id}</td>
                <td>${order.title || 'EcoSwap Order'}</td>
                <td style="font-weight: bold; color: var(--dark-green);">$${order.total_amount}</td>
                <td>
                    <div style="display: flex; gap: 10px;">
                        <button class="btn" onclick="payOrder(${order.order_id})">Pay</button>
                        <button class="btn btn-danger" onclick="cancelOrder(${order.order_id})">Cancel</button>
                    </div>
                </td>
            `;
            pendingOrdersBody.appendChild(tr);
        });
    }

    window.payOrder = (orderId) => {
        window.location.href = `payment.html?order_id=${orderId}`;
    };

    window.cancelOrder = async (orderId) => {
        const confirm = await Swal.fire({
            title: 'Cancel Order?',
            text: "This will unlock the items for other buyers.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!'
        });

        if (confirm.isConfirmed) {
            try {
                const res = await fetch(`${API_BASE}/orders/cancel`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId })
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire('Cancelled', 'Order has been cancelled.', 'success');
                    loadPendingOrders();
                } else {
                    Swal.fire('Error', data.error || 'Failed to cancel', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Connection failed', 'error');
            }
        }
    };

    loadPendingOrders();
});