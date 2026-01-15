<?php
/**
 * Teste direto de INSERT no banco
 */
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h1>🔬 Teste de INSERT Direto</h1>";
echo "<pre>";

// Dados de teste
$dados = [
    'nome' => 'Teste Insert Direto',
    'email' => 'insert_' . time() . '@test.com',
    'senha_hash' => password_hash('123456', PASSWORD_DEFAULT),
    'perfil' => 'atendente',
    'ativo' => 1
];

echo "📊 DADOS A SEREM INSERIDOS:\n";
print_r($dados);
echo "\n\n";

// INSERT direto
$query = "INSERT INTO usuarios 
          (nome, email, senha_hash, perfil, ativo) 
          VALUES (:nome, :email, :senha_hash, :perfil, :ativo)";

try {
    $stmt = $conn->prepare($query);
    
    echo "✅ Query preparada com sucesso\n\n";
    echo "📝 SQL:\n$query\n\n";
    
    $stmt->bindParam(':nome', $dados['nome']);
    $stmt->bindParam(':email', $dados['email']);
    $stmt->bindParam(':senha_hash', $dados['senha_hash']);
    $stmt->bindParam(':perfil', $dados['perfil']);
    $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_INT);
    
    echo "✅ Parâmetros vinculados\n\n";
    
    if ($stmt->execute()) {
        $lastId = $conn->lastInsertId();
        echo "✅ INSERT executado com sucesso!\n";
        echo "🆔 ID gerado: $lastId\n\n";
        
        // Buscar o registro inserido
        $querySelect = "SELECT id, nome, email, perfil, ativo FROM usuarios WHERE id = :id";
        $stmtSelect = $conn->prepare($querySelect);
        $stmtSelect->bindParam(':id', $lastId);
        $stmtSelect->execute();
        $registro = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        
        echo "📊 REGISTRO INSERIDO NO BANCO:\n";
        print_r($registro);
        
        echo "\n\n";
        echo "🔍 CAMPO PERFIL:\n";
        echo "Valor: [{$registro['perfil']}]\n";
        echo "Length: " . strlen($registro['perfil']) . " chars\n";
        echo "Empty: " . (empty($registro['perfil']) ? 'SIM (problema!)' : 'NÃO (ok!)') . "\n";
        
        if ($registro['perfil'] === 'atendente') {
            echo "\n✅ ✅ ✅ SUCESSO TOTAL! O perfil foi salvo corretamente!\n";
        } else {
            echo "\n❌ ❌ ❌ PROBLEMA! O perfil não foi salvo como esperado!\n";
        }
        
    } else {
        echo "❌ Erro ao executar INSERT\n";
        print_r($stmt->errorInfo());
    }
    
} catch (PDOException $e) {
    echo "❌ ERRO PDO: " . $e->getMessage() . "\n";
}

echo "\n\n";
echo "🔧 ESTRUTURA DA TABELA:\n";
$queryDescribe = "DESCRIBE usuarios";
$stmtDescribe = $conn->query($queryDescribe);
$estrutura = $stmtDescribe->fetchAll(PDO::FETCH_ASSOC);
print_r($estrutura);

echo "</pre>";
?>
