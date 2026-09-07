<?php
// admin/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAdminAuth() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}
