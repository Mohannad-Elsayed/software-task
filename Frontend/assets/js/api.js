const API_BASE_URL = 'http://localhost:8000';

async function testBackendConnection() {
    try {
        const response = await fetch(`${API_BASE_URL}/api/test`);
        const data = await response.json();
        console.log('Backend says:', data.message);
        // You can display this on the page if you like
    } catch (error) {
        console.error('Failed to connect to backend:', error);
    }
}

// Automatically test connection when api.js loads
testBackendConnection();
