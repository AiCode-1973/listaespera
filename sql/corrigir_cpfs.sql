-- =====================================================
-- Correção: CPFs Inválidos para CPFs Válidos
-- Data: 04/12/2024
-- Descrição: Atualiza CPFs de exemplo com CPFs válidos
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Backup dos CPFs antigos (opcional - descomente se quiser manter histórico)
-- CREATE TABLE IF NOT EXISTS cpfs_backup AS 
-- SELECT id, cpf, nome_paciente FROM fila_espera;

-- Atualizar CPFs inválidos para CPFs válidos
-- Formato: CPF sem formatação (apenas números)

UPDATE fila_espera SET cpf = '11144477735' 
WHERE cpf IN ('12345678901', '123.456.789-01');

UPDATE fila_espera SET cpf = '52998224725' 
WHERE cpf IN ('23456789012', '234.567.890-12');

UPDATE fila_espera SET cpf = '84824824891' 
WHERE cpf IN ('34567890123', '345.678.901-23');

UPDATE fila_espera SET cpf = '73239162858' 
WHERE cpf IN ('45678901234', '456.789.012-34');

UPDATE fila_espera SET cpf = '03619961059' 
WHERE cpf IN ('56789012345', '567.890.123-45');

UPDATE fila_espera SET cpf = '17033986030' 
WHERE cpf IN ('67890123456', '678.901.234-56');

UPDATE fila_espera SET cpf = '45797954040' 
WHERE cpf IN ('78901234567', '789.012.345-67');

UPDATE fila_espera SET cpf = '79476557056' 
WHERE cpf IN ('89012345678', '890.123.456-78');

-- Verificar resultados
SELECT id, nome_paciente, cpf 
FROM fila_espera 
ORDER BY id;

-- =====================================================
-- CPFs Válidos Utilizados:
-- =====================================================
-- 111.444.777-35 (11144477735)
-- 529.982.247-25 (52998224725)
-- 848.248.248-91 (84824824891)
-- 732.391.628-58 (73239162858)
-- 036.199.610-59 (03619961059)
-- 170.339.860-30 (17033986030)
-- 457.979.540-40 (45797954040)
-- 794.765.570-56 (79476557056)
-- =====================================================

-- Mensagem de sucesso
SELECT 'CPFs atualizados com sucesso! Agora você pode editar registros.' AS STATUS;

-- =====================================================
-- Fim da Correção
-- =====================================================
