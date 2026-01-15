<?php
/**
 * Deletar Registro da Fila de Espera
 * Apenas para Administradores
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/FilaEspera.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

// Verifica se é administrador
$auth->verificarPermissao(['administrador']);

// Verifica se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem_erro'] = 'ID do registro não especificado';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit();
}

$id = (int)$_GET['id'];

// Inicializa model
$filaModel = new FilaEspera();

// Busca o registro para ter detalhes antes de deletar (para log)
$registro = $filaModel->buscarPorId($id);

if (!$registro) {
    $_SESSION['mensagem_erro'] = 'Registro não encontrado';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit();
}

// Tenta deletar
try {
    if ($filaModel->deletar($id)) {
        $_SESSION['mensagem_sucesso'] = 'Registro excluído com sucesso! (Paciente: ' . htmlspecialchars($registro['nome_paciente']) . ')';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao excluir o registro. Tente novamente.';
    }
} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = 'Erro ao excluir: ' . $e->getMessage();
}

// Redireciona de volta para o dashboard
header('Location: ' . BASE_PATH . 'dashboard.php');
exit();
