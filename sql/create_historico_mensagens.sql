-- =====================================================
-- Tabela para Histórico de Mensagens WhatsApp
-- Registra todas as mensagens enviadas pelo sistema
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Remove a tabela se existir
DROP TABLE IF EXISTS historico_mensagens;

-- Cria tabela SEM foreign keys primeiro
CREATE TABLE historico_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fila_espera_id INT NULL,
    paciente_id INT NULL,
    usuario_id INT NULL,
    telefone VARCHAR(20) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo_mensagem VARCHAR(50) DEFAULT 'confirmacao_agendamento',
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para otimização
    INDEX idx_fila_espera (fila_espera_id),
    INDEX idx_paciente (paciente_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_data_envio (data_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela criada com sucesso!
-- Campos: id, fila_espera_id, paciente_id, usuario_id, telefone, mensagem, tipo_mensagem, data_envio
