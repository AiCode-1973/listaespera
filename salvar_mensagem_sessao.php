<?php
/**
 * Salva mensagem WhatsApp na sessão para registro posterior
 */

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit();
}

// Recebe dados JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

// Validações
if (empty($dados['telefone']) || empty($dados['mensagem'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Telefone e mensagem são obrigatórios']);
    exit();
}

// Salva na sessão
$_SESSION['mensagem_whatsapp_pendente'] = [
    'telefone' => $dados['telefone'],
    'mensagem' => $dados['mensagem'],
    'data_hora' => date('Y-m-d H:i:s')
];

http_response_code(200);
echo json_encode([
    'sucesso' => true,
    'mensagem' => 'Mensagem salva. Será registrada ao salvar o cadastro.'
]);
