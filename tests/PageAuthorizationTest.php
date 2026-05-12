<?php
use PHPUnit\Framework\TestCase;

class PageAuthorizationTest extends TestCase {
    private $pageUrl = "http://localhost:8000/Frontend/pages/admin/admin-dashboard.html";

    public function testAdminDashboardRouteReturnsThePage() {
        $response = $this->makeRequest($this->pageUrl);
        
        $this->assertEquals(200, $response['status']);
        $this->assertStringContainsString('<title>Admin Dashboard | EcoSwap</title>', $response['body']);
    }

    public function testAdminDashboardRouteIncludesLogoutRedirect() {
        $response = $this->makeRequest($this->pageUrl);
        
        $this->assertEquals(200, $response['status']);
        $this->assertStringContainsString('localStorage.removeItem("user")', $response['body']);
        $this->assertStringContainsString('../../pages/auth/login.html', $response['body']);
    }

    public function testAdminDashboardRouteIncludesDashboardContent() {
        $response = $this->makeRequest($this->pageUrl);
        
        $this->assertEquals(200, $response['status']);
        $this->assertStringContainsString('<section id="users">', $response['body']);
        $this->assertStringContainsString('../../assets/js/admin.js', $response['body']);
    }

    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $body = substr($response, $headerSize);
        
        return [
            'status' => $status,
            'body' => $body
        ];
    }
}
