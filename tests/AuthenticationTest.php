<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../Backend/app/Services/UserService.php';

class AuthenticationTest extends TestCase {
    private $userService;

    protected function setUp(): void {
        $this->userService = new UserService();
        // Clear test user if exists
        $conn = db();
        $conn->query("DELETE FROM User WHERE email = 'testauth@example.com'");
    }

    public function testUserRegistration() {
        $userId = $this->userService->createUser('testauth', 'testauth@example.com', 'password123');
        $this->assertNotFalse($userId);
        $this->assertIsInt($userId);

        $user = $this->userService->findByEmail('testauth@example.com');
        $this->assertEquals('testauth', $user['username']);
    }

    public function testUserLoginSuccessful() {
        // First create the user
        $this->userService->createUser('testauth', 'testauth@example.com', 'password123');

        // Try to find the user (as the AuthController would)
        $user = $this->userService->findByEmail('testauth@example.com');
        $this->assertNotNull($user);
        
        // Verify password
        $this->assertTrue(password_verify('password123', $user['password']));
    }

    public function testUserLoginFailed() {
        // First create the user
        $this->userService->createUser('testauth', 'testauth@example.com', 'password123');

        // Try to find the user
        $user = $this->userService->findByEmail('testauth@example.com');
        $this->assertNotNull($user);
        
        // Verify wrong password
        $this->assertFalse(password_verify('wrongpassword', $user['password']));
    }
}
