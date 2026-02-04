<?php
require_once __DIR__ . '/config/database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("DESCRIBE fila_espera");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
