document.addEventListener("DOMContentLoaded", () => {
    loadNotifications();
});

async function loadNotifications() {
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    const container = document.getElementById("notificationsList");

    console.log("Logged user:", user);

    if (!container) {
        console.log("notificationsList container not found");
        return;
    }

    if (!user.user_id) {
        container.innerHTML = `<div class="empty-state">Please login first.</div>`;
        return;
    }

    const result = await request(`/api/community/notifications&user_id=${user.user_id}`);

    console.log("Fetched notifications:", result);

    if (!result.success) {
        container.innerHTML = `<div class="empty-state">Failed to load notifications.</div>`;
        return;
    }

    const notifications = result.notifications || [];

    if (notifications.length === 0) {
        container.innerHTML = `<div class="empty-state">No notifications yet.</div>`;
        return;
    }

    container.innerHTML = notifications.map(n => `
        <div class="notification-card ${Number(n.is_read) === 0 ? "unread" : ""}">
            <div>
                <div class="notification-type">${n.type || "Notification"}</div>
                <div class="notification-message">${n.message}</div>
                <div class="notification-date">${n.created_at}</div>
            </div>

            <button 
                class="read-btn"
                ${Number(n.is_read) === 1 ? "disabled" : ""}
                onclick="markAsRead(${n.notification_id})"
            >
                ${Number(n.is_read) === 1 ? "Read" : "Mark as read"}
            </button>
        </div>
    `).join("");
}

async function markAsRead(notificationId) {
    const user = JSON.parse(localStorage.getItem("user") || "{}");

    await request(`/api/community/notifications/${notificationId}/read`, "PUT", {
        user_id: user.user_id
    });

    loadNotifications();
}