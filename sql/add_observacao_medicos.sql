-- Adiciona campo de observação na tabela médicos
-- Execute este script no banco de dados

ALTER TABLE medicos 
ADD COLUMN observacao TEXT NULL COMMENT 'Observações adicionais sobre o médico';
