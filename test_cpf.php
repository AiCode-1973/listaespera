<?php
/**
 * Script de teste para verificar CPFs no banco
 * REMOVER APÓS DEBUG
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $sql = "SELECT cpf, MIN(nome_paciente) as nome_paciente, COUNT(*) as total 
            FROM fila_espera 
            GROUP BY cpf 
            LIMIT 10";
    
    $stmt = $conn->query($sql);
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>CPFs no Banco de Dados (Total: " . count($pacientes) . ")</h1>";
    echo "<p><strong>Nota:</strong> Clique em 'Ver Histórico' para testar se funciona.</p>";
    echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
    echo "<tr style='background: #f0f0f0;'><th style='padding: 8px;'>CPF</th><th style='padding: 8px;'>Nome</th><th style='padding: 8px;'>Total Registros</th><th style='padding: 8px;'>Ações</th></tr>";
    
    foreach ($pacientes as $p) {
        echo "<tr>";
        echo "<td style='padding: 8px; font-family: monospace;'>" . htmlspecialchars($p['cpf']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($p['nome_paciente']) . "</td>";
        echo "<td style='padding: 8px; text-align: center;'>" . $p['total'] . "</td>";
        echo "<td style='padding: 8px;'>";
        echo "<a href='/listaespera/paciente_historico.php?cpf=" . urlencode($p['cpf']) . "' target='_blank' style='color: blue; text-decoration: underline;'>Ver Histórico</a>";
        echo " | ";
        echo "<a href='/listaespera/pacientes.php?busca=" . urlencode($p['cpf']) . "' target='_blank' style='color: green; text-decoration: underline;'>Buscar</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<h2>Teste Direto:</h2>";
    if (count($pacientes) > 0) {
        $primeiro = $pacientes[0];
        echo "<p>CPF de teste: <strong>" . htmlspecialchars($primeiro['cpf']) . "</strong></p>";
        echo "<p><a href='/listaespera/paciente_historico.php?cpf=" . urlencode($primeiro['cpf']) . "' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>TESTAR HISTÓRICO DO PRIMEIRO</a></p>";
    }
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
