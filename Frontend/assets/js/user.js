async function loadUserProfile() {
    const storedUser = JSON.parse(localStorage.getItem("user"));

    if (!storedUser) {
        window.location.href = "../../pages/auth/login.html";
        return;
    }

    const userId = storedUser.user_id;

    const data = await request(`/api/user/profile&user_id=${userId}`);

    if (!data.success) {
        console.error(data.message);
        return;
    }

    const user = data.user;

    document.getElementById("username").textContent = user.username;
    document.getElementById("email").textContent = user.email;
    document.getElementById("role").textContent = user.role_name || "user";
    document.getElementById("ecoPoints").textContent = user.eco_points || 0;
    document.getElementById("trustScore").textContent = (user.trust_score || 0) + "%";
}

loadUserProfile();