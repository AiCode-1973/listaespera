<?php
/**
 * Exportação de Lista de Espera para CSV
 */

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/FilaEspera.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

// Inicializa model
$filaModel = new FilaEspera();

// Filtros (mesmos do dashboard)
$filtros = [
    'medico_id' => $_GET['medico_id'] ?? '',
    'especialidade_id' => $_GET['especialidade_id'] ?? '',
    'convenio_id' => $_GET['convenio_id'] ?? '',
    'agendado' => $_GET['agendado'] ?? '',
    'nome_paciente' => $_GET['nome_paciente'] ?? '',
    'data_solicitacao_inicio' => isset($_GET['data_solicitacao_inicio']) ? $_GET['data_solicitacao_inicio'] : '',
    'data_solicitacao_fim' => isset($_GET['data_solicitacao_fim']) ? $_GET['data_solicitacao_fim'] : ''
];

// Remove filtros vazios
$filtros = array_filter($filtros, function($value) {
    return $value !== '';
});

// Busca todos os registros (sem paginação)
$registros = $filaModel->exportar($filtros);

// Configura headers para download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=lista_espera_' . date('Y-m-d_His') . '.csv');

// Abre output como arquivo
$output = fopen('php://output', 'w');

// BOM para UTF-8 (Excel reconhecer caracteres especiais)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos do CSV
fputcsv($output, [
    'ID',
    'Médico',
    'Especialidade',
    'Convênio',
    'Nome Paciente',
    'CPF',
    'Data Nascimento',
    'Data Solicitação',
    'Informação',
    'Observação',
    'Agendado',
    'Data Agendamento',
    'Telefone 1',
    'Telefone 2',
    'Agendado Por'
], ';');

// Dados
foreach ($registros as $reg) {
    fputcsv($output, [
        $reg['id'],
        $reg['medico_nome'] ?? '',
        $reg['especialidade_nome'],
        $reg['convenio_nome'] ?? '',
        $reg['nome_paciente'],
        formatarCPF($reg['cpf']),
        formatarData($reg['data_nascimento']),
        formatarData($reg['data_solicitacao']),
        $reg['informacao'] ?? '',
        $reg['observacao'] ?? '',
        $reg['agendado'] ? 'Sim' : 'Não',
        $reg['agendado'] ? formatarData($reg['data_agendamento']) : '',
        formatarTelefone($reg['telefone1']),
        $reg['telefone2'] ? formatarTelefone($reg['telefone2']) : '',
        $reg['agendado_por'] ?? ''
    ], ';');
}

fclose($output);
exit();
