const API_BASE_URL = 'http://localhost:8000/Backend/index.php?route=';

async function request(endpoint, method = "GET", body = null) {
    const url = API_BASE_URL + endpoint;

    console.log("API CALL:", method, url);
    if (body) console.log("REQUEST BODY:", body);

    try {
        const response = await fetch(url, {
            method,
            headers: { "Content-Type": "application/json" },
            body: body ? JSON.stringify(body) : null
        });

        const text = await response.text();

        console.log("STATUS:", response.status);
        console.log("RAW RESPONSE:", text);

        try {
            return JSON.parse(text);
        } catch {
            return {
                success: false,
                message: "Backend returned invalid JSON",
                raw: text,
                url: url
            };
        }

    } catch (error) {
        console.error("FAILED API REQUEST:", error);
        return {
            success: false,
            message: "Failed to connect to backend",
            error: error.message,
            url: url
        };
    }
}

async function testBackendConnection() {
    const data = await request('/api/health');
    console.log('Backend says:', data.message);
}

testBackendConnection();