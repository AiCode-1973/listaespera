<?php
/**
 * Endpoint para excluir mensagem do histórico
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/HistoricoMensagem.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();
$usuarioLogado = $auth->getUsuarioLogado();

// Define header JSON
header('Content-Type: application/json');

// Verifica método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit();
}

// Recebe dados JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

// Validação
if (empty($dados['id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID da mensagem é obrigatório']);
    exit();
}

try {
    $historicoModel = new HistoricoMensagem();
    
    // Verifica se a mensagem existe antes de excluir
    $mensagem = $historicoModel->buscarPorId($dados['id']);
    
    if (!$mensagem) {
        http_response_code(404);
        echo json_encode(['erro' => 'Mensagem não encontrada']);
        exit();
    }
    
    // Exclui a mensagem
    if ($historicoModel->excluir($dados['id'])) {
        http_response_code(200);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Mensagem excluída com sucesso'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao excluir mensagem']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
}
