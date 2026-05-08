const API_BASE_URL = 'http://localhost:8000';

async function request(endpoint, method = "GET", body = null) {
    const options = {
        method,
        headers: { "Content-Type": "application/json" },
    };
    if (body) options.body = JSON.stringify(body);

    const response = await fetch(`${API_BASE_URL}/api${endpoint}`, options);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return response.json();
}

async function loadUser() {
    try {
        const user = await request("/user");

        document.getElementById("username").textContent = user.name;
        document.getElementById("email").textContent = user.email;
        document.getElementById("role").textContent = user.role;
        document.getElementById("ecoPoints").textContent = user.ecoPoints;
        document.getElementById("trustScore").textContent = user.trustScore + "%";

    } catch (err) {
        console.error("Failed to load user", err);
    }
}

loadUser();