<?php
/**
 * Flash Messages Helper
 * Provides a simple way to display one-time messages after redirects.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message.
 * @param string $type Message type: 'success', 'error', 'warning', 'info'
 * @param string $message The message content
 */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear all flash messages.
 * @return array Array of flash messages
 */
function flash_get(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Check if there are any flash messages.
 * @return bool
 */
function flash_has(): bool
{
    return !empty($_SESSION['flash_messages']);
}

/**
 * Render flash messages as HTML.
 * @return string HTML output
 */
function flash_render(): string
{
    $messages = flash_get();
    if (empty($messages)) {
        return '';
    }

    $html = '';
    foreach ($messages as $msg) {
        $type = htmlspecialchars($msg['type'], ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8');
        $html .= "<p class=\"flash {$type}\">{$text}</p>\n";
    }
    return $html;
}
