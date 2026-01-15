-- =====================================================
-- Migração: Adicionar Autorização de Guia para Exames
-- Data: 04/12/2024
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Adicionar campo para autorização de guia (especialmente para exames)
ALTER TABLE fila_espera 
ADD COLUMN guia_autorizada BOOLEAN DEFAULT NULL AFTER tipo_atendimento,
ADD COLUMN data_autorizacao_guia DATE NULL AFTER guia_autorizada,
ADD COLUMN observacao_guia TEXT NULL AFTER data_autorizacao_guia;

-- Adicionar índice para performance
ALTER TABLE fila_espera 
ADD INDEX idx_guia_autorizada (guia_autorizada);

-- =====================================================
-- Comentários sobre os campos:
-- =====================================================
-- guia_autorizada: NULL = não se aplica (Consulta, Retorno, etc)
--                  FALSE = aguardando autorização
--                  TRUE = guia autorizada
-- data_autorizacao_guia: Data em que a guia foi autorizada
-- observacao_guia: Observações sobre a autorização (número da guia, etc)
-- =====================================================

SELECT 'Campo de autorização de guia adicionado com sucesso!' AS STATUS;
