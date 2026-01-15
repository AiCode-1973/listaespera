<?php
/**
 * Model Convenio
 * Gerencia operações relacionadas aos convênios
 */

require_once __DIR__ . '/../config/database.php';

class Convenio {
    private $conn;
    private $table = 'convenios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lista todos os convênios
     */
    public function listar($busca = '') {
        $query = "SELECT * FROM " . $this->table;
        
        if (!empty($busca)) {
            $query .= " WHERE nome LIKE :busca OR codigo LIKE :busca";
        }
        
        $query .= " ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($busca)) {
            $busca = '%' . $busca . '%';
            $stmt->bindParam(':busca', $busca);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Busca convênio por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Cria novo convênio
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table . " (nome, codigo, cor) VALUES (:nome, :codigo, :cor)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':codigo', $dados['codigo']);
        $stmt->bindParam(':cor', $dados['cor']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Atualiza convênio
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, codigo = :codigo, cor = :cor 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':codigo', $dados['codigo']);
        $stmt->bindParam(':cor', $dados['cor']);
        
        return $stmt->execute();
    }

    /**
     * Deleta convênio
     */
    public function deletar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Verifica se nome já existe
     */
    public function nomeExiste($nome, $excluirId = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE nome = :nome";
        
        if ($excluirId) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $nome);
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
