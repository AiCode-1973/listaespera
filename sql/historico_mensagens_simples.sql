-- =====================================================
-- SCRIPT SIMPLIFICADO - Histórico de Mensagens WhatsApp
-- Execute este SQL no phpMyAdmin
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Remove se existir
DROP TABLE IF EXISTS historico_mensagens;

-- Cria a tabela
CREATE TABLE historico_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fila_espera_id INT NULL COMMENT 'ID do registro na fila de espera',
    paciente_id INT NULL COMMENT 'Reservado para futuro uso',
    usuario_id INT NULL COMMENT 'ID do usuário que enviou',
    telefone VARCHAR(20) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo_mensagem VARCHAR(50) DEFAULT 'confirmacao_agendamento',
    data_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fila_espera (fila_espera_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_data_envio (data_envio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pronto! Tabela criada com sucesso!
