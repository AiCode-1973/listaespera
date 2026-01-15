<?php
/**
 * Script de teste para verificar histórico de um CPF específico
 * REMOVER APÓS DEBUG
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$cpfTeste = $_GET['cpf'] ?? '03619961059'; // CPF padrão para teste

// Limpa o CPF
$cpfLimpo = preg_replace('/[^0-9]/', '', $cpfTeste);

echo "<h1>Teste de Histórico - CPF: " . htmlspecialchars($cpfTeste) . "</h1>";
echo "<p><a href='?cpf='>Testar outro CPF (deixe vazio e adicione ?cpf=NUMERO na URL)</a></p>";
echo "<hr>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Teste 1: Busca direta sem formatação
    echo "<h2>Teste 1: Busca com CPF limpo (sem formatação)</h2>";
    echo "<p>CPF buscado: <code style='background: #eee; padding: 5px;'>" . htmlspecialchars($cpfLimpo) . "</code></p>";
    
    $sql = "SELECT * FROM fila_espera WHERE cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cpf', $cpfLimpo);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Registros encontrados: " . count($resultados) . "</strong></p>";
    
    if (count($resultados) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 8px;'>ID</th>";
        echo "<th style='padding: 8px;'>Nome</th>";
        echo "<th style='padding: 8px;'>CPF no Banco</th>";
        echo "<th style='padding: 8px;'>Tipo</th>";
        echo "<th style='padding: 8px;'>Agendado</th>";
        echo "<th style='padding: 8px;'>Data Solic.</th>";
        echo "</tr>";
        
        foreach ($resultados as $r) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $r['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($r['nome_paciente']) . "</td>";
            echo "<td style='padding: 8px; font-family: monospace;'>" . htmlspecialchars($r['cpf']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($r['tipo_atendimento']) . "</td>";
            echo "<td style='padding: 8px;'>" . ($r['agendado'] ? 'Sim' : 'Não') . "</td>";
            echo "<td style='padding: 8px;'>" . $r['data_solicitacao'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<hr>";
        echo "<h3>✅ Teste bem-sucedido!</h3>";
        echo "<p><a href='/listaespera/paciente_historico.php?cpf=" . urlencode($cpfLimpo) . "' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Ver Histórico Completo</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Nenhum registro encontrado com este CPF.</p>";
        
        // Teste 2: Ver todos os CPFs que existem
        echo "<hr>";
        echo "<h2>CPFs que existem no banco:</h2>";
        
        $sql2 = "SELECT DISTINCT cpf FROM fila_espera LIMIT 10";
        $stmt2 = $conn->query($sql2);
        $cpfs = $stmt2->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<ul>";
        foreach ($cpfs as $c) {
            $match = ($c === $cpfLimpo) ? " <strong>(ESTE!)</strong>" : "";
            echo "<li><code style='background: #eee; padding: 3px;'>" . htmlspecialchars($c) . "</code>$match</li>";
        }
        echo "</ul>";
    }
    
    // Teste 3: Query completa com JOINs (igual ao paciente_historico.php)
    echo "<hr>";
    echo "<h2>Teste 2: Query completa (com JOINs)</h2>";
    
    $sqlCompleta = "SELECT f.*, 
                m.nome as medico_nome,
                e.nome as especialidade_nome,
                c.nome as convenio_nome,
                ua.nome as usuario_agendamento_nome
            FROM fila_espera f
            LEFT JOIN medicos m ON f.medico_id = m.id
            LEFT JOIN especialidades e ON f.especialidade_id = e.id
            LEFT JOIN convenios c ON f.convenio_id = c.id
            LEFT JOIN usuarios ua ON f.usuario_agendamento_id = ua.id
            WHERE f.cpf = :cpf
            ORDER BY f.data_solicitacao DESC, f.id DESC";
    
    $stmt3 = $conn->prepare($sqlCompleta);
    $stmt3->bindParam(':cpf', $cpfLimpo);
    $stmt3->execute();
    $resultadosCompletos = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Registros encontrados com query completa: " . count($resultadosCompletos) . "</strong></p>";
    
    if (count($resultadosCompletos) > 0) {
        echo "<p style='color: green;'>✅ Query completa funcionou!</p>";
    } else {
        echo "<p style='color: red;'>❌ Query completa não retornou resultados.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; background: #fee; padding: 10px; border: 1px solid red;'>";
    echo "<strong>Erro SQL:</strong> " . htmlspecialchars($e->getMessage());
    echo "</p>";
}
?>

<hr>
<p><a href="/listaespera/test_cpf.php">← Voltar para lista de CPFs</a></p>
