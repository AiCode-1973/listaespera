-- =====================================================
-- Migração: Atualizar perfil "recepcao" para "atendente"
-- Data: 04/12/2024
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Verificar quantos usuários têm perfil "recepcao" ou "recepção"
SELECT perfil, COUNT(*) as total 
FROM usuarios 
WHERE perfil IN ('recepcao', 'recepção', 'Recepção')
GROUP BY perfil;

-- Atualizar perfil de "recepcao" e "recepção" para "atendente"
UPDATE usuarios 
SET perfil = 'atendente' 
WHERE perfil IN ('recepcao', 'recepção', 'Recepção');

-- Verificar resultado
SELECT id, nome, email, perfil, ativo 
FROM usuarios 
ORDER BY perfil, nome;

-- =====================================================
-- Mensagem de confirmação
-- =====================================================
SELECT 'Perfis atualizados com sucesso! Todos os usuários "recepcao" agora são "atendente".' AS STATUS;
