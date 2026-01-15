-- =====================================================
-- Migração: Adicionar Funcionalidade de URGÊNCIA
-- Data: 04/12/2024
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Adicionar novos campos à tabela fila_espera
ALTER TABLE fila_espera 
ADD COLUMN urgente BOOLEAN DEFAULT FALSE AFTER agendado_por,
ADD COLUMN motivo_urgencia TEXT NULL AFTER urgente,
ADD COLUMN tipo_atendimento VARCHAR(50) NULL AFTER motivo_urgencia;

-- Adicionar índices para melhor performance
ALTER TABLE fila_espera 
ADD INDEX idx_urgente (urgente),
ADD INDEX idx_tipo_atendimento (tipo_atendimento);

-- =====================================================
-- Comentários sobre os campos:
-- =====================================================
-- urgente: Campo booleano que indica se o paciente é urgente
-- motivo_urgencia: Campo de texto obrigatório quando urgente=TRUE
-- tipo_atendimento: Valores possíveis: 
--   - 'Consulta'
--   - 'Exame'
--   - 'Consulta + Exame'
--   - 'Retorno'
--   - 'Procedimento'
-- =====================================================

-- Exemplo de atualização de registros existentes (opcional)
-- UPDATE fila_espera SET tipo_atendimento = informacao WHERE informacao IN ('Consulta', 'Exame', 'Retorno', 'Procedimento');

-- =====================================================
-- Fim da Migração
-- =====================================================
