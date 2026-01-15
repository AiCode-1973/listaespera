<?php
/**
 * Script de Teste - Verifica Registros Agendados
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Teste - Registros Agendados</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; border-radius: 8px; max-width: 1200px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; margin: 10px 0; }
        .warning { background: #fff3e0; padding: 10px; border-left: 4px solid #ff9800; margin: 10px 0; }
        .error { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #2196f3; color: white; font-weight: bold; }
        tr:nth-child(even) { background: #f5f5f5; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-success { background: #4caf50; color: white; }
        .badge-danger { background: #f44336; color: white; }
        h1 { color: #333; border-bottom: 2px solid #2196f3; padding-bottom: 10px; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🧪 Teste - Registros Agendados na Agenda</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // 1. Conta TODOS os registros
    $sqlTotal = "SELECT COUNT(*) as total FROM fila_espera";
    $stmtTotal = $conn->query($sqlTotal);
    $total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 2. Conta apenas AGENDADOS
    $sqlAgendados = "SELECT COUNT(*) as total FROM fila_espera WHERE agendado = 1";
    $stmtAgendados = $conn->query($sqlAgendados);
    $totalAgendados = $stmtAgendados->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 3. Conta AGENDADOS COM DATA
    $sqlAgendadosComData = "SELECT COUNT(*) as total FROM fila_espera WHERE agendado = 1 AND data_agendamento IS NOT NULL";
    $stmtAgendadosComData = $conn->query($sqlAgendadosComData);
    $totalAgendadosComData = $stmtAgendadosComData->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div class='success'>
        <strong>✅ Conexão com banco de dados OK!</strong>
    </div>";
    
    echo "<h2>📊 Estatísticas Gerais</h2>";
    echo "<table>
        <tr>
            <th>Descrição</th>
            <th>Quantidade</th>
            <th>Status</th>
        </tr>
        <tr>
            <td><strong>Total de Registros</strong></td>
            <td><code style='font-size: 18px;'>{$total}</code></td>
            <td>" . ($total > 0 ? "<span class='badge badge-success'>OK</span>" : "<span class='badge badge-danger'>Vazio</span>") . "</td>
        </tr>
        <tr>
            <td><strong>Registros Agendados (agendado = 1)</strong></td>
            <td><code style='font-size: 18px;'>{$totalAgendados}</code></td>
            <td>" . ($totalAgendados > 0 ? "<span class='badge badge-success'>OK</span>" : "<span class='badge badge-warning'>Nenhum</span>") . "</td>
        </tr>
        <tr>
            <td><strong>Agendados COM Data Preenchida</strong></td>
            <td><code style='font-size: 18px;'>{$totalAgendadosComData}</code></td>
            <td>" . ($totalAgendadosComData > 0 ? "<span class='badge badge-success'>Aparecem na Agenda</span>" : "<span class='badge badge-danger'>Não aparecem</span>") . "</td>
        </tr>
    </table>";
    
    if ($totalAgendadosComData == 0) {
        echo "<div class='warning'>
            <strong>⚠️ ATENÇÃO:</strong> Não há registros agendados com data preenchida!<br><br>
            <strong>Para aparecer na agenda, o registro precisa:</strong><br>
            1. Ter o campo <code>agendado</code> = 1<br>
            2. Ter o campo <code>data_agendamento</code> preenchido<br><br>
            <strong>Como criar um registro agendado:</strong><br>
            1. Acesse <a href='fila_espera_form.php'>Cadastrar Paciente</a><br>
            2. Preencha os dados do paciente<br>
            3. Marque o checkbox <strong>'Agendado'</strong><br>
            4. Preencha a <strong>Data do Agendamento</strong><br>
            5. Salve o registro
        </div>";
    } else {
        echo "<div class='success'>
            <strong>✅ Perfeito!</strong> Há {$totalAgendadosComData} registro(s) que devem aparecer na agenda.
        </div>";
    }
    
    // 4. Lista os registros agendados com data
    if ($totalAgendadosComData > 0) {
        echo "<h2>📋 Registros que Aparecem na Agenda</h2>";
        
        $sql = "SELECT f.id, f.nome_paciente, f.data_agendamento, f.horario_agendamento, 
                       f.tipo_atendimento, f.urgente, f.agendado,
                       m.nome as medico_nome,
                       e.nome as especialidade_nome
                FROM fila_espera f
                LEFT JOIN medicos m ON f.medico_id = m.id
                LEFT JOIN especialidades e ON f.especialidade_id = e.id
                WHERE f.agendado = 1 AND f.data_agendamento IS NOT NULL
                ORDER BY f.data_agendamento DESC, f.id DESC
                LIMIT 20";
        
        $stmt = $conn->query($sql);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Paciente</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Tipo</th>
                    <th>Médico</th>
                    <th>Especialidade</th>
                    <th>Urgente</th>
                    <th>Agendado</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($registros as $reg) {
            $dataFormatada = date('d/m/Y', strtotime($reg['data_agendamento']));
            $urgente = $reg['urgente'] ? "<span class='badge badge-danger'>SIM</span>" : "<span class='badge badge-success'>NÃO</span>";
            $agendado = $reg['agendado'] ? "<span class='badge badge-success'>SIM</span>" : "<span class='badge'>NÃO</span>";
            
            echo "<tr>
                <td><strong>#{$reg['id']}</strong></td>
                <td>{$reg['nome_paciente']}</td>
                <td><strong>{$dataFormatada}</strong></td>
                <td>{$reg['horario_agendamento']}</td>
                <td>{$reg['tipo_atendimento']}</td>
                <td>" . ($reg['medico_nome'] ?: '<em style=\"color: #999;\">Sem médico</em>') . "</td>
                <td>{$reg['especialidade_nome']}</td>
                <td>{$urgente}</td>
                <td>{$agendado}</td>
            </tr>";
        }
        
        echo "</tbody>
        </table>";
    }
    
    // 5. Verifica se há registros marcados como agendado mas sem data
    $sqlAgendadosSemData = "SELECT COUNT(*) as total FROM fila_espera WHERE agendado = 1 AND data_agendamento IS NULL";
    $stmtAgendadosSemData = $conn->query($sqlAgendadosSemData);
    $totalAgendadosSemData = $stmtAgendadosSemData->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($totalAgendadosSemData > 0) {
        echo "<div class='warning'>
            <strong>⚠️ Inconsistência Detectada:</strong><br>
            Há {$totalAgendadosSemData} registro(s) marcado(s) como <code>agendado = 1</code> mas <strong>sem data de agendamento</strong>.<br>
            Estes registros NÃO aparecerão na agenda até que a data seja preenchida.
        </div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>
        <strong>❌ Erro ao conectar no banco de dados:</strong><br>
        {$e->getMessage()}
    </div>";
}

echo "
        <br><br>
        <a href='agenda.php' style='display: inline-block; background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>📅 Ver Agenda</a>
        <a href='dashboard.php' style='display: inline-block; background: #4caf50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;'>🏠 Dashboard</a>
        <a href='fila_espera_form.php' style='display: inline-block; background: #ff9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;'>➕ Criar Agendamento</a>
    </div>
</body>
</html>";
?>
