<?php
/**
 * Model Especialidade
 * Gerencia operações relacionadas às especialidades médicas
 */

require_once __DIR__ . '/../config/database.php';

class Especialidade {
    private $conn;
    private $table = 'especialidades';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lista todas as especialidades com paginação
     */
    public function listar($busca = '', $limit = null, $offset = 0) {
        $query = "SELECT * FROM " . $this->table;
        
        if (!empty($busca)) {
            $query .= " WHERE nome LIKE :busca";
        }
        
        $query .= " ORDER BY nome ASC";
        
        // Adiciona paginação se limit for especificado
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($busca)) {
            $buscaParam = '%' . $busca . '%';
            $stmt->bindParam(':busca', $buscaParam);
        }
        
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Conta total de especialidades com filtros
     */
    public function contar($busca = '') {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        
        if (!empty($busca)) {
            $query .= " WHERE nome LIKE :busca";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($busca)) {
            $buscaParam = '%' . $busca . '%';
            $stmt->bindParam(':busca', $buscaParam);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Busca especialidade por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Cria nova especialidade
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table . " (nome, cor) VALUES (:nome, :cor)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':cor', $dados['cor']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Atualiza especialidade
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, cor = :cor 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':cor', $dados['cor']);
        
        return $stmt->execute();
    }

    /**
     * Deleta especialidade
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

    /**
     * Busca especialidades de um médico
     */
    public function buscarPorMedico($medicoId) {
        $query = "SELECT e.* 
                  FROM " . $this->table . " e
                  INNER JOIN medico_especialidade me ON e.id = me.especialidade_id
                  WHERE me.medico_id = :medico_id
                  ORDER BY e.nome ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medico_id', $medicoId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
