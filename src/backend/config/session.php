<?php
// Session helper - Safe session management
// Use this instead of session_start() in API files

function ensureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Start session safely
ensureSession();
?>
