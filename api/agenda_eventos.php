<?php
/**
 * API - Eventos da Agenda
 * Retorna eventos agendados em formato JSON para FullCalendar
 */

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../models/FilaEspera.php';
require_once __DIR__ . '/../includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$usuarioLogado = $auth->getUsuarioLogado();

// Verifica se é administrador, recepção ou atendente
if (!in_array($usuarioLogado['perfil'], ['administrador', 'recepcao', 'atendente'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

// Define header JSON
header('Content-Type: application/json; charset=utf-8');

try {
    $filaModel = new FilaEspera();
    
    // Busca apenas registros agendados
    $filtros = ['agendado' => '1'];
    $registros = $filaModel->listar($filtros, 10000, 0); // Limite alto para pegar todos
    
    $eventos = [];
    
    foreach ($registros as $reg) {
        // Pula se não tiver data de agendamento
        if (empty($reg['data_agendamento'])) {
            continue;
        }
        
        // Define cor baseada no tipo de atendimento
        $cor = '#3b82f6'; // Azul padrão
        switch ($reg['tipo_atendimento']) {
            case 'Consulta':
                $cor = '#10b981'; // Verde
                break;
            case 'Exame':
                $cor = '#3b82f6'; // Azul
                break;
            case 'Consulta + Exame':
                $cor = '#8b5cf6'; // Roxo
                break;
            case 'Retorno':
                $cor = '#eab308'; // Amarelo
                break;
            case 'Cirurgia':
                $cor = '#ef4444'; // Vermelho
                break;
            case 'Procedimento':
                $cor = '#f97316'; // Laranja
                break;
        }
        
        // Se for urgente, cor mais escura e borda
        if ($reg['urgente']) {
            $cor = '#b91c1c'; // Vermelho escuro
        }
        
        // Formata data para exibição
        $dataFormatada = formatarData($reg['data_agendamento']);
        
        // Prepara tooltip
        $tooltip = $reg['nome_paciente'] . ' - ' . $reg['tipo_atendimento'];
        if ($reg['urgente']) {
            $tooltip = '🚨 URGENTE - ' . $tooltip;
        }
        
        // Cria evento
        $evento = [
            'id' => $reg['id'],
            'title' => $reg['nome_paciente'],
            'start' => $reg['data_agendamento'], // Formato YYYY-MM-DD
            'color' => $cor,
            'extendedProps' => [
                'id' => $reg['id'],
                'medico' => $reg['medico_nome'] ?? 'N/A',
                'especialidade' => $reg['especialidade_nome'] ?? 'N/A',
                'convenio' => $reg['convenio_nome'] ?? 'Particular',
                'tipoAtendimento' => $reg['tipo_atendimento'] ?? 'N/A',
                'telefone' => formatarTelefone($reg['telefone1']),
                'cpf' => formatarCPF($reg['cpf']),
                'urgente' => (bool)$reg['urgente'],
                'motivoUrgencia' => $reg['motivo_urgencia'] ?? '',
                'observacoes' => $reg['observacoes'] ?? '',
                'atendente' => $reg['usuario_agendamento_nome'] ?: ($reg['agendado_por'] ?: $reg['usuario_criacao_nome']),
                'dataFormatada' => $dataFormatada,
                'tooltip' => $tooltip
            ]
        ];
        
        $eventos[] = $evento;
    }
    
    // Retorna eventos em JSON
    echo json_encode($eventos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro ao buscar eventos',
        'message' => $e->getMessage()
    ]);
}
