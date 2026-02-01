<?php
/**
 * CSRF Protection Utility
 * Generates and validates CSRF tokens for forms
 */

namespace App\Utils;

class CSRF
{
    private static string $tokenName = 'csrf_token';
    private static int $tokenExpiry = 3600; // 1 hour

    /**
     * Generate a new CSRF token
     */
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$tokenName] = [
            'token' => $token,
            'expires' => time() + self::$tokenExpiry
        ];
        $_SESSION['csrf_token'] = $token; // Also store in simple session key

        return $token;
    }

    /**
     * Validate CSRF token from form
     */
    public static function validate(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check primary token storage
        if (isset($_SESSION[self::$tokenName])) {
            $stored = $_SESSION[self::$tokenName];

            // Ensure stored value is an array with expected keys
            if (!is_array($stored) || !isset($stored['expires']) || !isset($stored['token'])) {
                // Fallback to simple session storage
                if (isset($_SESSION['csrf_token'])) {
                    return hash_equals($_SESSION['csrf_token'], $token);
                }
                return false;
            }

            // Check if token is expired
            if (time() > $stored['expires']) {
                unset($_SESSION[self::$tokenName]);
                return false;
            }

            // Verify token match (timing attack safe)
            if (!hash_equals($stored['token'], $token)) {
                return false;
            }

            return true;
        }

        // Fallback to simple session storage
        if (isset($_SESSION['csrf_token'])) {
            return hash_equals($_SESSION['csrf_token'], $token);
        }

        return false;
    }

    /**
     * Get current token without regenerating
     */
    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check primary token storage first
        if (isset($_SESSION[self::$tokenName])) {
            $stored = $_SESSION[self::$tokenName];
            // Ensure stored value is an array with expected keys
            if (is_array($stored) && isset($stored['expires']) && isset($stored['token'])) {
                if (time() <= $stored['expires']) {
                    return $stored['token'];
                }
            }
        }

        // Check fallback storage
        if (isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])) {
            return $_SESSION['csrf_token'];
        }

        return null;
    }

    /**
     * Generate HTML input for CSRF token
     */
    public static function getInput(): string
    {
        $token = self::getToken() ?? self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Check if current request is a form submission with valid CSRF
     */
    public static function isValidPost(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token)) {
            return false;
        }

        return self::validate($token);
    }

    /**
     * Regenerate token (call after successful validation)
     */
    public static function regenerate(): string
    {
        return self::generate();
    }
}

