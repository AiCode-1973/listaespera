<?php
/**
 * Script de Teste de Conexão MySQL Remota
 * Use este arquivo para verificar se a conexão está funcionando
 */

// Impedir cache
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão MySQL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-2xl w-full">
        <div class="text-center mb-6">
            <i class="fas fa-database text-blue-600 text-6xl mb-4"></i>
            <h1 class="text-3xl font-bold text-gray-800">Teste de Conexão MySQL</h1>
            <p class="text-gray-600 mt-2">Verificando conexão com o banco de dados remoto</p>
        </div>

        <div class="space-y-4">
            <?php
            // Informações de conexão
            $host = '186.209.113.107';
            $dbname = 'dema5738_lista_espera_hospital';
            $username = 'dema5738_lista_espera_hospital';
            $password = 'Dema@1973';
            
            echo '<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">';
            echo '<h3 class="font-bold text-blue-800 mb-2"><i class="fas fa-info-circle mr-2"></i>Informações de Conexão</h3>';
            echo '<table class="w-full text-sm">';
            echo '<tr><td class="py-1 font-semibold">Host:</td><td>' . htmlspecialchars($host) . '</td></tr>';
            echo '<tr><td class="py-1 font-semibold">Banco:</td><td>' . htmlspecialchars($dbname) . '</td></tr>';
            echo '<tr><td class="py-1 font-semibold">Usuário:</td><td>' . htmlspecialchars($username) . '</td></tr>';
            echo '<tr><td class="py-1 font-semibold">Senha:</td><td>' . str_repeat('*', strlen($password)) . '</td></tr>';
            echo '</table>';
            echo '</div>';

            // Teste 1: Extensão PDO
            echo '<div class="border-l-4 border-gray-400 bg-gray-50 p-4 rounded">';
            echo '<h3 class="font-bold text-gray-800 mb-2"><i class="fas fa-check-circle mr-2"></i>Teste 1: Extensão PDO</h3>';
            if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
                echo '<p class="text-green-600"><i class="fas fa-check mr-2"></i>PDO e PDO_MySQL estão instalados</p>';
                $testePDO = true;
            } else {
                echo '<p class="text-red-600"><i class="fas fa-times mr-2"></i>PDO ou PDO_MySQL NÃO estão instalados</p>';
                echo '<p class="text-sm text-gray-600 mt-2">Ative as extensões no php.ini</p>';
                $testePDO = false;
            }
            echo '</div>';

            // Teste 2: Conexão
            if ($testePDO) {
                echo '<div class="border-l-4 border-gray-400 bg-gray-50 p-4 rounded">';
                echo '<h3 class="font-bold text-gray-800 mb-2"><i class="fas fa-network-wired mr-2"></i>Teste 2: Conexão TCP/IP</h3>';
                
                try {
                    $inicio = microtime(true);
                    
                    $conn = new PDO(
                        "mysql:host={$host};port=3306;charset=utf8mb4",
                        $username,
                        $password,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_TIMEOUT => 5
                        ]
                    );
                    
                    $tempo = round((microtime(true) - $inicio) * 1000, 2);
                    
                    echo '<p class="text-green-600"><i class="fas fa-check mr-2"></i>Conexão estabelecida com sucesso!</p>';
                    echo '<p class="text-sm text-gray-600 mt-1">Tempo de resposta: ' . $tempo . ' ms</p>';
                    $conexaoOK = true;
                    
                } catch (PDOException $e) {
                    echo '<p class="text-red-600"><i class="fas fa-times mr-2"></i>Falha na conexão</p>';
                    echo '<p class="text-sm text-red-500 mt-2 font-mono bg-red-50 p-2 rounded">' . htmlspecialchars($e->getMessage()) . '</p>';
                    $conexaoOK = false;
                }
                echo '</div>';

                // Teste 3: Selecionar banco
                if ($conexaoOK) {
                    echo '<div class="border-l-4 border-gray-400 bg-gray-50 p-4 rounded">';
                    echo '<h3 class="font-bold text-gray-800 mb-2"><i class="fas fa-database mr-2"></i>Teste 3: Banco de Dados</h3>';
                    
                    try {
                        $conn->exec("USE {$dbname}");
                        echo '<p class="text-green-600"><i class="fas fa-check mr-2"></i>Banco de dados selecionado com sucesso</p>';
                        $bancoOK = true;
                        
                    } catch (PDOException $e) {
                        echo '<p class="text-red-600"><i class="fas fa-times mr-2"></i>Banco de dados não encontrado</p>';
                        echo '<p class="text-sm text-gray-600 mt-2">Execute o script schema.sql para criar o banco e as tabelas</p>';
                        $bancoOK = false;
                    }
                    echo '</div>';

                    // Teste 4: Listar tabelas
                    if ($bancoOK) {
                        echo '<div class="border-l-4 border-gray-400 bg-gray-50 p-4 rounded">';
                        echo '<h3 class="font-bold text-gray-800 mb-2"><i class="fas fa-table mr-2"></i>Teste 4: Estrutura do Banco</h3>';
                        
                        try {
                            $stmt = $conn->query("SHOW TABLES");
                            $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                            
                            if (count($tabelas) > 0) {
                                echo '<p class="text-green-600"><i class="fas fa-check mr-2"></i>Encontradas ' . count($tabelas) . ' tabelas:</p>';
                                echo '<ul class="mt-2 space-y-1 text-sm">';
                                foreach ($tabelas as $tabela) {
                                    echo '<li class="ml-4"><i class="fas fa-caret-right text-blue-500 mr-2"></i>' . htmlspecialchars($tabela) . '</li>';
                                }
                                echo '</ul>';
                                
                                // Verificar tabelas esperadas
                                $tabelasEsperadas = ['usuarios', 'medicos', 'especialidades', 'convenios', 'medico_especialidade', 'fila_espera'];
                                $tabelasFaltando = array_diff($tabelasEsperadas, $tabelas);
                                
                                if (empty($tabelasFaltando)) {
                                    echo '<p class="text-green-600 mt-3"><i class="fas fa-check-double mr-2"></i>Todas as tabelas necessárias estão presentes!</p>';
                                } else {
                                    echo '<p class="text-orange-600 mt-3"><i class="fas fa-exclamation-triangle mr-2"></i>Tabelas faltando:</p>';
                                    echo '<ul class="mt-1 space-y-1 text-sm text-orange-700">';
                                    foreach ($tabelasFaltando as $faltando) {
                                        echo '<li class="ml-4">• ' . htmlspecialchars($faltando) . '</li>';
                                    }
                                    echo '</ul>';
                                }
                                
                            } else {
                                echo '<p class="text-orange-600"><i class="fas fa-exclamation-triangle mr-2"></i>Banco existe mas não há tabelas</p>';
                                echo '<p class="text-sm text-gray-600 mt-2">Execute o script schema.sql para criar as tabelas</p>';
                            }
                            
                        } catch (PDOException $e) {
                            echo '<p class="text-red-600"><i class="fas fa-times mr-2"></i>Erro ao listar tabelas</p>';
                            echo '<p class="text-sm text-red-500 mt-2 font-mono bg-red-50 p-2 rounded">' . htmlspecialchars($e->getMessage()) . '</p>';
                        }
                        echo '</div>';

                        // Teste 5: Verificar usuários
                        if (!empty($tabelas) && in_array('usuarios', $tabelas)) {
                            echo '<div class="border-l-4 border-gray-400 bg-gray-50 p-4 rounded">';
                            echo '<h3 class="font-bold text-gray-800 mb-2"><i class="fas fa-users mr-2"></i>Teste 5: Dados de Exemplo</h3>';
                            
                            try {
                                $stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios");
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                if ($result['total'] > 0) {
                                    echo '<p class="text-green-600"><i class="fas fa-check mr-2"></i>Encontrados ' . $result['total'] . ' usuários cadastrados</p>';
                                    
                                    $stmt = $conn->query("SELECT nome, email, perfil FROM usuarios LIMIT 5");
                                    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    echo '<table class="mt-2 w-full text-sm border">';
                                    echo '<thead class="bg-gray-200"><tr><th class="p-2 text-left">Nome</th><th class="p-2 text-left">E-mail</th><th class="p-2 text-left">Perfil</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($usuarios as $user) {
                                        echo '<tr class="border-t">';
                                        echo '<td class="p-2">' . htmlspecialchars($user['nome']) . '</td>';
                                        echo '<td class="p-2">' . htmlspecialchars($user['email']) . '</td>';
                                        echo '<td class="p-2"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">' . htmlspecialchars($user['perfil']) . '</span></td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table>';
                                } else {
                                    echo '<p class="text-orange-600"><i class="fas fa-exclamation-triangle mr-2"></i>Tabela usuarios existe mas está vazia</p>';
                                    echo '<p class="text-sm text-gray-600 mt-2">Execute o script schema.sql completo para inserir dados de exemplo</p>';
                                }
                                
                            } catch (PDOException $e) {
                                echo '<p class="text-red-600"><i class="fas fa-times mr-2"></i>Erro ao consultar usuários</p>';
                            }
                            echo '</div>';
                        }
                    }
                }
            }

            // Resumo final
            echo '<div class="mt-6 p-4 rounded-lg ' . (isset($bancoOK) && $bancoOK ? 'bg-green-100 border-green-500' : 'bg-red-100 border-red-500') . ' border-l-4">';
            echo '<h3 class="font-bold text-lg mb-2">';
            if (isset($bancoOK) && $bancoOK) {
                echo '<i class="fas fa-check-circle text-green-600 mr-2"></i><span class="text-green-800">Sistema Pronto!</span>';
                echo '</h3>';
                echo '<p class="text-green-700">A conexão com o banco de dados remoto está funcionando perfeitamente.</p>';
                echo '<p class="mt-3"><a href="/listaespera/login.php" class="inline-block bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition">';
                echo '<i class="fas fa-sign-in-alt mr-2"></i>Acessar o Sistema</a></p>';
            } else {
                echo '<i class="fas fa-times-circle text-red-600 mr-2"></i><span class="text-red-800">Problemas Detectados</span>';
                echo '</h3>';
                echo '<p class="text-red-700">Verifique os erros acima e siga as instruções em CONFIGURACAO_MYSQL_REMOTO.md</p>';
            }
            echo '</div>';
            ?>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>⚠️ <strong>Importante:</strong> Remova este arquivo após a configuração por segurança</p>
            <p class="mt-2">Arquivo: testar_conexao.php</p>
        </div>
    </div>
</body>
</html>
