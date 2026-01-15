<?php
/**
 * Controller de Autenticação
 * Gerencia login, logout e verificação de sessão
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/functions.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
        
        // Inicia sessão se não estiver iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Processa login
     */
    public function login($email, $senha) {
        // Valida campos
        if (empty($email) || empty($senha)) {
            return [
                'sucesso' => false,
                'mensagem' => 'E-mail e senha são obrigatórios'
            ];
        }

        // Tenta autenticar
        $usuario = $this->usuarioModel->autenticar($email, $senha);

        if ($usuario) {
            // Cria sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];
            
            // Regenera ID da sessão para segurança
            session_regenerate_id(true);

            return [
                'sucesso' => true,
                'mensagem' => 'Login realizado com sucesso',
                'redirect' => BASE_PATH . 'dashboard.php'
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => 'E-mail ou senha incorretos'
        ];
    }

    /**
     * Processa logout
     */
    public function logout() {
        // Destrói todas as variáveis de sessão
        $_SESSION = array();

        // Destrói o cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        // Destrói a sessão
        session_destroy();

        // Redireciona para login
        redirecionar(BASE_PATH . 'login.php');
    }

    /**
     * Verifica se usuário está logado
     */
    public function verificarAutenticacao() {
        if (!isset($_SESSION['usuario_id'])) {
            redirecionar(BASE_PATH . 'login.php');
        }
    }

    /**
     * Verifica se usuário tem permissão (perfil específico)
     */
    public function verificarPermissao($perfisPermitidos = []) {
        $this->verificarAutenticacao();

        if (!empty($perfisPermitidos) && !in_array($_SESSION['usuario_perfil'], $perfisPermitidos)) {
            $_SESSION['mensagem_erro'] = 'Você não tem permissão para acessar esta área';
            redirecionar(BASE_PATH . 'dashboard.php');
        }
    }

    /**
     * Retorna informações do usuário logado
     */
    public function getUsuarioLogado() {
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nome' => $_SESSION['usuario_nome'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'perfil' => $_SESSION['usuario_perfil'] ?? ''
        ];
    }

    /**
     * Verifica se é administrador
     */
    public function isAdmin() {
        return isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'administrador';
    }

    /**
     * Verifica se é recepção
     */
    public function isRecepcao() {
        return isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'recepcao';
    }

    /**
     * Verifica se é médico
     */
    public function isMedico() {
        return isset($_SESSION['usuario_perfil']) && $_SESSION['usuario_perfil'] === 'medico';
    }
}
