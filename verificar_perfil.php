<?php
/**
 * Script para verificar o perfil do usuário logado
 * Use este script para debug
 */

session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verificar Perfil</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196f3; margin: 10px 0; }
        .success { background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; margin: 10px 0; }
        .warning { background: #fff3e0; padding: 10px; border-left: 4px solid #ff9800; margin: 10px 0; }
        .error { background: #ffebee; padding: 10px; border-left: 4px solid #f44336; margin: 10px 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        h1 { color: #333; border-bottom: 2px solid #2196f3; padding-bottom: 10px; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🔍 Verificação de Perfil do Usuário</h1>";

if (!isset($_SESSION['usuario_id'])) {
    echo "<div class='error'>
        <strong>❌ Você não está logado!</strong><br>
        <a href='login.php'>Clique aqui para fazer login</a>
    </div>";
} else {
    echo "<div class='success'>
        <strong>✅ Você está logado!</strong>
    </div>";
    
    echo "<div class='info'>
        <strong>📋 Informações da Sessão:</strong><br><br>
        <strong>ID:</strong> " . htmlspecialchars($_SESSION['usuario_id'] ?? 'N/A') . "<br>
        <strong>Nome:</strong> " . htmlspecialchars($_SESSION['usuario_nome'] ?? 'N/A') . "<br>
        <strong>Email:</strong> " . htmlspecialchars($_SESSION['usuario_email'] ?? 'N/A') . "<br>
        <strong>Perfil:</strong> <code style='font-size: 16px; font-weight: bold; color: #2196f3;'>" . htmlspecialchars($_SESSION['usuario_perfil'] ?? 'N/A') . "</code><br>
    </div>";
    
    $perfil = $_SESSION['usuario_perfil'] ?? '';
    
    // Verificar se tem acesso à agenda
    $temAcessoAgenda = in_array($perfil, ['administrador', 'recepcao', 'atendente']);
    
    if ($temAcessoAgenda) {
        echo "<div class='success'>
            <strong>✅ Você TEM acesso à Agenda!</strong><br>
            Seu perfil (<code>$perfil</code>) está na lista de perfis permitidos.
        </div>";
    } else {
        echo "<div class='error'>
            <strong>❌ Você NÃO tem acesso à Agenda!</strong><br>
            Seu perfil (<code>$perfil</code>) não está na lista de perfis permitidos.<br><br>
            <strong>Perfis permitidos:</strong> administrador, recepcao, atendente
        </div>";
    }
    
    // Verificar se o perfil está correto
    $perfisValidos = ['administrador', 'recepcao', 'atendente', 'medico'];
    if (!in_array($perfil, $perfisValidos)) {
        echo "<div class='warning'>
            <strong>⚠️ ATENÇÃO: Perfil não reconhecido!</strong><br>
            O perfil <code>$perfil</code> não está na lista de perfis válidos do sistema.<br><br>
            <strong>Perfis válidos:</strong> " . implode(', ', $perfisValidos) . "
        </div>";
    }
    
    echo "<div class='info'>
        <strong>🔧 Sessão Completa (Debug):</strong><br>
        <pre style='background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;'>";
    print_r($_SESSION);
    echo "</pre>
    </div>";
}

echo "
        <br>
        <a href='dashboard.php' style='display: inline-block; background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>← Voltar ao Dashboard</a>
        <a href='logout.php' style='display: inline-block; background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-left: 10px;'>🚪 Sair</a>
    </div>
</body>
</html>";
?>
