<?php
// Shared admin guard — include at the top of every admin page
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
