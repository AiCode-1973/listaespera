<?php
/**
 * Endpoint para registrar envio de mensagem WhatsApp
 * Chamado via AJAX quando usuário envia mensagem
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/HistoricoMensagem.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$usuarioLogado = $auth->getUsuarioLogado();

// Apenas aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit();
}

// Recebe dados JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

// Validações (apenas telefone e mensagem são obrigatórios)
$erros = [];

if (empty($dados['telefone'])) {
    $erros[] = 'Telefone é obrigatório';
}

if (empty($dados['mensagem'])) {
    $erros[] = 'Mensagem é obrigatória';
}

if (!empty($erros)) {
    http_response_code(400);
    echo json_encode(['erro' => implode(', ', $erros)]);
    exit();
}

// Registra no histórico
$historicoModel = new HistoricoMensagem();

$dadosHistorico = [
    'fila_espera_id' => $dados['fila_espera_id'] ?? null,
    'paciente_id' => $dados['paciente_id'] ?? null,
    'usuario_id' => $usuarioLogado['id'],
    'telefone' => $dados['telefone'],
    'mensagem' => $dados['mensagem'],
    'tipo_mensagem' => $dados['tipo_mensagem'] ?? 'confirmacao_agendamento'
];

$id = $historicoModel->registrar($dadosHistorico);

if ($id) {
    http_response_code(200);
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Mensagem registrada no histórico',
        'id' => $id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao registrar mensagem no histórico']);
}
