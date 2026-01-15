<?php
/**
 * Arquivo de Configuração do Sistema
 * Define constantes e configurações globais
 * Detecta automaticamente ambiente local ou produção
 */

// Detecta o ambiente baseado no host
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080']);

// Define o caminho base da aplicação automaticamente
if ($isLocal) {
    // Ambiente LOCAL (XAMPP, WAMP, etc)
    define('BASE_PATH', '/listaespera/');
    define('BASE_URL', 'http://localhost/listaespera');
    define('ENVIRONMENT', 'development');
} else {
    // Ambiente de PRODUÇÃO
    define('BASE_PATH', '/');
    define('BASE_URL', 'https://listasaude.aicode.dev.br');
    define('ENVIRONMENT', 'production');
}

// Configurações de exibição de erros
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
