<?php
/**
 * Gerador de Hash de Senha
 * Use este script para gerar o hash correto das senhas
 */

header('Content-Type: text/html; charset=utf-8');

// Gera hash para a senha "admin123"
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Senha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-3xl w-full">
        <div class="text-center mb-6">
            <i class="fas fa-key text-blue-600 text-6xl mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Gerador de Hash de Senha</h1>
        </div>

        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
            <h3 class="font-bold text-green-800 mb-2">Hash Gerado com Sucesso!</h3>
            <p class="text-sm text-green-700">Use este hash no SQL para a senha: <strong>admin123</strong></p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Hash Bcrypt:</label>
            <textarea readonly class="w-full p-3 border border-gray-300 rounded font-mono text-sm bg-white" rows="3" onclick="this.select()"><?php echo $hash; ?></textarea>
            <p class="text-xs text-gray-500 mt-2">Clique no texto acima para selecionar e copiar</p>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded mb-6">
            <h3 class="font-bold text-blue-800 mb-2">SQL para Corrigir Usuários</h3>
            <p class="text-sm text-blue-700 mb-3">Execute este SQL no phpMyAdmin do servidor remoto:</p>
            <textarea readonly class="w-full p-3 border border-gray-300 rounded font-mono text-xs bg-white" rows="8" onclick="this.select()">USE dema5738_lista_espera_hospital;

UPDATE usuarios SET senha_hash = '<?php echo $hash; ?>' WHERE email = 'admin@hospital.com';
UPDATE usuarios SET senha_hash = '<?php echo $hash; ?>' WHERE email = 'recepcao@hospital.com';
UPDATE usuarios SET senha_hash = '<?php echo $hash; ?>' WHERE email = 'medico@hospital.com';

SELECT id, nome, email, perfil FROM usuarios;</textarea>
            <p class="text-xs text-gray-500 mt-2">Este comando atualizará a senha de todos os usuários para "admin123"</p>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded mb-6">
            <h3 class="font-bold text-yellow-800 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Teste de Login</h3>
            <p class="text-sm text-yellow-700 mb-3">Vamos testar se a função de verificação de senha funciona:</p>
            
            <?php
            // Testa a verificação de senha
            $senhaDigitada = 'admin123';
            $resultado = password_verify($senhaDigitada, $hash);
            ?>
            
            <div class="bg-white p-3 rounded border">
                <p class="text-sm"><strong>Senha testada:</strong> <?php echo $senhaDigitada; ?></p>
                <p class="text-sm"><strong>Hash usado:</strong> <?php echo substr($hash, 0, 30); ?>...</p>
                <p class="text-sm mt-2">
                    <strong>Resultado:</strong> 
                    <?php if ($resultado): ?>
                        <span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> VÁLIDA ✓</span>
                    <?php else: ?>
                        <span class="text-red-600 font-bold"><i class="fas fa-times-circle"></i> INVÁLIDA ✗</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded mb-6">
            <h3 class="font-bold text-purple-800 mb-2"><i class="fas fa-database mr-2"></i>Verificar Usuários no Banco</h3>
            <p class="text-sm text-purple-700 mb-3">Execute este SQL para ver os usuários atuais:</p>
            <textarea readonly class="w-full p-3 border border-gray-300 rounded font-mono text-xs bg-white" rows="3" onclick="this.select()">USE dema5738_lista_espera_hospital;
