<?php
require_once __DIR__ . '/../config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$usuario = [
    'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
    'perfil' => $_SESSION['usuario_perfil'] ?? ''
];

$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'ListaSaúde'; ?> - Hospital</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_PATH; ?>images/favicon.png">
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_PATH; ?>images/favicon.png">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Estilos customizados */
        .chip {
            @apply px-3 py-1 rounded-full text-xs font-semibold inline-block;
        }
        
        .table-hover tbody tr:hover {
            @apply bg-gray-100 transition-colors duration-150;
        }
        
        /* Animação suave para modais */
        .modal-backdrop {
            @apply fixed inset-0 bg-black bg-opacity-50 z-40;
        }
        
        .modal-content {
            @apply fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-xl z-50 max-h-[90vh] overflow-y-auto;
        }
        
        /* Ícones de ação nas tabelas */
        td a i.fa-edit,
        td a i.fa-ban,
        td a i.fa-trash-alt,
        td a i.fa-eye,
        td a i.fa-whatsapp {
            font-size: 1.1rem;
        }
        
        td a {
            transition: all 0.2s ease;
        }
        
        td a:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-blue-900 text-white shadow-lg">
        <div class="max-w-[98%] mx-auto px-2">
            <div class="flex items-center justify-between h-16">
                <!-- Logo e título -->
                <div class="flex items-center space-x-4">
                    <i class="fas fa-hospital text-2xl"></i>
                    <span class="text-xl font-bold">ListaSaúde</span>
                </div>

                <!-- Menu de navegação -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?php echo BASE_PATH; ?>dashboard.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'dashboard' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-list-ul mr-2"></i>Dashboard
                    </a>
                    
                    <?php if (in_array($usuario['perfil'], ['administrador', 'recepcao', 'atendente'])): ?>
                    <a href="<?php echo BASE_PATH; ?>agenda.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'agenda' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-calendar-alt mr-2"></i>Agenda
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($usuario['perfil'] === 'administrador'): ?>
                    
                    <a href="<?php echo BASE_PATH; ?>historico_mensagens.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'historico_mensagens' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fab fa-whatsapp mr-2"></i>Mensagens
                    </a>
                    
                    <a href="<?php echo BASE_PATH; ?>dashboard_graficos.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'dashboard-graficos' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-chart-line mr-2"></i>Gráficos
                    </a>
                    
                    <a href="<?php echo BASE_PATH; ?>medicos.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'medicos' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-user-md mr-2"></i>Médicos
                    </a>
                    
                    <a href="<?php echo BASE_PATH; ?>especialidades.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'especialidades' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-stethoscope mr-2"></i>Especialidades
                    </a>
                    
                    <a href="<?php echo BASE_PATH; ?>convenios.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'convenios' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-file-contract mr-2"></i>Convênios
                    </a>
                    
                    <a href="<?php echo BASE_PATH; ?>usuarios.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'usuarios' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-users mr-2"></i>Usuários
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array($usuario['perfil'], ['administrador', 'atendente'])): ?>
                    <a href="<?php echo BASE_PATH; ?>pacientes.php" 
                       class="hover:text-blue-200 transition <?php echo $paginaAtual == 'pacientes' || $paginaAtual == 'paciente_historico' ? 'font-bold border-b-2 border-white' : ''; ?>">
                        <i class="fas fa-user-injured mr-2"></i>Pacientes
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Menu de Perfil do Usuário -->
                <div class="relative" id="menuPerfilContainer">
                    <!-- Botão do Perfil -->
                    <button onclick="toggleMenuPerfil()" 
                            class="flex items-center space-x-3 bg-blue-700 hover:bg-blue-800 px-4 py-2 rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <div class="text-right hidden md:block">
                            <p class="font-semibold text-sm"><?php echo htmlspecialchars($usuario['nome']); ?></p>
                            <p class="text-xs text-blue-200 capitalize"><?php echo htmlspecialchars($usuario['perfil']); ?></p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-10 h-10 bg-blue-900 rounded-full flex items-center justify-center text-white font-bold">
                                <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
                            </div>
                            <i class="fas fa-chevron-down text-sm transition-transform" id="menuPerfilIcon"></i>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="menuPerfilDropdown" 
                         class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50">
                        <!-- Cabeçalho do Menu -->
                        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 text-white">
                            <p class="font-semibold"><?php echo htmlspecialchars($usuario['nome']); ?></p>
                            <p class="text-xs text-blue-100 capitalize"><?php echo htmlspecialchars($usuario['perfil']); ?></p>
                        </div>

                        <!-- Opções do Menu -->
                        <div class="py-2">
                            <!-- Trocar Senha -->
                            <a href="<?php echo BASE_PATH; ?>trocar_senha.php" 
                               class="flex items-center px-4 py-3 hover:bg-gray-100 transition group">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 group-hover:bg-blue-200 transition">
                                    <i class="fas fa-key text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Alterar Senha</p>
                                    <p class="text-xs text-gray-500">Trocar sua senha de acesso</p>
                                </div>
                            </a>

                            <div class="border-t border-gray-200 my-1"></div>

                            <!-- Sair -->
                            <a href="<?php echo BASE_PATH; ?>logout.php" 
                               onclick="return confirm('Deseja realmente sair?');"
                               class="flex items-center px-4 py-3 hover:bg-red-50 transition group">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3 group-hover:bg-red-200 transition">
                                    <i class="fas fa-sign-out-alt text-red-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Sair do Sistema</p>
                                    <p class="text-xs text-gray-500">Encerrar sua sessão</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Container principal -->
    <main class="max-w-[98%] mx-auto px-2 py-8">
