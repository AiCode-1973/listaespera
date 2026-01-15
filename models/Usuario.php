<?php
/**
 * Model Usuario
 * Gerencia operações relacionadas aos usuários do sistema
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Autentica usuário por email e senha
     */
    public function autenticar($email, $senha) {
        $query = "SELECT id, nome, email, senha_hash, perfil, ativo 
                  FROM " . $this->table . " 
                  WHERE email = :email AND ativo = 1 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $usuario = $stmt->fetch();
            
            // Verifica se a senha está correta
            if (password_verify($senha, $usuario['senha_hash'])) {
                return $usuario;
            }
        }
        
        return false;
    }

    /**
     * Busca usuário por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT id, nome, email, perfil, ativo, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Lista todos os usuários
     */
    public function listar($filtros = []) {
        $query = "SELECT id, nome, email, perfil, ativo, created_at 
                  FROM " . $this->table . " 
                  WHERE 1=1";
        
        if (!empty($filtros['perfil'])) {
            $query .= " AND perfil = :perfil";
        }
        
        if (isset($filtros['ativo'])) {
            $query .= " AND ativo = :ativo";
        }
        
        if (!empty($filtros['busca'])) {
            $query .= " AND (nome LIKE :busca OR email LIKE :busca)";
        }
        
        $query .= " ORDER BY nome ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['perfil'])) {
            $stmt->bindParam(':perfil', $filtros['perfil']);
        }
        
        if (isset($filtros['ativo'])) {
            $stmt->bindParam(':ativo', $filtros['ativo'], PDO::PARAM_BOOL);
        }
        
        if (!empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $stmt->bindParam(':busca', $busca);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cria novo usuário
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table . " 
                  (nome, email, senha_hash, perfil, ativo) 
                  VALUES (:nome, :email, :senha_hash, :perfil, :ativo)";
        
        $stmt = $this->conn->prepare($query);
        
        $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':email', $dados['email']);
        $stmt->bindParam(':senha_hash', $senha_hash);
        $stmt->bindParam(':perfil', $dados['perfil']);
        $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_BOOL);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Atualiza usuário
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table . " 
                  SET nome = :nome, 
                      email = :email, 
                      perfil = :perfil, 
                      ativo = :ativo";
        
        // Se houver nova senha, incluir no update
        if (!empty($dados['senha'])) {
            $query .= ", senha_hash = :senha_hash";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':email', $dados['email']);
        $stmt->bindParam(':perfil', $dados['perfil']);
        $stmt->bindParam(':ativo', $dados['ativo'], PDO::PARAM_BOOL);
        
        if (!empty($dados['senha'])) {
            $senha_hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
            $stmt->bindParam(':senha_hash', $senha_hash);
        }
        
        return $stmt->execute();
    }

    /**
     * Verifica se email já existe
     */
    public function emailExiste($email, $excluirId = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        
        if ($excluirId) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Inativa um usuário
     */
    public function inativar($id) {
        $query = "UPDATE " . $this->table . " SET ativo = 0 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Deleta permanentemente um usuário
     */
    public function deletar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Altera senha do usuário (com verificação da senha atual)
     */
    public function alterarSenha($id, $senhaAtual, $senhaNova) {
        // Busca a senha hash atual
        $query = "SELECT senha_hash FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['sucesso' => false, 'mensagem' => 'Usuário não encontrado'];
        }
        
        // Verifica se a senha atual está correta
        if (!password_verify($senhaAtual, $usuario['senha_hash'])) {
            return ['sucesso' => false, 'mensagem' => 'Senha atual incorreta'];
        }
        
        // Atualiza para nova senha
        $query = "UPDATE " . $this->table . " SET senha_hash = :senha_hash WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $senha_hash = password_hash($senhaNova, PASSWORD_DEFAULT);
        $stmt->bindParam(':senha_hash', $senha_hash);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return ['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso'];
        }
        
        return ['sucesso' => false, 'mensagem' => 'Erro ao atualizar senha'];
    }
}
