-- =====================================================
-- Script para Corrigir Senhas dos Usuários
-- Execute este SQL no phpMyAdmin do servidor remoto
-- =====================================================
-- 
-- PROBLEMA: As senhas no banco não correspondem a "admin123"
-- SOLUÇÃO: Este script atualiza todas as senhas para "admin123"
--
-- Data: 04/12/2025
-- =====================================================

USE dema5738_lista_espera_hospital;

-- Atualiza a senha de todos os usuários para: admin123
-- Hash gerado com password_hash('admin123', PASSWORD_DEFAULT)

UPDATE usuarios 
SET senha_hash = '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm' 
WHERE email = 'admin@hospital.com';

UPDATE usuarios 
SET senha_hash = '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm' 
WHERE email = 'recepcao@hospital.com';

UPDATE usuarios 
SET senha_hash = '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm' 
WHERE email = 'medico@hospital.com';

-- Verifica se as senhas foram atualizadas
SELECT 
    id, 
    nome, 
    email, 
    perfil, 
    LEFT(senha_hash, 30) as hash_inicio, 
    ativo,
    'Senha: admin123' as observacao
FROM usuarios
ORDER BY id;

-- =====================================================
-- INSTRUÇÕES:
-- =====================================================
-- 1. Acesse o phpMyAdmin do servidor: 186.209.113.107
-- 2. Faça login com suas credenciais
-- 3. Selecione o banco: dema5738_lista_espera_hospital
-- 4. Clique na aba "SQL"
-- 5. Copie e cole TODO este script
-- 6. Clique em "Executar"
-- 7. Verifique se apareceram os 3 usuários na tabela de resultado
-- 8. Tente fazer login no sistema com:
--    Email: admin@hospital.com
--    Senha: admin123
-- =====================================================

-- =====================================================
-- NOTA: Se você ainda tiver problemas, use o arquivo:
-- gerar_senha.php (http://localhost/listaespera/gerar_senha.php)
-- Ele irá gerar um novo hash e testar a conexão
-- =====================================================
