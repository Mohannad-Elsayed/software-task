<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Backend/app/Services/AdminService.php';

class AuthorizationTest extends TestCase {
    private $adminService;
    private $adminId;
    private $userId;

    protected function setUp(): void {
        $this->adminService = new AdminService();
        $conn = db();
        
        // Find the seeded admin and user
        $admin = $conn->query("SELECT user_id FROM User WHERE email = 'admin@example.com'")->fetch_assoc();
        $user = $conn->query("SELECT user_id FROM User WHERE email = 'user@example.com'")->fetch_assoc();
        
        $this->adminId = $admin['user_id'];
        $this->userId = $user['user_id'];
    }

    public function testAdminIsAuthorized() {
        $this->assertTrue($this->adminService->isUserAdmin($this->adminId));
    }

    public function testRegularUserIsNotAuthorized() {
        $this->assertFalse($this->adminService->isUserAdmin($this->userId));
    }

    public function testNonExistentUserIsNotAuthorized() {
        $this->assertFalse($this->adminService->isUserAdmin(999999));
    }
}
