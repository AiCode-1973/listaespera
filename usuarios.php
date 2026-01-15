<?php
/**
 * Gerenciamento de Usuários
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação e permissão (somente administradores)
$auth = new AuthController();
$auth->verificarAutenticacao();
$auth->verificarPermissao(['administrador']);

$usuarioModel = new Usuario();
$erros = [];

// Processar ações
$acao = $_GET['acao'] ?? '';

// Inativar
if ($acao === 'inativar' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Não permitir inativar o próprio usuário
    if ($id == $_SESSION['usuario_id']) {
        $_SESSION['mensagem_erro'] = 'Você não pode inativar sua própria conta';
    } else {
        if ($usuarioModel->inativar($id)) {
            $_SESSION['mensagem_sucesso'] = 'Usuário inativado com sucesso';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao inativar usuário';
        }
    }
    header('Location: ' . BASE_PATH . 'usuarios.php');
    exit();
}

// Deletar (permanente)
if ($acao === 'deletar' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Não permitir deletar o próprio usuário
    if ($id == $_SESSION['usuario_id']) {
        $_SESSION['mensagem_erro'] = 'Você não pode deletar sua própria conta';
    } else {
        if ($usuarioModel->deletar($id)) {
            $_SESSION['mensagem_sucesso'] = 'Usuário deletado com sucesso';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao deletar usuário';
        }
    }
    header('Location: ' . BASE_PATH . 'usuarios.php');
    exit();
}

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    // DEBUG: Mostrar tudo que foi recebido
    error_log("==================== POST RECEBIDO ====================");
    error_log("ID: " . ($id ?? 'NOVO'));
    error_log("Nome: [" . ($_POST['nome'] ?? 'VAZIO') . "]");
    error_log("Email: [" . ($_POST['email'] ?? 'VAZIO') . "]");
    error_log("Perfil: [" . ($_POST['perfil'] ?? 'VAZIO') . "]");
    error_log("Ativo: " . (isset($_POST['ativo']) ? 'SIM' : 'NÃO'));
    error_log("======================================================");
    
    // Validações
    if (empty($_POST['nome'])) {
        $erros[] = 'Nome é obrigatório';
    }
    
    if (empty($_POST['email'])) {
        $erros[] = 'E-mail é obrigatório';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'E-mail inválido';
    }
    
    if (empty($_POST['perfil'])) {
        $erros[] = 'Perfil é obrigatório';
    } elseif (!in_array($_POST['perfil'], ['administrador', 'atendente', 'medico', 'recepcao', 'recepção'])) {
        $erros[] = 'Perfil inválido';
    }
    
    // Validar senha (obrigatória apenas para novo usuário)
    if (!$id && empty($_POST['senha'])) {
        $erros[] = 'Senha é obrigatória para novo usuário';
    }
    
    if (!empty($_POST['senha'])) {
        if (strlen($_POST['senha']) < 6) {
            $erros[] = 'Senha deve ter no mínimo 6 caracteres';
        }
        
        if ($_POST['senha'] !== $_POST['confirmar_senha']) {
            $erros[] = 'Senhas não conferem';
        }
    }
    
    // Verifica duplicidade de e-mail
    if ($usuarioModel->emailExiste($_POST['email'], $id)) {
        $erros[] = 'Já existe um usuário com este e-mail';
    }
    
    if (empty($erros)) {
        // DEBUG: Ver o que está vindo no POST
        error_log("POST recebido: " . print_r($_POST, true));
        error_log("Perfil no POST: [" . ($_POST['perfil'] ?? 'VAZIO') . "]");
        
        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'perfil' => $_POST['perfil'] ?? '',
            'ativo' => isset($_POST['ativo']) ? 1 : 0
        ];
        
        // Adicionar senha se foi fornecida
        if (!empty($_POST['senha'])) {
            $dados['senha'] = $_POST['senha'];
        }
        
        if ($id) {
            if ($usuarioModel->atualizar($id, $dados)) {
                $_SESSION['mensagem_sucesso'] = 'Usuário atualizado com sucesso';
                header('Location: ' . BASE_PATH . 'usuarios.php');
                exit();
            } else {
                $erros[] = 'Erro ao atualizar usuário';
            }
        } else {
            $dados['ativo'] = 1; // Novo usuário sempre ativo
            
            // DEBUG: Verificação crítica
            error_log("==================== ANTES DE CRIAR ====================");
            error_log("Dados completos: " . print_r($dados, true));
            error_log("Perfil: [" . $dados['perfil'] . "] (Length: " . strlen($dados['perfil']) . ")");
            error_log("========================================================");
            
            // Validação adicional
            if (empty($dados['perfil'])) {
                $erros[] = 'ERRO CRÍTICO: Perfil está vazio antes do INSERT! POST perfil: [' . ($_POST['perfil'] ?? 'VAZIO') . ']';
                error_log("ERRO: Perfil vazio detectado!");
            } else {
                $novoId = $usuarioModel->criar($dados);
                if ($novoId) {
                    $_SESSION['mensagem_sucesso'] = 'Usuário criado com sucesso! (ID: ' . $novoId . ' | Perfil: ' . $dados['perfil'] . ')';
                    // Redireciona sem filtros para mostrar todos os usuários
                    header('Location: ' . BASE_PATH . 'usuarios.php');
                    exit();
                } else {
                    $erros[] = 'Erro ao criar usuário. Verifique se o e-mail já não está cadastrado.';
                }
            }
        }
    }
}

// Buscar usuário para edição
$usuarioEdicao = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $usuarioEdicao = $usuarioModel->buscarPorId($_GET['id']);
}

// Listar usuários com filtros
$busca = $_GET['busca'] ?? '';
$filtroPerfil = $_GET['perfil'] ?? '';
$filtroAtivo = isset($_GET['ativo']) ? (int)$_GET['ativo'] : '';

$filtros = [];
if ($busca) $filtros['busca'] = $busca;
if ($filtroPerfil) $filtros['perfil'] = $filtroPerfil;
if ($filtroAtivo !== '') $filtros['ativo'] = $filtroAtivo;

$usuarios = $usuarioModel->listar($filtros);

$pageTitle = 'Usuários';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
        <i class="fas fa-users mr-3"></i>Usuários
    </h1>
    <p class="text-gray-600">Gerencie os usuários do sistema</p>
</div>

<!-- Mensagens -->
<?php if (!empty($erros)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
    <ul class="list-disc list-inside">
        <?php foreach ($erros as $erro): ?>
        <li><?php echo htmlspecialchars($erro); ?></li>
        <?php endforeach; ?>
    </ul>
    <!-- DEBUG: Mostrar dados recebidos -->
    <div class="mt-4 p-3 bg-white rounded text-xs font-mono">
        <strong>DEBUG - Dados Recebidos:</strong><br>
        Nome: [<?php echo htmlspecialchars($_POST['nome'] ?? 'VAZIO'); ?>]<br>
        Email: [<?php echo htmlspecialchars($_POST['email'] ?? 'VAZIO'); ?>]<br>
        Perfil: [<?php echo htmlspecialchars($_POST['perfil'] ?? 'VAZIO'); ?>]<br>
        Ativo: <?php echo isset($_POST['ativo']) ? 'SIM' : 'NÃO'; ?>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['mensagem_sucesso'])): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
    <?php echo htmlspecialchars($_SESSION['mensagem_sucesso']); unset($_SESSION['mensagem_sucesso']); ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['mensagem_erro'])): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
    <?php echo htmlspecialchars($_SESSION['mensagem_erro']); unset($_SESSION['mensagem_erro']); ?>
</div>
<?php endif; ?>

<!-- Formulário -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        <?php echo $usuarioEdicao ? 'Editar' : 'Novo'; ?> Usuário
    </h2>
    
    <form method="POST" action="" class="space-y-4">
        <?php if ($usuarioEdicao): ?>
        <input type="hidden" name="id" value="<?php echo $usuarioEdicao['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       required
                       value="<?php echo htmlspecialchars($usuarioEdicao['nome'] ?? $_POST['nome'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    E-mail <span class="text-red-500">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       required
                       value="<?php echo htmlspecialchars($usuarioEdicao['email'] ?? $_POST['email'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Perfil <span class="text-red-500">*</span>
                </label>
                <select name="perfil" 
                        id="select_perfil"
                        required
                        onchange="console.log('Perfil selecionado:', this.value)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione...</option>
                    <option value="administrador" <?php echo (($usuarioEdicao['perfil'] ?? $_POST['perfil'] ?? '') === 'administrador') ? 'selected' : ''; ?>>
                        Administrador
                    </option>
                    <option value="atendente" <?php echo (($usuarioEdicao['perfil'] ?? $_POST['perfil'] ?? '') === 'atendente') ? 'selected' : ''; ?>>
                        Atendente
                    </option>
                    <?php if (in_array(($usuarioEdicao['perfil'] ?? ''), ['recepcao', 'recepção'])): ?>
                    <option value="<?php echo $usuarioEdicao['perfil']; ?>" selected style="background-color: #fef3c7;">
                        ⚠️ Recepção (Atualizar para Atendente)
                    </option>
                    <?php endif; ?>
                    <option value="medico" <?php echo (($usuarioEdicao['perfil'] ?? $_POST['perfil'] ?? '') === 'medico') ? 'selected' : ''; ?>>
                        Médico
                    </option>
                </select>
                <?php if (in_array(($usuarioEdicao['perfil'] ?? ''), ['recepcao', 'recepção'])): ?>
                <p class="text-xs text-yellow-700 mt-1 bg-yellow-50 p-2 rounded">
                    <i class="fas fa-exclamation-triangle"></i> Este usuário tem perfil antigo "<?php echo $usuarioEdicao['perfil']; ?>". Altere para "Atendente" e salve.
                </p>
                <?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Senha <?php echo !$usuarioEdicao ? '<span class="text-red-500">*</span>' : '(deixe em branco para manter)'; ?>
                </label>
                <input type="password" 
                       name="senha" 
                       <?php echo !$usuarioEdicao ? 'required' : ''; ?>
                       minlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Mínimo de 6 caracteres</p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Confirmar Senha <?php echo !$usuarioEdicao ? '<span class="text-red-500">*</span>' : ''; ?>
                </label>
                <input type="password" 
                       name="confirmar_senha" 
                       <?php echo !$usuarioEdicao ? 'required' : ''; ?>
                       minlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <?php if ($usuarioEdicao): ?>
        <div>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" 
                       name="ativo" 
                       value="1"
                       <?php echo ($usuarioEdicao['ativo'] ?? true) ? 'checked' : ''; ?>
                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                <span class="text-sm font-semibold text-gray-700">Usuário Ativo</span>
            </label>
        </div>
        <?php endif; ?>
        
        <div class="flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $usuarioEdicao ? 'Atualizar' : 'Criar'; ?>
            </button>
            <?php if ($usuarioEdicao): ?>
            <a href="<?php echo BASE_PATH; ?>usuarios.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <?php endif; ?>
        </div>
    </form>
    
    <!-- DEBUG JAVASCRIPT -->
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const perfil = document.getElementById('select_perfil').value;
        const formData = new FormData(this);
        
        console.log('========== SUBMIT FORM ==========');
        console.log('Perfil do select:', perfil);
        console.log('Perfil no FormData:', formData.get('perfil'));
        console.log('Todos os dados:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ':', value);
        }
        console.log('=================================');
        
        // Verificar se o perfil está vazio
        if (!perfil || perfil === '') {
            alert('ERRO DEBUG: Campo perfil está vazio!\nValor: [' + perfil + ']');
            e.preventDefault();
            return false;
        }
    });
    </script>
</div>

<!-- Filtros e Busca -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" 
               name="busca" 
               placeholder="Buscar por nome ou e-mail..."
               value="<?php echo htmlspecialchars($busca); ?>"
               class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        
        <select name="perfil" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os perfis</option>
            <option value="administrador" <?php echo $filtroPerfil === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
            <option value="atendente" <?php echo $filtroPerfil === 'atendente' ? 'selected' : ''; ?>>Atendente</option>
            <option value="recepcao" <?php echo $filtroPerfil === 'recepcao' ? 'selected' : ''; ?>>⚠️ Recepção (Legado)</option>
            <option value="medico" <?php echo $filtroPerfil === 'medico' ? 'selected' : ''; ?>>Médico</option>
        </select>
        
        <select name="ativo" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Todos os status</option>
            <option value="1" <?php echo $filtroAtivo === 1 ? 'selected' : ''; ?>>Ativos</option>
            <option value="0" <?php echo $filtroAtivo === 0 ? 'selected' : ''; ?>>Inativos</option>
        </select>
        
        <div class="flex gap-2">
            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
            <?php if ($busca || $filtroPerfil || $filtroAtivo !== ''): ?>
            <a href="<?php echo BASE_PATH; ?>usuarios.php" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg transition">
                Limpar
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Contador e aviso de filtros -->
<?php if ($busca || $filtroPerfil || $filtroAtivo !== ''): ?>
<div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded mb-4 flex items-center justify-between">
    <div>
        <i class="fas fa-filter mr-2"></i>
        <strong>Filtros ativos:</strong> Mostrando <?php echo count($usuarios); ?> usuário(s)
        <?php if ($busca): ?> | Busca: "<?php echo htmlspecialchars($busca); ?>"<?php endif; ?>
        <?php if ($filtroPerfil): ?> | Perfil: <?php echo htmlspecialchars($filtroPerfil); ?><?php endif; ?>
        <?php if ($filtroAtivo !== ''): ?> | Status: <?php echo $filtroAtivo ? 'Ativo' : 'Inativo'; ?><?php endif; ?>
    </div>
    <a href="<?php echo BASE_PATH; ?>usuarios.php" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition text-sm">
        <i class="fas fa-times mr-1"></i>Limpar Filtros
    </a>
</div>
<?php else: ?>
<div class="bg-blue-50 border border-blue-300 text-blue-800 px-4 py-3 rounded mb-4">
    <i class="fas fa-info-circle mr-2"></i>
    Mostrando <strong><?php echo count($usuarios); ?> usuário(s)</strong> cadastrado(s) no sistema
</div>
<?php endif; ?>

<!-- Tabela -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">E-mail</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Perfil</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Criado em</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (count($usuarios) > 0): ?>
                <?php foreach ($usuarios as $usuario): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <?php echo htmlspecialchars($usuario['nome']); ?>
                        <?php if ($usuario['id'] == $_SESSION['usuario_id']): ?>
                        <span class="text-xs text-blue-600 font-semibold">(Você)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo htmlspecialchars($usuario['email']); ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $perfis = [
                            'administrador' => ['Administrador', 'bg-purple-200 text-purple-800'],
                            'atendente' => ['Atendente', 'bg-blue-200 text-blue-800'],
                            'recepcao' => ['Recepção (Atualizar)', 'bg-yellow-200 text-yellow-800'], // Legado
                            'recepção' => ['Recepção (Atualizar)', 'bg-yellow-200 text-yellow-800'], // Legado com cedilha
                            'medico' => ['Médico', 'bg-green-200 text-green-800']
                        ];
                        $perfil = $perfis[$usuario['perfil']] ?? ['Desconhecido (' . $usuario['perfil'] . ')', 'bg-gray-200 text-gray-800'];
                        ?>
                        <span class="chip <?php echo $perfil[1]; ?>">
                            <?php echo $perfil[0]; ?>
                        </span>
                        <!-- DEBUG: Remover depois -->
                        <!--<div class="text-xs text-gray-500 mt-1">
                            DB: "<?php echo htmlspecialchars($usuario['perfil'] ?? 'NULL'); ?>" 
                            (<?php echo strlen($usuario['perfil'] ?? ''); ?> chars)
                        </div>-->
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($usuario['ativo']): ?>
                        <span class="chip bg-green-200 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Ativo
                        </span>
                        <?php else: ?>
                        <span class="chip bg-gray-200 text-gray-800">
                            <i class="fas fa-ban mr-1"></i>Inativo
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-3">
                            <a href="?acao=editar&id=<?php echo $usuario['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                <?php if ($usuario['ativo']): ?>
                                <a href="?acao=inativar&id=<?php echo $usuario['id']; ?>" 
                                   onclick="return confirm('Deseja realmente inativar este usuário?')"
                                   class="text-orange-600 hover:text-orange-800">
                                    <i class="fas fa-ban"></i> Inativar
                                </a>
                                <?php endif; ?>
                                <a href="?acao=deletar&id=<?php echo $usuario['id']; ?>" 
                                   onclick="return confirm('ATENÇÃO: Esta ação é irreversível! Deseja realmente deletar permanentemente este usuário?')"
                                   class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i> Deletar
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        Nenhum usuário encontrado
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Validação de confirmação de senha em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const senha = document.querySelector('input[name="senha"]');
    const confirmarSenha = document.querySelector('input[name="confirmar_senha"]');
    
    if (senha && confirmarSenha) {
        confirmarSenha.addEventListener('input', function() {
            if (senha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não conferem');
            } else {
                confirmarSenha.setCustomValidity('');
            }
        });
        
        senha.addEventListener('input', function() {
            if (confirmarSenha.value && senha.value !== confirmarSenha.value) {
                confirmarSenha.setCustomValidity('As senhas não conferem');
            } else {
                confirmarSenha.setCustomValidity('');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
