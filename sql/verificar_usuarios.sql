-- =====================================================
-- Script de Verificação: Usuários e Perfis
-- Data: 04/12/2024
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Ver TODOS os usuários com seus perfis exatos
SELECT 
    id,
    nome,
    email,
    perfil,
    CONCAT('"', perfil, '"') as perfil_com_aspas,
    LENGTH(perfil) as tamanho_perfil,
    HEX(perfil) as perfil_hex,
    ativo,
    created_at
FROM usuarios
ORDER BY id DESC;

-- Contar usuários por perfil
SELECT 
    perfil,
    COUNT(*) as total
FROM usuarios
GROUP BY perfil;

-- Ver perfis únicos no banco
SELECT DISTINCT perfil 
FROM usuarios
ORDER BY perfil;
