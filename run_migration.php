<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // SQL to add the column and index
    $sql = "ALTER TABLE fila_espera 
            ADD COLUMN usuario_id INT NULL AFTER agendado_por,
            ADD CONSTRAINT fk_fila_espera_usuario_criador FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
            ADD INDEX idx_usuario_criacao (usuario_id)";
            
    $conn->exec($sql);
    echo "Sucesso: Coluna usuario_id adicionada com sucesso!";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Aviso: A coluna usuario_id já existe.";
    } else {
        echo "Erro: " . $e->getMessage();
    }
}
?>
