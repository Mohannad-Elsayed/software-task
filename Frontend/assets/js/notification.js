document.addEventListener("DOMContentLoaded", loadNotifications);

async function loadNotifications() {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    const container = document.getElementById("notificationsList");

    if (!user.user_id) {
        container.innerHTML = "<p>Please login first.</p>";
        return;
    }

    const result = await request(`/api/notifications&user_id=${user.user_id}`);

    const notifications = result.notifications || [];

    if (!notifications.length) {
        container.innerHTML = "<p>No notifications yet.</p>";
        return;
    }

    container.innerHTML = notifications.map(n => `
        <div class="card" style="margin-bottom:12px; padding:16px;">
            <h3>${n.type}</h3>
            <p>${n.message}</p>
            <small>${n.created_at}</small>
            <br>
            <button onclick="markAsRead(${n.notification_id})">
                ${n.is_read == 1 ? "Read" : "Mark as read"}
            </button>
        </div>
    `).join("");
}

async function markAsRead(notificationId) {
    const user = JSON.parse(localStorage.getItem("user") || "{}");

    await request(`/api/notifications/${notificationId}/read`, "PUT", {
        user_id: user.user_id
    });

    loadNotifications();
}