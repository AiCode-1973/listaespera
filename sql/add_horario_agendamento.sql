-- =====================================================
-- Adicionar campo de horário de agendamento
-- Execute este SQL no seu banco de dados
-- =====================================================

USE dema5738_lista_espera_hospital;

ALTER TABLE fila_espera 
ADD COLUMN horario_agendamento TIME NULL AFTER data_agendamento;

-- Adicionar comentário na coluna
ALTER TABLE fila_espera 
MODIFY COLUMN horario_agendamento TIME NULL COMMENT 'Horário do agendamento da consulta/exame';
