<?php
/**
 * Model Medico
 * Gerencia operações relacionadas aos médicos
 */

require_once __DIR__ . '/../config/database.php';

class Medico {
    private $conn;
    private $table = 'medicos';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lista todos os médicos com paginação
     */
    public function listar($filtros = [], $limit = null, $offset = 0) {
        $query = "SELECT m.*, GROUP_CONCAT(e.nome SEPARATOR ', ') as especialidades
                  FROM " . $this->table . " m
                  LEFT JOIN medico_especialidade me ON m.id = me.medico_id
                  LEFT JOIN especialidades e ON me.especialidade_id = e.id
                  WHERE 1=1";
        
        if (isset($filtros['ativo'])) {
            $query .= " AND m.ativo = :ativo";
        }
        
        if (!empty($filtros['busca'])) {
            $query .= " AND (m.nome LIKE :busca1 OR m.crm_cpf LIKE :busca2)";
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $query .= " AND me.especialidade_id = :especialidade_id";
        }
        
        $query .= " GROUP BY m.id ORDER BY m.nome ASC";
        
        // Adiciona paginação se limit for especificado
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filtros['ativo'])) {
            $stmt->bindParam(':ativo', $filtros['ativo'], PDO::PARAM_BOOL);
        }
        
        if (!empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $stmt->bindValue(':busca1', $busca);
            $stmt->bindValue(':busca2', $busca);
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $stmt->bindParam(':especialidade_id', $filtros['especialidade_id']);
        }
        
        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Conta total de médicos com filtros
     */
    public function contar($filtros = []) {
        $query = "SELECT COUNT(DISTINCT m.id) as total
                  FROM " . $this->table . " m
                  LEFT JOIN medico_especialidade me ON m.id = me.medico_id
                  LEFT JOIN especialidades e ON me.especialidade_id = e.id
                  WHERE 1=1";
        
        if (isset($filtros['ativo'])) {
            $query .= " AND m.ativo = :ativo";
        }
        
        if (!empty($filtros['busca'])) {
            $query .= " AND (m.nome LIKE :busca1 OR m.crm_cpf LIKE :busca2)";
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $query .= " AND me.especialidade_id = :especialidade_id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($filtros['ativo'])) {
            $stmt->bindParam(':ativo', $filtros['ativo'], PDO::PARAM_BOOL);
        }
        
        if (!empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $stmt->bindValue(':busca1', $busca);
            $stmt->bindValue(':busca2', $busca);
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $stmt->bindParam(':especialidade_id', $filtros['especialidade_id']);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Busca médico por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria novo médico
     */
    public function criar($dados) {
        try {
            $this->conn->beginTransaction();
            
            // Insere o médico
            $query = "INSERT INTO " . $this->table . " 
                      (nome, crm_cpf, telefone, email, observacao, ativo) 
                      VALUES (:nome, :crm_cpf, :telefone, :email, :observacao, :ativo)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':crm_cpf', $dados['crm_cpf']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':email', $dados['email']);
            $stmt->bindParam(':observacao', $dados['observacao']);
            $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_BOOL);
            
            $stmt->execute();
            $medicoId = $this->conn->lastInsertId();
            
            // Associa especialidades
            if (!empty($dados['especialidades']) && is_array($dados['especialidades'])) {
                $this->associarEspecialidades($medicoId, $dados['especialidades']);
            }
            
            $this->conn->commit();
            return $medicoId;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Atualiza médico
     */
    public function atualizar($id, $dados) {
        try {
            $this->conn->beginTransaction();
            
            $query = "UPDATE " . $this->table . " 
                      SET nome = :nome, 
                          crm_cpf = :crm_cpf, 
                          telefone = :telefone, 
                          email = :email, 
                          observacao = :observacao,
                          ativo = :ativo 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nome', $dados['nome']);
            $stmt->bindParam(':crm_cpf', $dados['crm_cpf']);
            $stmt->bindParam(':telefone', $dados['telefone']);
            $stmt->bindParam(':email', $dados['email']);
            $stmt->bindParam(':observacao', $dados['observacao']);
            $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_BOOL);
            
            $stmt->execute();
            
            // Atualiza especialidades (remove todas e reinsere)
            if (isset($dados['especialidades'])) {
                $this->removerEspecialidades($id);
                if (!empty($dados['especialidades']) && is_array($dados['especialidades'])) {
                    $this->associarEspecialidades($id, $dados['especialidades']);
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Associa especialidades ao médico
     */
    private function associarEspecialidades($medicoId, $especialidadesIds) {
        $query = "INSERT INTO medico_especialidade (medico_id, especialidade_id) VALUES (:medico_id, :especialidade_id)";
        $stmt = $this->conn->prepare($query);
        
        foreach ($especialidadesIds as $especialidadeId) {
            $stmt->bindParam(':medico_id', $medicoId);
            $stmt->bindParam(':especialidade_id', $especialidadeId);
            $stmt->execute();
        }
    }

    /**
     * Remove todas as especialidades do médico
     */
    private function removerEspecialidades($medicoId) {
        $query = "DELETE FROM medico_especialidade WHERE medico_id = :medico_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medico_id', $medicoId);
        $stmt->execute();
    }

    /**
     * Busca IDs das especialidades do médico
     */
    public function buscarEspecialidades($medicoId) {
        $query = "SELECT e.id 
                  FROM medico_especialidade me
                  INNER JOIN especialidades e ON me.especialidade_id = e.id
                  WHERE me.medico_id = :medico_id
                  ORDER BY e.nome";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':medico_id', $medicoId);
        $stmt->execute();
        
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result;
    }

    /**
     * Verifica se CRM/CPF já existe
     */
    public function crmCpfExiste($crmCpf, $excluirId = null) {
        // Não verifica duplicidade se o CRM/CPF estiver vazio
        if (empty($crmCpf)) {
            return false;
        }
        
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE crm_cpf = :crm_cpf 
                  AND crm_cpf IS NOT NULL 
                  AND crm_cpf != ''";
        
        if ($excluirId) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':crm_cpf', $crmCpf);
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Inativa médico (não exclui fisicamente)
     */
    public function inativar($id) {
        $query = "UPDATE " . $this->table . " SET ativo = 0 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Deleta médico (exclusão física)
     */
    public function deletar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
