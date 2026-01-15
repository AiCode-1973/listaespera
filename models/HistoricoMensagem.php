<?php
/**
 * Model HistoricoMensagem
 * Gerencia o histórico de mensagens WhatsApp enviadas
 */

require_once __DIR__ . '/../config/database.php';

class HistoricoMensagem {
    private $conn;
    private $table = 'historico_mensagens';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Registra uma mensagem enviada
     */
    public function registrar($dados) {
        $query = "INSERT INTO " . $this->table . " 
                  (fila_espera_id, paciente_id, usuario_id, telefone, mensagem, tipo_mensagem) 
                  VALUES (:fila_espera_id, :paciente_id, :usuario_id, :telefone, :mensagem, :tipo_mensagem)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':fila_espera_id', $dados['fila_espera_id']);
        $stmt->bindParam(':paciente_id', $dados['paciente_id']);
        $stmt->bindParam(':usuario_id', $dados['usuario_id']);
        $stmt->bindParam(':telefone', $dados['telefone']);
        $stmt->bindParam(':mensagem', $dados['mensagem']);
        $stmt->bindParam(':tipo_mensagem', $dados['tipo_mensagem']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Lista histórico com filtros
     */
    public function listar($filtros = []) {
        $query = "SELECT 
                    hm.*,
                    fe.nome_paciente as paciente_nome,
                    fe.cpf as paciente_cpf,
                    u.nome as usuario_nome,
                    m.nome as medico_nome,
                    e.nome as especialidade_nome,
                    fe.data_agendamento,
                    fe.tipo_atendimento
                  FROM " . $this->table . " hm
                  LEFT JOIN fila_espera fe ON hm.fila_espera_id = fe.id
                  LEFT JOIN usuarios u ON hm.usuario_id = u.id
                  LEFT JOIN medicos m ON fe.medico_id = m.id
                  LEFT JOIN especialidades e ON fe.especialidade_id = e.id
                  WHERE 1=1";
        
        // Filtro por paciente
        if (!empty($filtros['paciente_id'])) {
            $query .= " AND hm.paciente_id = :paciente_id";
        }
        
        // Filtro por usuário que enviou
        if (!empty($filtros['usuario_id'])) {
            $query .= " AND hm.usuario_id = :usuario_id";
        }
        
        // Filtro por tipo de mensagem
        if (!empty($filtros['tipo_mensagem'])) {
            $query .= " AND hm.tipo_mensagem = :tipo_mensagem";
        }
        
        // Filtro por período
        if (!empty($filtros['data_inicio'])) {
            $query .= " AND DATE(hm.data_envio) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $query .= " AND DATE(hm.data_envio) <= :data_fim";
        }
        
        // Busca por nome do paciente
        if (!empty($filtros['busca'])) {
            $query .= " AND fe.nome_paciente LIKE :busca";
        }
        
        $query .= " ORDER BY hm.data_envio DESC";
        
        // Paginação
        if (!empty($filtros['limite'])) {
            $query .= " LIMIT :limite";
            if (!empty($filtros['offset'])) {
                $query .= " OFFSET :offset";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind dos parâmetros
        if (!empty($filtros['paciente_id'])) {
            $stmt->bindParam(':paciente_id', $filtros['paciente_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['usuario_id'])) {
            $stmt->bindParam(':usuario_id', $filtros['usuario_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['tipo_mensagem'])) {
            $stmt->bindParam(':tipo_mensagem', $filtros['tipo_mensagem']);
        }
        
        if (!empty($filtros['data_inicio'])) {
            $stmt->bindParam(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $stmt->bindParam(':data_fim', $filtros['data_fim']);
        }
        
        if (!empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $stmt->bindParam(':busca', $busca);
        }
        
        if (!empty($filtros['limite'])) {
            $stmt->bindParam(':limite', $filtros['limite'], PDO::PARAM_INT);
            if (!empty($filtros['offset'])) {
                $stmt->bindParam(':offset', $filtros['offset'], PDO::PARAM_INT);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT 
                    hm.*,
                    fe.nome_paciente as paciente_nome,
                    fe.cpf as paciente_cpf,
                    fe.telefone1 as paciente_telefone,
                    u.nome as usuario_nome,
                    m.nome as medico_nome,
                    e.nome as especialidade_nome,
                    fe.data_agendamento,
                    fe.tipo_atendimento
                  FROM " . $this->table . " hm
                  LEFT JOIN fila_espera fe ON hm.fila_espera_id = fe.id
                  LEFT JOIN usuarios u ON hm.usuario_id = u.id
                  LEFT JOIN medicos m ON fe.medico_id = m.id
                  LEFT JOIN especialidades e ON fe.especialidade_id = e.id
                  WHERE hm.id = :id
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Conta total de registros (para paginação)
     */
    public function contar($filtros = []) {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table . " hm
                  LEFT JOIN fila_espera fe ON hm.fila_espera_id = fe.id
                  WHERE 1=1";
        
        if (!empty($filtros['paciente_id'])) {
            $query .= " AND hm.paciente_id = :paciente_id";
        }
        
        if (!empty($filtros['usuario_id'])) {
            $query .= " AND hm.usuario_id = :usuario_id";
        }
        
        if (!empty($filtros['tipo_mensagem'])) {
            $query .= " AND hm.tipo_mensagem = :tipo_mensagem";
        }
        
        if (!empty($filtros['data_inicio'])) {
            $query .= " AND DATE(hm.data_envio) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $query .= " AND DATE(hm.data_envio) <= :data_fim";
        }
        
        if (!empty($filtros['busca'])) {
            $query .= " AND fe.nome_paciente LIKE :busca";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['paciente_id'])) {
            $stmt->bindParam(':paciente_id', $filtros['paciente_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['usuario_id'])) {
            $stmt->bindParam(':usuario_id', $filtros['usuario_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['tipo_mensagem'])) {
            $stmt->bindParam(':tipo_mensagem', $filtros['tipo_mensagem']);
        }
        
        if (!empty($filtros['data_inicio'])) {
            $stmt->bindParam(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $stmt->bindParam(':data_fim', $filtros['data_fim']);
        }
        
        if (!empty($filtros['busca'])) {
            $busca = '%' . $filtros['busca'] . '%';
            $stmt->bindParam(':busca', $busca);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'];
    }

    /**
     * Busca histórico de um paciente específico
     */
    public function buscarPorPaciente($pacienteId, $limite = 10) {
        return $this->listar([
            'paciente_id' => $pacienteId,
            'limite' => $limite
        ]);
    }

    /**
     * Busca histórico de uma fila de espera específica
     */
    public function buscarPorFilaEspera($filaEsperaId) {
        $query = "SELECT 
                    hm.*,
                    u.nome as usuario_nome
                  FROM " . $this->table . " hm
                  INNER JOIN usuarios u ON hm.usuario_id = u.id
                  WHERE hm.fila_espera_id = :fila_espera_id
                  ORDER BY hm.data_envio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fila_espera_id', $filaEsperaId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estatísticas de envios
     */
    public function estatisticas($filtros = []) {
        $query = "SELECT 
                    COUNT(*) as total_envios,
                    COUNT(DISTINCT fila_espera_id) as total_pacientes,
                    COUNT(DISTINCT usuario_id) as total_usuarios,
                    DATE(MIN(data_envio)) as primeira_mensagem,
                    DATE(MAX(data_envio)) as ultima_mensagem
                  FROM " . $this->table . "
                  WHERE 1=1";
        
        if (!empty($filtros['data_inicio'])) {
            $query .= " AND DATE(data_envio) >= :data_inicio";
        }
        
        if (!empty($filtros['data_fim'])) {
            $query .= " AND DATE(data_envio) <= :data_fim";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if (!empty($filtros['data_inicio'])) {
            $stmt->bindParam(':data_inicio', $filtros['data_inicio']);
        }
        
        if (!empty($filtros['data_fim'])) {
            $stmt->bindParam(':data_fim', $filtros['data_fim']);
        }
        
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Exclui mensagem do histórico
     */
    public function excluir($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
