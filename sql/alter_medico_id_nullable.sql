-- =====================================================
-- Torna o campo medico_id OPCIONAL na tabela fila_espera
-- Execute este SQL no banco de dados
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Altera a coluna medico_id para aceitar NULL
ALTER TABLE fila_espera 
MODIFY COLUMN medico_id INT NULL;

-- Remove a constraint FOREIGN KEY existente
ALTER TABLE fila_espera 
DROP FOREIGN KEY fila_espera_ibfk_1;

-- Recria a FOREIGN KEY permitindo NULL
ALTER TABLE fila_espera 
ADD CONSTRAINT fila_espera_ibfk_1 
FOREIGN KEY (medico_id) REFERENCES medicos(id) 
ON DELETE RESTRICT 
ON UPDATE CASCADE;

-- Verifica a estrutura da tabela
DESCRIBE fila_espera;

-- =====================================================
-- INSTRUÇÕES:
-- 1. Acesse o phpMyAdmin do servidor remoto
-- 2. Selecione o banco: dema5738_lista_espera_hospital
-- 3. Clique em "SQL" e cole este script
-- 4. Execute
-- =====================================================
