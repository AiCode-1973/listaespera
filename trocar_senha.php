<?php
/**
 * Página de Alteração de Senha
 * Permite que usuários autenticados troquem sua senha
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Usuario.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$usuarioLogado = $auth->getUsuarioLogado();
$erros = [];
$sucesso = false;

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $senhaNova = $_POST['senha_nova'] ?? '';
    $senhaConfirmacao = $_POST['senha_confirmacao'] ?? '';
    
    // Validações
    if (empty($senhaAtual)) {
        $erros[] = 'Senha atual é obrigatória';
    }
    
    if (empty($senhaNova)) {
        $erros[] = 'Nova senha é obrigatória';
    }
    
    if (strlen($senhaNova) < 6) {
        $erros[] = 'Nova senha deve ter no mínimo 6 caracteres';
    }
    
    if (empty($senhaConfirmacao)) {
        $erros[] = 'Confirmação de senha é obrigatória';
    }
    
    if ($senhaNova !== $senhaConfirmacao) {
        $erros[] = 'Nova senha e confirmação não conferem';
    }
    
    if ($senhaAtual === $senhaNova) {
        $erros[] = 'Nova senha deve ser diferente da senha atual';
    }
    
    // Se não houver erros, tenta alterar a senha
    if (empty($erros)) {
        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->alterarSenha($usuarioLogado['id'], $senhaAtual, $senhaNova);
        
        if ($resultado['sucesso']) {
            $_SESSION['mensagem_sucesso'] = $resultado['mensagem'];
            $sucesso = true;
            
            // Limpa os campos
            $_POST = [];
        } else {
            $erros[] = $resultado['mensagem'];
        }
    }
}

$pageTitle = 'Alterar Senha';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Cabeçalho -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-key text-blue-600 mr-3"></i>Alterar Senha
                    </h1>
                    <p class="text-gray-600 mt-2">Altere sua senha de acesso ao sistema</p>
                </div>
            </div>
        </div>

        <!-- Mensagens -->
        <?php if (!empty($erros)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
            <div class="flex items-start">
                <i class="fas fa-exclamation-circle text-red-500 mt-1 mr-3"></i>
                <div>
                    <p class="font-semibold text-red-800">Erro ao alterar senha:</p>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        <?php foreach ($erros as $erro): ?>
                        <li><?php echo htmlspecialchars($erro); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                <div>
                    <p class="font-semibold text-green-800">Senha alterada com sucesso!</p>
                    <p class="mt-1 text-sm text-green-700">Você pode fechar esta página ou alterar novamente se desejar.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Informações do Usuário -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div>
                    <p class="font-semibold text-blue-800">Informações da Conta</p>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($usuarioLogado['nome']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($usuarioLogado['email']); ?></p>
                        <p><strong>Perfil:</strong> <?php echo ucfirst(htmlspecialchars($usuarioLogado['perfil'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form method="POST" action="" id="formTrocarSenha">
                <div class="space-y-6">
                    <!-- Senha Atual -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1 text-gray-600"></i>Senha Atual <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="senha_atual" 
                               id="senha_atual"
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Digite sua senha atual">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Digite a senha que você usa atualmente para entrar no sistema
                        </p>
                    </div>

                    <hr class="border-gray-200">

                    <!-- Nova Senha -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-key mr-1 text-blue-600"></i>Nova Senha <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="senha_nova" 
                               id="senha_nova"
                               required
                               minlength="6"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Digite a nova senha (mínimo 6 caracteres)">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-shield-alt mr-1"></i>Mínimo de 6 caracteres
                        </p>
                    </div>

                    <!-- Confirmar Nova Senha -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-check-circle mr-1 text-green-600"></i>Confirmar Nova Senha <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="senha_confirmacao" 
                               id="senha_confirmacao"
                               required
                               minlength="6"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Digite novamente a nova senha">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>Repita a nova senha para confirmar
                        </p>
                    </div>

                    <!-- Dicas de Segurança -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="font-semibold text-yellow-800 mb-2">
                            <i class="fas fa-lightbulb mr-2"></i>Dicas para uma senha segura:
                        </p>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <li><i class="fas fa-check text-green-600 mr-2"></i>Use no mínimo 6 caracteres (recomendado 8 ou mais)</li>
                            <li><i class="fas fa-check text-green-600 mr-2"></i>Misture letras maiúsculas e minúsculas</li>
                            <li><i class="fas fa-check text-green-600 mr-2"></i>Inclua números e caracteres especiais</li>
                            <li><i class="fas fa-check text-green-600 mr-2"></i>Não use senhas óbvias como "123456" ou "senha"</li>
                        </ul>
                    </div>

                    <!-- Botões -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition font-semibold">
                            <i class="fas fa-save mr-2"></i>Alterar Senha
                        </button>
                        <a href="<?php echo BASE_PATH; ?>dashboard.php" 
                           class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition font-semibold text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validação adicional no frontend
document.getElementById('formTrocarSenha').addEventListener('submit', function(e) {
    const senhaNova = document.getElementById('senha_nova').value;
    const senhaConfirmacao = document.getElementById('senha_confirmacao').value;
    const senhaAtual = document.getElementById('senha_atual').value;
    
    if (senhaNova !== senhaConfirmacao) {
        e.preventDefault();
        alert('⚠️ Nova senha e confirmação não conferem!');
        return false;
    }
    
    if (senhaNova === senhaAtual) {
        e.preventDefault();
        alert('⚠️ Nova senha deve ser diferente da senha atual!');
        return false;
    }
    
    if (senhaNova.length < 6) {
        e.preventDefault();
        alert('⚠️ Nova senha deve ter no mínimo 6 caracteres!');
        return false;
    }
    
    return true;
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
