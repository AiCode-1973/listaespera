-- =====================================================
-- Atualizar ENUM do campo perfil
-- Adicionar 'atendente' e 'recepção' ao ENUM
-- Data: 04/12/2024
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Ver estrutura atual
SHOW COLUMNS FROM usuarios LIKE 'perfil';

-- Atualizar ENUM para incluir 'atendente' e 'recepção'
ALTER TABLE usuarios 
MODIFY COLUMN perfil ENUM(
    'administrador',
    'atendente',
    'recepcao',
    'recepção',
    'medico'
) NOT NULL DEFAULT 'atendente';

-- Verificar alteração
SHOW COLUMNS FROM usuarios LIKE 'perfil';

-- Atualizar registros com 'recepcao' para 'atendente'
UPDATE usuarios 
SET perfil = 'atendente' 
WHERE perfil IN ('recepcao', 'recepção');

-- Ver resultado
SELECT id, nome, perfil 
FROM usuarios 
ORDER BY id;

-- =====================================================
-- Mensagem de confirmação
-- =====================================================
SELECT 'ENUM atualizado com sucesso! Agora aceita: administrador, atendente, medico' AS STATUS;