SELECT id, nome, email, perfil, LEFT(senha_hash, 30) as hash_inicio, ativo FROM usuarios;</textarea>
        </div>

        <?php
        // Tenta conectar ao banco e verificar usuários
        try {
            require_once __DIR__ . '/config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
            
            echo '<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">';
            echo '<h3 class="font-bold text-green-800 mb-3"><i class="fas fa-check-circle mr-2"></i>Conexão OK - Usuários no Banco</h3>';
            
            $stmt = $conn->query("SELECT id, nome, email, perfil, senha_hash, ativo FROM usuarios");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($usuarios) > 0) {
                echo '<table class="w-full text-sm border">';
                echo '<thead class="bg-gray-200">';
                echo '<tr>';
                echo '<th class="p-2 text-left">ID</th>';
                echo '<th class="p-2 text-left">Nome</th>';
                echo '<th class="p-2 text-left">E-mail</th>';
                echo '<th class="p-2 text-left">Perfil</th>';
                echo '<th class="p-2 text-left">Senha Funciona?</th>';
                echo '<th class="p-2 text-left">Ativo</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                foreach ($usuarios as $user) {
                    // Testa se a senha admin123 funciona para este usuário
                    $senhaFunciona = password_verify('admin123', $user['senha_hash']);
                    
                    echo '<tr class="border-t hover:bg-gray-50">';
                    echo '<td class="p-2">' . $user['id'] . '</td>';
                    echo '<td class="p-2">' . htmlspecialchars($user['nome']) . '</td>';
                    echo '<td class="p-2">' . htmlspecialchars($user['email']) . '</td>';
                    echo '<td class="p-2"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">' . $user['perfil'] . '</span></td>';
                    echo '<td class="p-2">';
                    if ($senhaFunciona) {
                        echo '<span class="text-green-600 font-bold"><i class="fas fa-check"></i> SIM</span>';
                    } else {
                        echo '<span class="text-red-600 font-bold"><i class="fas fa-times"></i> NÃO - Precisa Atualizar!</span>';
                    }
                    echo '</td>';
                    echo '<td class="p-2">';
                    echo $user['ativo'] ? '<span class="text-green-600">Sim</span>' : '<span class="text-red-600">Não</span>';
                    echo '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
                
                // Verifica se alguma senha não funciona
                $senhasProblematicas = array_filter($usuarios, function($u) {
                    return !password_verify('admin123', $u['senha_hash']);
                });
                
                if (count($senhasProblematicas) > 0) {
                    echo '<div class="mt-4 p-3 bg-red-100 border border-red-400 rounded">';
                    echo '<p class="text-red-800 font-bold"><i class="fas fa-exclamation-triangle mr-2"></i>PROBLEMA ENCONTRADO!</p>';
                    echo '<p class="text-red-700 text-sm mt-2">';
                    echo count($senhasProblematicas) . ' usuário(s) com senha incorreta. ';
                    echo 'Execute o SQL de correção acima para resolver.';
                    echo '</p>';
                    echo '</div>';
                } else {
                    echo '<div class="mt-4 p-3 bg-green-100 border border-green-400 rounded">';
                    echo '<p class="text-green-800 font-bold"><i class="fas fa-check-circle mr-2"></i>Todas as senhas estão OK!</p>';
                    echo '<p class="text-green-700 text-sm mt-2">Você pode fazer login com: admin123</p>';
                    echo '</div>';
                }
                
            } else {
                echo '<p class="text-orange-600">Nenhum usuário encontrado no banco. Execute o schema.sql completo.</p>';
            }
            
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded mb-6">';
            echo '<h3 class="font-bold text-red-800 mb-2"><i class="fas fa-times-circle mr-2"></i>Erro de Conexão</h3>';
            echo '<p class="text-sm text-red-700">Não foi possível conectar ao banco de dados:</p>';
            echo '<p class="text-xs text-red-600 mt-2 font-mono bg-red-100 p-2 rounded">' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p class="text-sm text-red-700 mt-3">Verifique as credenciais em config/database.php</p>';
            echo '</div>';
        }
        ?>

        <div class="mt-6 space-y-3">
            <a href="/listaespera/testar_conexao.php" class="block text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-network-wired mr-2"></i>Testar Conexão com Banco
            </a>
            <a href="/listaespera/login.php" class="block text-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Ir para o Login
            </a>
        </div>

        <div class="mt-6 p-4 bg-gray-100 rounded-lg">
            <h3 class="font-bold text-gray-800 mb-2">📋 Passo a Passo para Resolver</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                <li>Copie o SQL de correção acima</li>
                <li>Acesse o phpMyAdmin do servidor remoto (186.209.113.107)</li>
                <li>Selecione o banco: dema5738_lista_espera_hospital</li>
                <li>Clique na aba "SQL"</li>
                <li>Cole e execute o comando UPDATE</li>
                <li>Volte aqui e recarregue a página para verificar</li>
                <li>Tente fazer login com: admin@hospital.com / admin123</li>
            </ol>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>⚠️ <strong>Importante:</strong> Remova este arquivo após corrigir as senhas</p>
            <p class="mt-2">Arquivo: gerar_senha.php</p>
        </div>
    </div>
</body>
</html>
