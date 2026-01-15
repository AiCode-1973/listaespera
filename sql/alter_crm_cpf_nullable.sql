-- =====================================================
-- Tornar campo CRM/CPF opcional (NULL)
-- Execute este SQL no seu banco de dados
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Altera a coluna crm_cpf para permitir valores NULL
ALTER TABLE medicos 
MODIFY COLUMN crm_cpf VARCHAR(50) NULL;

-- Remove a restrição UNIQUE se existir (para permitir múltiplos valores NULL)
-- ALTER TABLE medicos 
-- DROP INDEX crm_cpf;

-- Adiciona índice não-único para melhor performance (opcional)
-- CREATE INDEX idx_crm_cpf ON medicos(crm_cpf);
