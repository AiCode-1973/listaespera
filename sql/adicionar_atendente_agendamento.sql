-- =====================================================
-- Adicionar campo de atendente responsável pelo agendamento
-- Data: 04/12/2024
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Verificar se o campo agendado_por existe
SELECT COLUMN_NAME, DATA_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'dema5738_lista_espera_hospital' 
  AND TABLE_NAME = 'fila_espera' 
  AND COLUMN_NAME IN ('agendado_por', 'usuario_agendamento_id');

-- Adicionar campo usuario_agendamento_id se não existir
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'dema5738_lista_espera_hospital' 
                 AND TABLE_NAME = 'fila_espera' 
                 AND COLUMN_NAME = 'usuario_agendamento_id');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE fila_espera ADD COLUMN usuario_agendamento_id INT NULL AFTER data_agendamento',
    'SELECT "Campo usuario_agendamento_id já existe" AS INFO');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- Adicionar data_hora_agendamento se não existir
SET @exist2 := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = 'dema5738_lista_espera_hospital' 
                  AND TABLE_NAME = 'fila_espera' 
                  AND COLUMN_NAME = 'data_hora_agendamento');

SET @sqlstmt2 := IF(@exist2 = 0, 
    'ALTER TABLE fila_espera ADD COLUMN data_hora_agendamento DATETIME NULL AFTER usuario_agendamento_id',
    'SELECT "Campo data_hora_agendamento já existe" AS INFO');
PREPARE stmt2 FROM @sqlstmt2;
EXECUTE stmt2;

-- Adicionar foreign key se não existir
SET @fkexist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = 'dema5738_lista_espera_hospital' 
                   AND TABLE_NAME = 'fila_espera' 
                   AND CONSTRAINT_NAME = 'fk_fila_espera_usuario_agendamento');

SET @sqlstmt3 := IF(@fkexist = 0, 
    'ALTER TABLE fila_espera ADD CONSTRAINT fk_fila_espera_usuario_agendamento FOREIGN KEY (usuario_agendamento_id) REFERENCES usuarios(id) ON DELETE SET NULL',
    'SELECT "FK fk_fila_espera_usuario_agendamento já existe" AS INFO');
PREPARE stmt3 FROM @sqlstmt3;
EXECUTE stmt3;

-- Adicionar índice se não existir
SET @idxexist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
                  WHERE TABLE_SCHEMA = 'dema5738_lista_espera_hospital' 
                    AND TABLE_NAME = 'fila_espera' 
                    AND INDEX_NAME = 'idx_usuario_agendamento');

SET @sqlstmt4 := IF(@idxexist = 0, 
    'CREATE INDEX idx_usuario_agendamento ON fila_espera(usuario_agendamento_id)',
    'SELECT "Index idx_usuario_agendamento já existe" AS INFO');
PREPARE stmt4 FROM @sqlstmt4;
EXECUTE stmt4;

-- Verificar estrutura final
DESCRIBE fila_espera;

-- =====================================================
-- Mensagem de confirmação
-- =====================================================
SELECT 'Campos de atendente agendamento verificados/adicionados com sucesso!' AS STATUS;
