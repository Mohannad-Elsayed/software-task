//login
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    const email = document.querySelector('input[type="email"]');
    const password = document.querySelector('input[type="password"]');

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        if (email.value === "" || password.value === "") {
            alert("Please fill all fields.");
            return;
        }

        try {
            const response = await fetch("/api/auth/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    email: email.value,
                    password: password.value
                })
            });

            const data = await response.json();

            if (data.success) {
                alert("Login Successful!");
                window.location.href = "UserPage.html";
            } else {
                alert(data.message || "Invalid Email or Password");
            }

        } catch (error) {
            alert("Something went wrong. Please try again.");
            console.error("Login error:", error);
        }
    });

});

//register
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registerForm");

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const name            = document.getElementById("name").value.trim();
        const email           = document.getElementById("email").value.trim();
        const password        = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();

        // Frontend validation
        if (name === "" || email === "" || password === "" || confirmPassword === "") {
            alert("Please fill all fields.");
            return;
        }

        if (password.length < 6) {
            alert("Password must be at least 6 characters.");
            return;
        }

        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return;
        }

        try {
            const response = await fetch("/api/auth/register", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    name:     name,
                    email:    email,
                    password: password
                })
            });

            const data = await response.json();

            if (data.success) {
                alert("Registration Successful!");
                window.location.href = "../auth/login.html";
            } else {
                alert(data.message || "Registration failed. Please try again.");
            }

        } catch (error) {
            alert("Something went wrong. Please try again.");
            console.error("Register error:", error);
        }
    });

});