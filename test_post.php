<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste POST - Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .box { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .red { background: #ffebee; }
        .green { background: #e8f5e9; }
    </style>
</head>
<body>
    <h1>🔬 Teste de POST - Perfil</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="box <?php echo empty($_POST['perfil']) ? 'red' : 'green'; ?>">
            <h2>📊 Dados Recebidos no POST:</h2>
            <pre><?php print_r($_POST); ?></pre>
            
            <h3>Campo PERFIL:</h3>
            <p>Valor: <strong>[<?php echo $_POST['perfil'] ?? 'NÃO ENVIADO'; ?>]</strong></p>
            <p>Existe: <?php echo isset($_POST['perfil']) ? 'SIM' : 'NÃO'; ?></p>
            <p>Empty: <?php echo empty($_POST['perfil']) ? 'SIM (vazio)' : 'NÃO (tem valor)'; ?></p>
            <p>Tipo: <?php echo gettype($_POST['perfil'] ?? null); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="box">
        <h2>📝 Formulário de Teste</h2>
        <form method="POST" action="">
            <p>
                <label><strong>Nome:</strong></label><br>
                <input type="text" name="nome" value="Teste" required style="padding: 5px; width: 300px;">
            </p>
            
            <p>
                <label><strong>Perfil:</strong></label><br>
                <select name="perfil" required style="padding: 5px; width: 300px;">
                    <option value="">Selecione...</option>
                    <option value="administrador">Administrador</option>
                    <option value="atendente">Atendente</option>
                    <option value="medico">Médico</option>
                </select>
            </p>
            
            <p>
                <button type="submit" style="padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Enviar Teste
                </button>
            </p>
        </form>
    </div>
    
    <div class="box">
        <h3>ℹ️ Instruções:</h3>
        <ol>
            <li>Selecione um <strong>Perfil</strong> no dropdown</li>
            <li>Clique em <strong>Enviar Teste</strong></li>
            <li>Veja se o campo perfil aparece nos "Dados Recebidos"</li>
            <li>Se aparecer aqui mas não no sistema, o problema é no código do sistema</li>
            <li>Se NÃO aparecer aqui, o problema é no servidor/PHP</li>
        </ol>
    </div>
    
    <div class="box">
        <h3>🔧 Informações do Servidor:</h3>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'; ?></p>
        <p>Request Method: <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
    </div>
</body>
</html>
