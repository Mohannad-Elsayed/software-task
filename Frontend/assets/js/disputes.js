/**
 * Dispute Module
 * Handles fetching user orders and submitting disputes.
 */

document.addEventListener("DOMContentLoaded", async () => {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (!user.user_id) {
        window.location.href = '../auth/login.html';
        return;
    }

    const orderSelect = document.getElementById('orderSelect');
    const disputeForm = document.getElementById('disputeForm');
    const noOrdersMessage = document.getElementById('noOrdersMessage');

    try {
        // Fetch all orders for the current user
        const orders = await request(`/api/orders/buyer&user_id=${user.user_id}`);
        
        if (orders.status === 'success' && orders.data.length > 0) {
            orderSelect.innerHTML = '<option value="" disabled selected>-- Choose an Order --</option>';
            orders.data.forEach(order => {
                const option = document.createElement('option');
                option.value = order.order_id;
                // Format: Order #123 - [First Item Title] ($50.00)
                option.textContent = `Order #${order.order_id} - ${order.title || 'Multiple Items'} ($${parseFloat(order.total_amount).toFixed(2)})`;
                orderSelect.appendChild(option);
            });
        } else {
            disputeForm.style.display = 'none';
            noOrdersMessage.style.display = 'block';
        }
    } catch (err) {
        console.error("Failed to load orders:", err);
        Swal.fire('Error', 'Could not load your orders. Please try again later.', 'error');
    }
});

document.getElementById('disputeForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const orderId = document.getElementById('orderSelect').value;
    const reason = document.getElementById('disputeReason').value;
    const submitBtn = document.getElementById('submitBtn');

    if (!orderId) {
        Swal.fire('Warning', 'Please select an order to dispute.', 'warning');
        return;
    }

    try {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const result = await request('/api/disputes', 'POST', {
            initiator_id: user.user_id,
            order_id: parseInt(orderId),
            reason: reason
        });

        if (result.success) {
            Swal.fire({
                title: 'Dispute Submitted',
                text: 'Your dispute has been recorded. An administrator will review it shortly.',
                icon: 'success'
            }).then(() => {
                window.location.href = '../user/profile.html';
            });
        } else {
            Swal.fire('Error', result.error || 'Failed to submit dispute', 'error');
        }
    } catch (err) {
        console.error("Submission error:", err);
        Swal.fire('Error', 'Connection failed. Please check your internet.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Dispute';
    }
});
