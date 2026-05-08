const API_BASE_URL = 'http://localhost:8000/software-task/Backend/routes/api.php';

async function request(endpoint, method = 'GET', body = null) {
    try {
        const response = await fetch(API_BASE_URL + endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: body ? JSON.stringify(body) : null
        });

        return await response.json();

    } catch (error) {
        console.error('Failed API request:', error);
        return {
            success: false,
            message: 'Failed to connect to backend'
        };
    }
}

async function testBackendConnection() {
    const data = await request('/api/health');
    console.log('Backend says:', data.message);
}

testBackendConnection();