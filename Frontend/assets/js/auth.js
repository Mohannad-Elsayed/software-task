const registerForm = document.getElementById("registerForm");

if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const username = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();

        if (password !== confirmPassword) {
            alert("Passwords do not match");
            return;
        }

        const result = await request("/api/auth/register", "POST", {
            username: username,
            email: email,
            password: password
        });

        console.log("Register result:", result);
        alert(result.message);

        if (result.success) {
            window.location.href = "../../pages/auth/login.html";
        }
    });
}

const loginForm = document.getElementById("loginForm");

if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const email = document.getElementById("loginEmail").value.trim();
        const password = document.getElementById("loginPassword").value.trim();

        const result = await request("/api/auth/login", "POST", {
            email: email,
            password: password
        });

        console.log("Login result:", result);
        alert(result.message);

        if (result.success) {
            localStorage.setItem("user", JSON.stringify(result.user));
            localStorage.setItem("user_id", result.user.user_id);

            if (result.user.role === "admin") {
                window.location.href = "../../pages/admin/admin-dashboard.html";
            } else {
                window.location.href = "../../pages/user/profile.html";
            }
        }
    });
}