<?php
/**
 * CSRF Protection Helper
 * Generates and validates CSRF tokens to protect forms against cross-site request forgery.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a new CSRF token or return the existing one.
 * @return string The CSRF token
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate an HTML hidden input with the CSRF token.
 * @return string HTML input element
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Validate the submitted CSRF token.
 * @param string|null $token The token from the form submission
 * @return bool True if valid, false otherwise
 */
function csrf_validate(?string $token): bool
{
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate the CSRF token (call after successful form submission).
 */
function csrf_regenerate(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
