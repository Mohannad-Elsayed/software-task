document.addEventListener("DOMContentLoaded", async function () {
    const storedUser = JSON.parse(localStorage.getItem("user"));

    if (!storedUser || storedUser.role !== "admin") {
        window.location.href = "../../pages/auth/login.html";
        return;
    }

    await loadDashboardStats();
    await loadUsers();
    await loadReports();
    await loadDisputes();
});

async function loadDashboardStats() {
    const usersData = await request("/api/admin/users");

    if (!usersData.success) return;

    const users = usersData.users || [];

    document.getElementById("activeUsersCount").textContent = users.length;

    document.getElementById("totalSalesAmount").textContent =
        "$" + users.length * 250;

    document.getElementById("escrowFundsAmount").textContent =
        "$" + users.length * 40;
}

async function loadUsers() {
    const data = await request("/api/admin/users");

    if (!data.success) return;

    const users = data.users || [];
    const table = document.getElementById("usersTableBody");

    if (!table) return;

    table.innerHTML = "";

    users.forEach(user => {
        table.innerHTML += `
            <tr>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.trust_score || 0}</td>
                <td>
                    <button class="btn btn-danger" onclick="deleteUser(${user.user_id})">
                        Delete
                    </button>
                </td>
            </tr>
        `;
    });
}

async function deleteUser(userId) {
    const confirmDelete = confirm("Delete this user?");

    if (!confirmDelete) return;

    const result = await request(
        `/api/admin/user&user_id=${userId}`,
        "DELETE"
    );

    if (result.success) {
        alert("User deleted successfully");
        await loadUsers();
        await loadDashboardStats();
    } else {
        alert(result.message || "Failed to delete user");
    }
}

async function loadReports() {
    const data = await request("/api/admin/reports");

    if (!data.success) return;

    const reports = data.reports || [];
    const table = document.getElementById("reportsTableBody");

    if (!table) return;

    table.innerHTML = "";

    if (reports.length === 0) {
        table.innerHTML = `
            <tr>
                <td colspan="5">No reports found</td>
            </tr>
        `;
        return;
    }

    reports.forEach(report => {
        table.innerHTML += `
            <tr>
                <td>#${report.report_id}</td>
                <td>${report.reason}</td>
                <td>${report.listing_id || "N/A"}</td>
                <td>${report.status}</td>
                <td>
                ${report.status === "pending" ? `
                    <button
                        class="btn btn-outline"
                        onclick="updateReportStatus(${report.report_id}, 'resolved')"
                    >
                        Resolve
                    </button>
                    <button
                        class="btn btn-danger"
                        onclick="updateReportStatus(${report.report_id}, 'rejected')"
                    >
                        Reject
                    </button>

                ` : `
                    <span>
                        Completed
                    </span>
                `}

                </td>
            </tr>
        `;
    });
}

async function loadDisputes() {

    const data =
        await request("/api/admin/disputes");

    if (!data.success) return;

    const disputes =
        data.disputes || [];

    const table =
        document.getElementById(
            "disputesTableBody"
        );

    if (!table) return;

    table.innerHTML = "";

    if (disputes.length === 0) {

        table.innerHTML = `
            <tr>
                <td colspan="5">
                    No disputes found
                </td>
            </tr>
        `;

        return;
    }

    disputes.forEach(dispute => {

        table.innerHTML += `

            <tr>

                <td>
                    #${dispute.dispute_id}
                </td>

                <td>
                    ${dispute.reason}
                </td>

                <td>
                    ${dispute.order_id || "N/A"}
                </td>

                <td>
                    ${dispute.status}
                </td>

                <td>

                    <button
                        class="btn btn-outline"
                        onclick="resolveDispute(${dispute.dispute_id})"
                    >
                        Resolve
                    </button>

                </td>

            </tr>
        `;
    });
}

async function updateReportStatus(reportId, status) {
    const result = await request(
        `/api/admin/report&report_id=${reportId}`,
        "POST",
        { status: status }
    );

    if (result.success) {
        alert("Report updated");
        await loadReports();
    } else {
        alert(result.message || "Failed to update report");
    }
}

async function resolveDispute(disputeId) {

    const result = await request(
        "/api/admin/dispute/resolve",
        "POST",
        {
            dispute_id: disputeId
        }
    );

    if (result.success) {

        alert("Dispute resolved");

        loadDisputes();

    } else {

        alert(
            result.message ||
            "Failed to resolve dispute"
        );
    }
}