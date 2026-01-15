<?php
/**
 * Página de Login
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController();

// Se já está logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit();
}

$erro = '';
$sucesso = '';

// Processa o login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    $resultado = $auth->login($email, $senha);
    
    if ($resultado['sucesso']) {
        header('Location: ' . $resultado['redirect']);
        exit();
    } else {
        $erro = $resultado['mensagem'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Lista de Espera</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
        <!-- Logo e título -->
        <div class="text-center mb-8">
            <img src="<?php echo BASE_PATH; ?>images/logo_listasaude.png" 
                 alt="ListaSaúde Logo" 
                 class="mx-auto mb-4 h-72 w-auto">
            <!--<h1 class="text-3xl font-bold text-gray-800 mb-2">ListaSaúde</h1>-->
            <!--<p class="text-gray-600">Hospital - Gestão de Consultas e Exames</p>-->
        </div>

        <!-- Mensagens de erro -->
        <?php if ($erro): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($erro); ?></span>
        </div>
        <?php endif; ?>

        <!-- Mensagens de sucesso -->
        <?php if ($sucesso): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($sucesso); ?></span>
        </div>
        <?php endif; ?>

        <!-- Formulário de login -->
        <form method="POST" action="">
            <div class="mb-6">
                <label for="email" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-envelope mr-2"></i>E-mail
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="seu@email.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                >
            </div>

            <div class="mb-6">
                <label for="senha" class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2"></i>Senha
                </label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="••••••••"
                >
            </div>

            <button 
                type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200 flex items-center justify-center"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>Entrar
            </button>
        </form>

        <!-- Informações de acesso para teste -->
        <!--<div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-800 font-semibold mb-2">Informações:</p>
            <div class="text-xs text-blue-700 space-y-1">
                <p><strong>Desenvolvido por:</strong> Demetrius Figueiredo</p>
                <p><strong>Telefone:</strong> (13) 97410-6240</p>
                <p><strong>Empresa:</strong> <a href="https://aicode.dev.br" target="_blank" class="text-blue-600 hover:text-blue-800 underline">AiCode</a></p>
            </div>
        </div>
    </div>-->

    <!-- Footer -->
    <div class="fixed bottom-4 left-0 right-0 text-center text-white text-sm">
        &copy; <?php echo date('Y'); ?> Sistema de Lista de Espera - Hospital
    </div>
</body>
</html>
