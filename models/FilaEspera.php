<?php
/**
 * Model FilaEspera
 * Gerencia operações relacionadas à fila de espera de pacientes
 */

require_once __DIR__ . '/../config/database.php';

class FilaEspera {
    private $conn;
    private $table = 'fila_espera';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Lista registros da fila com filtros e paginação
     */
    public function listar($filtros = [], $limit = 20, $offset = 0) {
        $query = "SELECT f.*, 
                         m.nome as medico_nome,
                         e.nome as especialidade_nome, 
                         e.cor as especialidade_cor,
                         c.nome as convenio_nome,
                         c.cor as convenio_cor,
                         u.nome as usuario_agendamento_nome
                  FROM " . $this->table . " f
                  LEFT JOIN medicos m ON f.medico_id = m.id
                  INNER JOIN especialidades e ON f.especialidade_id = e.id
                  LEFT JOIN convenios c ON f.convenio_id = c.id
                  LEFT JOIN usuarios u ON f.usuario_agendamento_id = u.id
                  WHERE 1=1";
        
        // Aplicar filtros
        if (!empty($filtros['medico_id'])) {
            $query .= " AND f.medico_id = :medico_id";
        }
        
        if (isset($filtros['urgente']) && $filtros['urgente'] !== '') {
            $query .= " AND f.urgente = :urgente";
        }
        
        if (!empty($filtros['tipo_atendimento'])) {
            $query .= " AND f.tipo_atendimento = :tipo_atendimento";
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $query .= " AND f.especialidade_id = :especialidade_id";
        }
        
        if (!empty($filtros['convenio_id'])) {
            $query .= " AND f.convenio_id = :convenio_id";
        }
        
        if (isset($filtros['agendado']) && $filtros['agendado'] !== '') {
            $query .= " AND f.agendado = :agendado";
        }
        
        if (!empty($filtros['nome_paciente'])) {
            $query .= " AND f.nome_paciente LIKE :nome_paciente";
        }
        
        if (!empty($filtros['cpf'])) {
            $query .= " AND f.cpf LIKE :cpf";
        }
        
        if (!empty($filtros['data_solicitacao_inicio'])) {
            $query .= " AND f.data_solicitacao >= :data_solicitacao_inicio";
        }
        
        if (!empty($filtros['data_solicitacao_fim'])) {
            $query .= " AND f.data_solicitacao <= :data_solicitacao_fim";
        }
        
        if (isset($filtros['guia_autorizada']) && $filtros['guia_autorizada'] !== '') {
            if ($filtros['guia_autorizada'] === 'null') {
                $query .= " AND f.guia_autorizada IS NULL";
            } else {
                $query .= " AND f.guia_autorizada = :guia_autorizada";
            }
        }
        
        // Ordenação (pacientes urgentes sempre primeiro)
        $query .= " ORDER BY f.urgente DESC";
        
        $orderBy = $filtros['order_by'] ?? 'data_solicitacao';
        $orderDir = $filtros['order_dir'] ?? 'ASC';
        $query .= ", f." . $orderBy . " " . $orderDir;
        
        // Paginação
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind dos parâmetros
        if (!empty($filtros['medico_id'])) {
            $stmt->bindParam(':medico_id', $filtros['medico_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $stmt->bindParam(':especialidade_id', $filtros['especialidade_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['convenio_id'])) {
            $stmt->bindParam(':convenio_id', $filtros['convenio_id'], PDO::PARAM_INT);
        }
        
        if (isset($filtros['agendado']) && $filtros['agendado'] !== '') {
            $agendado = (int)$filtros['agendado'];
            $stmt->bindParam(':agendado', $agendado, PDO::PARAM_INT);
        }
        
        if (!empty($filtros['nome_paciente'])) {
            $nome = '%' . $filtros['nome_paciente'] . '%';
            $stmt->bindParam(':nome_paciente', $nome);
        }
        
        if (!empty($filtros['cpf'])) {
            $cpf = '%' . $filtros['cpf'] . '%';
            $stmt->bindParam(':cpf', $cpf);
        }
        
        if (!empty($filtros['data_solicitacao_inicio'])) {
            $stmt->bindParam(':data_solicitacao_inicio', $filtros['data_solicitacao_inicio']);
        }
        
        if (!empty($filtros['data_solicitacao_fim'])) {
            $stmt->bindParam(':data_solicitacao_fim', $filtros['data_solicitacao_fim']);
        }
        
        if (isset($filtros['urgente']) && $filtros['urgente'] !== '') {
            $urgente = (int)$filtros['urgente'];
            $stmt->bindParam(':urgente', $urgente, PDO::PARAM_INT);
        }
        
        if (!empty($filtros['tipo_atendimento'])) {
            $stmt->bindParam(':tipo_atendimento', $filtros['tipo_atendimento']);
        }
        
        if (isset($filtros['guia_autorizada']) && $filtros['guia_autorizada'] !== '' && $filtros['guia_autorizada'] !== 'null') {
            $guia = (int)$filtros['guia_autorizada'];
            $stmt->bindParam(':guia_autorizada', $guia, PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Conta total de registros com filtros
     */
    public function contar($filtros = []) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " f WHERE 1=1";
        
        if (!empty($filtros['medico_id'])) {
            $query .= " AND f.medico_id = :medico_id";
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $query .= " AND f.especialidade_id = :especialidade_id";
        }
        
        if (!empty($filtros['convenio_id'])) {
            $query .= " AND f.convenio_id = :convenio_id";
        }
        
        if (isset($filtros['agendado']) && $filtros['agendado'] !== '') {
            $query .= " AND f.agendado = :agendado";
        }
        
        if (!empty($filtros['nome_paciente'])) {
            $query .= " AND f.nome_paciente LIKE :nome_paciente";
        }
        
        if (!empty($filtros['cpf'])) {
            $query .= " AND f.cpf LIKE :cpf";
        }
        
        if (!empty($filtros['data_solicitacao_inicio'])) {
            $query .= " AND f.data_solicitacao >= :data_solicitacao_inicio";
        }
        
        if (!empty($filtros['data_solicitacao_fim'])) {
            $query .= " AND f.data_solicitacao <= :data_solicitacao_fim";
        }
        
        if (isset($filtros['urgente']) && $filtros['urgente'] !== '') {
            $query .= " AND f.urgente = :urgente";
        }
        
        if (!empty($filtros['tipo_atendimento'])) {
            $query .= " AND f.tipo_atendimento = :tipo_atendimento";
        }
        
        if (isset($filtros['guia_autorizada']) && $filtros['guia_autorizada'] !== '') {
            if ($filtros['guia_autorizada'] === 'null') {
                $query .= " AND f.guia_autorizada IS NULL";
            } else {
                $query .= " AND f.guia_autorizada = :guia_autorizada";
            }
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind dos parâmetros (mesmo do listar)
        if (!empty($filtros['medico_id'])) {
            $stmt->bindParam(':medico_id', $filtros['medico_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['especialidade_id'])) {
            $stmt->bindParam(':especialidade_id', $filtros['especialidade_id'], PDO::PARAM_INT);
        }
        
        if (!empty($filtros['convenio_id'])) {
            $stmt->bindParam(':convenio_id', $filtros['convenio_id'], PDO::PARAM_INT);
        }
        
        if (isset($filtros['agendado']) && $filtros['agendado'] !== '') {
            $agendado = (int)$filtros['agendado'];
            $stmt->bindParam(':agendado', $agendado, PDO::PARAM_INT);
        }
        
        if (!empty($filtros['nome_paciente'])) {
            $nome = '%' . $filtros['nome_paciente'] . '%';
            $stmt->bindParam(':nome_paciente', $nome);
        }
        
        if (!empty($filtros['cpf'])) {
            $cpf = '%' . $filtros['cpf'] . '%';
            $stmt->bindParam(':cpf', $cpf);
        }
        
        if (!empty($filtros['data_solicitacao_inicio'])) {
            $stmt->bindParam(':data_solicitacao_inicio', $filtros['data_solicitacao_inicio']);
        }
        
        if (!empty($filtros['data_solicitacao_fim'])) {
            $stmt->bindParam(':data_solicitacao_fim', $filtros['data_solicitacao_fim']);
        }
        
        if (isset($filtros['urgente']) && $filtros['urgente'] !== '') {
            $urgente = (int)$filtros['urgente'];
            $stmt->bindParam(':urgente', $urgente, PDO::PARAM_INT);
        }
        
        if (!empty($filtros['tipo_atendimento'])) {
            $stmt->bindParam(':tipo_atendimento', $filtros['tipo_atendimento']);
        }
        
        if (isset($filtros['guia_autorizada']) && $filtros['guia_autorizada'] !== '' && $filtros['guia_autorizada'] !== 'null') {
            $guia = (int)$filtros['guia_autorizada'];
            $stmt->bindParam(':guia_autorizada', $guia, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Busca registro por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT f.*, 
                         m.nome as medico_nome,
                         e.nome as especialidade_nome,
                         c.nome as convenio_nome
                  FROM " . $this->table . " f
                  LEFT JOIN medicos m ON f.medico_id = m.id
                  INNER JOIN especialidades e ON f.especialidade_id = e.id
                  LEFT JOIN convenios c ON f.convenio_id = c.id
                  WHERE f.id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Cria novo registro na fila
     */
    public function criar($dados) {
        $query = "INSERT INTO " . $this->table . " 
                  (medico_id, especialidade_id, convenio_id, nome_paciente, cpf, 
                   data_nascimento, data_solicitacao, informacao, observacao, 
                   agendado, data_agendamento, horario_agendamento, telefone1, telefone2, agendado_por,
                   urgente, motivo_urgencia, tipo_atendimento,
                   guia_autorizada, data_autorizacao_guia, observacao_guia,
                   usuario_agendamento_id, data_hora_agendamento) 
                  VALUES 
                  (:medico_id, :especialidade_id, :convenio_id, :nome_paciente, :cpf, 
                   :data_nascimento, :data_solicitacao, :informacao, :observacao, 
                   :agendado, :data_agendamento, :horario_agendamento, :telefone1, :telefone2, :agendado_por,
                   :urgente, :motivo_urgencia, :tipo_atendimento,
                   :guia_autorizada, :data_autorizacao_guia, :observacao_guia,
                   :usuario_agendamento_id, :data_hora_agendamento)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':medico_id', $dados['medico_id']);
        $stmt->bindParam(':especialidade_id', $dados['especialidade_id']);
        $stmt->bindParam(':convenio_id', $dados['convenio_id']);
        $stmt->bindParam(':nome_paciente', $dados['nome_paciente']);
        $stmt->bindParam(':cpf', $dados['cpf']);
        $stmt->bindParam(':data_nascimento', $dados['data_nascimento']);
        $stmt->bindParam(':data_solicitacao', $dados['data_solicitacao']);
        $stmt->bindParam(':informacao', $dados['informacao']);
        $stmt->bindParam(':observacao', $dados['observacao']);
        $stmt->bindParam(':agendado', $dados['agendado'], PDO::PARAM_BOOL);
        $stmt->bindParam(':data_agendamento', $dados['data_agendamento']);
        $stmt->bindParam(':horario_agendamento', $dados['horario_agendamento']);
        $stmt->bindParam(':telefone1', $dados['telefone1']);
        $stmt->bindParam(':telefone2', $dados['telefone2']);
        $stmt->bindParam(':agendado_por', $dados['agendado_por']);
        $stmt->bindParam(':urgente', $dados['urgente'], PDO::PARAM_BOOL);
        $stmt->bindParam(':motivo_urgencia', $dados['motivo_urgencia']);
        $stmt->bindParam(':tipo_atendimento', $dados['tipo_atendimento']);
        $stmt->bindParam(':guia_autorizada', $dados['guia_autorizada'], PDO::PARAM_INT);
        $stmt->bindParam(':data_autorizacao_guia', $dados['data_autorizacao_guia']);
        $stmt->bindParam(':observacao_guia', $dados['observacao_guia']);
        $stmt->bindParam(':usuario_agendamento_id', $dados['usuario_agendamento_id'], PDO::PARAM_INT);
        $stmt->bindParam(':data_hora_agendamento', $dados['data_hora_agendamento']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    /**
     * Atualiza registro na fila
     */
    public function atualizar($id, $dados) {
        $query = "UPDATE " . $this->table . " 
                  SET medico_id = :medico_id,
                      especialidade_id = :especialidade_id,
                      convenio_id = :convenio_id,
                      nome_paciente = :nome_paciente,
                      cpf = :cpf,
                      data_nascimento = :data_nascimento,
                      data_solicitacao = :data_solicitacao,
                      informacao = :informacao,
                      observacao = :observacao,
                      agendado = :agendado,
                      data_agendamento = :data_agendamento,
                      horario_agendamento = :horario_agendamento,
                      telefone1 = :telefone1,
                      telefone2 = :telefone2,
                      agendado_por = :agendado_por,
                      urgente = :urgente,
                      motivo_urgencia = :motivo_urgencia,
                      tipo_atendimento = :tipo_atendimento,
                      guia_autorizada = :guia_autorizada,
                      data_autorizacao_guia = :data_autorizacao_guia,
                      observacao_guia = :observacao_guia,
                      usuario_agendamento_id = :usuario_agendamento_id,
                      data_hora_agendamento = :data_hora_agendamento
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':medico_id', $dados['medico_id']);
        $stmt->bindParam(':especialidade_id', $dados['especialidade_id']);
        $stmt->bindParam(':convenio_id', $dados['convenio_id']);
        $stmt->bindParam(':nome_paciente', $dados['nome_paciente']);
        $stmt->bindParam(':cpf', $dados['cpf']);
        $stmt->bindParam(':data_nascimento', $dados['data_nascimento']);
        $stmt->bindParam(':data_solicitacao', $dados['data_solicitacao']);
        $stmt->bindParam(':informacao', $dados['informacao']);
        $stmt->bindParam(':observacao', $dados['observacao']);
        $stmt->bindParam(':agendado', $dados['agendado'], PDO::PARAM_BOOL);
        $stmt->bindParam(':data_agendamento', $dados['data_agendamento']);
        $stmt->bindParam(':horario_agendamento', $dados['horario_agendamento']);
        $stmt->bindParam(':telefone1', $dados['telefone1']);
        $stmt->bindParam(':telefone2', $dados['telefone2']);
        $stmt->bindParam(':agendado_por', $dados['agendado_por']);
        $stmt->bindParam(':urgente', $dados['urgente'], PDO::PARAM_BOOL);
        $stmt->bindParam(':motivo_urgencia', $dados['motivo_urgencia']);
        $stmt->bindParam(':tipo_atendimento', $dados['tipo_atendimento']);
        $stmt->bindParam(':guia_autorizada', $dados['guia_autorizada'], PDO::PARAM_INT);
        $stmt->bindParam(':data_autorizacao_guia', $dados['data_autorizacao_guia']);
        $stmt->bindParam(':observacao_guia', $dados['observacao_guia']);
        $stmt->bindParam(':usuario_agendamento_id', $dados['usuario_agendamento_id'], PDO::PARAM_INT);
        $stmt->bindParam(':data_hora_agendamento', $dados['data_hora_agendamento']);
        
        return $stmt->execute();
    }

    /**
     * Marca registro como agendado
     */
    public function marcarAgendado($id, $dataAgendamento, $agendadoPor) {
        $query = "UPDATE " . $this->table . " 
                  SET agendado = 1, 
                      data_agendamento = :data_agendamento, 
                      agendado_por = :agendado_por 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':data_agendamento', $dataAgendamento);
        $stmt->bindParam(':agendado_por', $agendadoPor);
        
        return $stmt->execute();
    }

    /**
     * Deleta registro
     */
    public function deletar($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    /**
     * Verifica se já existe registro do mesmo paciente/médico/data
     */
    public function verificarDuplicidade($cpf, $medicoId, $dataSolicitacao, $excluirId = null) {
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE cpf = :cpf 
                  AND data_solicitacao = :data_solicitacao";
        
        if ($medicoId !== null) {
            $query .= " AND medico_id = :medico_id";
        } else {
            $query .= " AND medico_id IS NULL";
        }
        
        if ($excluirId) {
            $query .= " AND id != :id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':data_solicitacao', $dataSolicitacao);
        
        if ($medicoId !== null) {
            $stmt->bindParam(':medico_id', $medicoId);
        }
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Exporta dados para array (usado para CSV/Excel)
     */
    public function exportar($filtros = []) {
        // Remove paginação para exportar todos os registros
        return $this->listar($filtros, 999999, 0);
    }
}
