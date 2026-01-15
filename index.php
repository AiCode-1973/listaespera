<?php
/**
 * Página inicial - Redireciona para login ou dashboard
 */

require_once __DIR__ . '/config.php';
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_PATH . 'dashboard.php');
} else {
    header('Location: ' . BASE_PATH . 'login.php');
}
exit();
