<?php
/**
 * Página de Logout
 */

require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();
