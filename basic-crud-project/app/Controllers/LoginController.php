<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Login Controller for basic authentication
 * 
 * Note: In a real-world application, this would use a proper User model and entity
 * with database storage, password hashing, and proper security measures.
 * For this technical test, we're using hardcoded user data for simplicity.
 */
class LoginController extends BaseController
{
    /**
     * Hardcoded user data for testing purposes
     * In production, this would come from a UserModel and database
     */
    private const HARDCODED_USER = [
        'id' => 1,
        'username' => 'admin',
        'password' => 'admin123' // In production, this would be hashed
    ];

    /**
     * Display login form
     *
     * @return string|ResponseInterface
     */
    public function index()
    {
        // If user is already logged in, redirect to products
        if ($this->isLoggedIn()) {
            return redirect()->to('/');
        }

        return view('auth/login');
    }

    /**
     * Process login form submission
     *
     * @return ResponseInterface
     */
    public function login(): ResponseInterface
    {
        // If user is already logged in, redirect to products
        if ($this->isLoggedIn()) {
            return redirect()->to('/');
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($username) || empty($password)) {
            session()->setFlashdata('error', 'Por favor, ingrese usuario y contraseña.');
            return redirect()->back()->withInput();
        }

        // Check credentials against hardcoded user
        if ($this->validateCredentials($username, $password)) {
            // Set session data for logged in user
            $this->setUserSession(self::HARDCODED_USER);
            
            session()->setFlashdata('success', '¡Bienvenido! Has iniciado sesión correctamente.');
            return redirect()->to('/');
        } else {
            session()->setFlashdata('error', 'Credenciales incorrectas. Por favor, intente nuevamente.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Process logout
     *
     * @return ResponseInterface
     */
    public function logout(): ResponseInterface
    {
        // Clear user session data
        $this->clearUserSession();
        
        session()->setFlashdata('success', 'Has cerrado sesión correctamente.');
        return redirect()->to('/login');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Validate user credentials
     * In production, this would hash the password and compare with database
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    private function validateCredentials(string $username, string $password): bool
    {
        return $username === self::HARDCODED_USER['username'] && 
               $password === self::HARDCODED_USER['password'];
    }

    /**
     * Set user session data
     *
     * @param array $user
     * @return void
     */
    private function setUserSession(array $user): void
    {
        $sessionData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'is_logged_in' => true,
            'login_time' => time()
        ];

        session()->set($sessionData);
    }

    /**
     * Clear user session data
     *
     * @return void
     */
    private function clearUserSession(): void
    {
        session()->remove(['user_id', 'username', 'is_logged_in', 'login_time']);
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return session()->get('is_logged_in') === true;
    }

    /**
     * Get current logged in user data
     *
     * @return array|null
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => session()->get('user_id'),
            'username' => session()->get('username'),
            'login_time' => session()->get('login_time')
        ];
    }

    /**
     * Middleware method to check authentication for protected routes
     * This can be used in other controllers to protect API endpoints
     *
     * @return bool
     */
    public static function requireAuth(): bool
    {
        $session = session();
        return $session->get('is_logged_in') === true;
    }
}
