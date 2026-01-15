<?php
/**
 * Gerenciamento de Especialidades
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Especialidade.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$especialidadeModel = new Especialidade();
$erros = [];

// Processar ações
$acao = $_GET['acao'] ?? '';

// Deletar
if ($acao === 'deletar' && isset($_GET['id'])) {
    // Apenas admin pode deletar
    $auth->verificarPermissao(['administrador']);
    
    if ($especialidadeModel->deletar($_GET['id'])) {
        $_SESSION['mensagem_sucesso'] = 'Especialidade excluída com sucesso';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao excluir especialidade. Pode haver registros vinculados.';
    }
    header('Location: ' . BASE_PATH . 'especialidades.php');
    exit();
}

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if (empty($_POST['nome'])) {
        $erros[] = 'Nome é obrigatório';
    }
    
    if (empty($_POST['cor'])) {
        $erros[] = 'Cor é obrigatória';
    }
    
    // Verifica duplicidade
    if ($especialidadeModel->nomeExiste($_POST['nome'], $id)) {
        $erros[] = 'Já existe uma especialidade com este nome';
    }
    
    if (empty($erros)) {
        $dados = [
            'nome' => $_POST['nome'],
            'cor' => $_POST['cor']
        ];
        
        if ($id) {
            if ($especialidadeModel->atualizar($id, $dados)) {
                $_SESSION['mensagem_sucesso'] = 'Especialidade atualizada com sucesso';
                header('Location: ' . BASE_PATH . 'especialidades.php');
                exit();
            } else {
                $erros[] = 'Erro ao atualizar especialidade';
            }
        } else {
            if ($especialidadeModel->criar($dados)) {
                $_SESSION['mensagem_sucesso'] = 'Especialidade criada com sucesso';
                header('Location: ' . BASE_PATH . 'especialidades.php');
                exit();
            } else {
                $erros[] = 'Erro ao criar especialidade';
            }
        }
    }
}

// Buscar especialidade para edição
$especialidadeEdicao = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $especialidadeEdicao = $especialidadeModel->buscarPorId($_GET['id']);
    if (!$especialidadeEdicao) {
        $_SESSION['mensagem_erro'] = 'Especialidade não encontrada';
        header('Location: ' . BASE_PATH . 'especialidades.php');
        exit();
    }
}

// Listar especialidades com paginação
$busca = $_GET['busca'] ?? '';

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registrosPorPagina = 15;
$offset = ($pagina - 1) * $registrosPorPagina;

// Busca dados
$especialidades = $especialidadeModel->listar($busca, $registrosPorPagina, $offset);
$totalRegistros = $especialidadeModel->contar($busca);
$paginacao = paginar($totalRegistros, $registrosPorPagina, $pagina);

$pageTitle = 'Especialidades';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
        <i class="fas fa-stethoscope mr-3"></i>Especialidades Médicas
    </h1>
    <p class="text-gray-600">Gerencie as especialidades disponíveis no sistema</p>
</div>

<!-- Mensagens -->
<?php if (!empty($erros)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
    <ul class="list-disc list-inside">
        <?php foreach ($erros as $erro): ?>
        <li><?php echo htmlspecialchars($erro); ?></li>
        <?php endforeach; ?>
    </ul>
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
<div class="bg-white rounded-lg shadow-md p-6 mb-6 <?php echo $especialidadeEdicao ? 'border-2 border-blue-500' : ''; ?>">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">
            <?php if ($especialidadeEdicao): ?>
                <i class="fas fa-edit text-blue-600 mr-2"></i>Editar Especialidade
            <?php else: ?>
                <i class="fas fa-plus-circle text-green-600 mr-2"></i>Nova Especialidade
            <?php endif; ?>
        </h2>
        <?php if ($especialidadeEdicao): ?>
        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
            <i class="fas fa-info-circle mr-1"></i>Modo Edição
        </span>
        <?php endif; ?>
    </div>
    
    <?php if ($especialidadeEdicao): ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4">
        <p class="text-sm text-blue-800">
            <i class="fas fa-lightbulb mr-2"></i>
            Você está editando: <strong><?php echo htmlspecialchars($especialidadeEdicao['nome']); ?></strong>
        </p>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-4">
        <?php if ($especialidadeEdicao): ?>
        <input type="hidden" name="id" value="<?php echo $especialidadeEdicao['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome da Especialidade <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       required
                       value="<?php echo htmlspecialchars($especialidadeEdicao['nome'] ?? $_POST['nome'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Cor (Classe Tailwind) <span class="text-red-500">*</span>
                </label>
                <select name="cor" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php
                    $cores = [
                        'bg-blue-200' => 'Azul',
                        'bg-purple-200' => 'Roxo',
                        'bg-red-200' => 'Vermelho',
                        'bg-pink-200' => 'Rosa',
                        'bg-yellow-200' => 'Amarelo',
                        'bg-green-200' => 'Verde',
                        'bg-indigo-200' => 'Índigo',
                        'bg-teal-200' => 'Teal',
                        'bg-orange-200' => 'Laranja',
                        'bg-cyan-200' => 'Ciano',
                        'bg-lime-200' => 'Lima',
                        'bg-emerald-200' => 'Esmeralda'
                    ];
                    $corSelecionada = $especialidadeEdicao['cor'] ?? $_POST['cor'] ?? '';
                    foreach ($cores as $classe => $nome):
                    ?>
                    <option value="<?php echo $classe; ?>" <?php echo $corSelecionada == $classe ? 'selected' : ''; ?>>
                        <?php echo $nome; ?> (<?php echo $classe; ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $especialidadeEdicao ? 'Atualizar' : 'Criar'; ?>
            </button>
            <?php if ($especialidadeEdicao): ?>
            <a href="<?php echo BASE_PATH; ?>especialidades.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Busca -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="" class="flex gap-3">
        <input type="text" 
               name="busca" 
               placeholder="Buscar especialidade..."
               value="<?php echo htmlspecialchars($busca); ?>"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-search mr-2"></i>Buscar
        </button>
        <?php if ($busca): ?>
        <a href="<?php echo BASE_PATH; ?>especialidades.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-times mr-2"></i>Limpar
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabela -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <!-- Contador de Registros -->
    <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-600">
                <i class="fas fa-stethoscope mr-2"></i>
                <span class="font-semibold"><?php echo $totalRegistros; ?></span> 
                especialidade<?php echo $totalRegistros != 1 ? 's' : ''; ?> encontrada<?php echo $totalRegistros != 1 ? 's' : ''; ?>
            </p>
            <?php if ($busca): ?>
            <p class="text-sm text-gray-500">
                Filtro ativo: <span class="font-semibold">"<?php echo htmlspecialchars($busca); ?>"</span>
            </p>
            <?php endif; ?>
        </div>
    </div>
    
    <table class="min-w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cor (Preview)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (count($especialidades) > 0): ?>
                <?php foreach ($especialidades as $esp): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <?php echo htmlspecialchars($esp['nome']); ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="chip <?php echo gerarClasseChip($esp['cor']); ?>">
                            <?php echo htmlspecialchars($esp['nome']); ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-3">
                            <a href="?acao=editar&id=<?php echo $esp['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800"
                               title="Editar especialidade">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($auth->isAdmin()): ?>
                            <a href="?acao=deletar&id=<?php echo $esp['id']; ?>" 
                               onclick="return confirm('⚠️ ATENÇÃO: Deseja realmente EXCLUIR esta especialidade?\n\nEsta ação não pode ser desfeita!\n\nSe houver médicos vinculados, a exclusão será bloqueada.')"
                               class="text-red-600 hover:text-red-800"
                               title="Excluir especialidade permanentemente">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-stethoscope text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg font-semibold">Nenhuma especialidade encontrada</p>
                            <?php if ($busca): ?>
                            <p class="text-gray-400 text-sm mt-2">
                                Tente alterar os critérios de busca
                            </p>
                            <a href="<?php echo BASE_PATH; ?>especialidades.php" 
                               class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-redo mr-2"></i>Ver todas
                            </a>
                            <?php else: ?>
                            <p class="text-gray-400 text-sm mt-2">
                                Cadastre a primeira especialidade usando o formulário acima
                            </p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if ($paginacao['total_paginas'] > 1): ?>
<div class="bg-white rounded-lg shadow-md p-4 mt-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Informações -->
        <div class="text-sm text-gray-600">
            Mostrando 
            <span class="font-semibold"><?php echo min($offset + 1, $totalRegistros); ?></span>
            até 
            <span class="font-semibold"><?php echo min($offset + $registrosPorPagina, $totalRegistros); ?></span>
            de 
            <span class="font-semibold"><?php echo $totalRegistros; ?></span>
            especialidade<?php echo $totalRegistros != 1 ? 's' : ''; ?>
        </div>
        
        <!-- Botões de paginação -->
        <div class="flex items-center space-x-2">
            <?php
            $queryParams = $_GET;
            
            // Botão Primeira página
            if ($pagina > 1):
                $queryParams['pagina'] = 1;
                $urlPrimeira = '?' . http_build_query($queryParams);
            ?>
                <a href="<?php echo $urlPrimeira; ?>" 
                   class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition">
                    <i class="fas fa-angle-double-left"></i>
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded cursor-not-allowed">
                    <i class="fas fa-angle-double-left"></i>
                </span>
            <?php endif; ?>
            
            <!-- Botão Anterior -->
            <?php if ($pagina > 1):
                $queryParams['pagina'] = $pagina - 1;
                $urlAnterior = '?' . http_build_query($queryParams);
            ?>
                <a href="<?php echo $urlAnterior; ?>" 
                   class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition">
                    <i class="fas fa-angle-left"></i> Anterior
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded cursor-not-allowed">
                    <i class="fas fa-angle-left"></i> Anterior
                </span>
            <?php endif; ?>
            
            <!-- Números de página -->
            <?php
            $inicio = max(1, $pagina - 2);
            $fim = min($paginacao['total_paginas'], $pagina + 2);
            
            for ($i = $inicio; $i <= $fim; $i++):
                $queryParams['pagina'] = $i;
                $urlPagina = '?' . http_build_query($queryParams);
                
                if ($i == $pagina):
            ?>
                <span class="px-4 py-2 bg-blue-600 text-white rounded font-semibold">
                    <?php echo $i; ?>
                </span>
            <?php else: ?>
                <a href="<?php echo $urlPagina; ?>" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition">
                    <?php echo $i; ?>
                </a>
            <?php 
                endif;
            endfor; 
            ?>
            
            <!-- Botão Próxima -->
            <?php if ($pagina < $paginacao['total_paginas']):
                $queryParams['pagina'] = $pagina + 1;
                $urlProxima = '?' . http_build_query($queryParams);
            ?>
                <a href="<?php echo $urlProxima; ?>" 
                   class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition">
                    Próxima <i class="fas fa-angle-right"></i>
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded cursor-not-allowed">
                    Próxima <i class="fas fa-angle-right"></i>
                </span>
            <?php endif; ?>
            
            <!-- Botão Última página -->
            <?php if ($pagina < $paginacao['total_paginas']):
                $queryParams['pagina'] = $paginacao['total_paginas'];
                $urlUltima = '?' . http_build_query($queryParams);
            ?>
                <a href="<?php echo $urlUltima; ?>" 
                   class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded transition">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 text-gray-400 rounded cursor-not-allowed">
                    <i class="fas fa-angle-double-right"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
