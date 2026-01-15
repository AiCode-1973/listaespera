<?php
/**
 * Gerenciamento de Convênios
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Convenio.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$convenioModel = new Convenio();
$erros = [];
$sucesso = '';

// Processar ações
$acao = $_GET['acao'] ?? '';

// Deletar
if ($acao === 'deletar' && isset($_GET['id'])) {
    $auth->verificarPermissao(['administrador']);
    
    if ($convenioModel->deletar($_GET['id'])) {
        $_SESSION['mensagem_sucesso'] = 'Convênio excluído com sucesso';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao excluir convênio. Pode haver registros vinculados.';
    }
    header('Location: ' . BASE_PATH . 'convenios.php');
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
    
    if ($convenioModel->nomeExiste($_POST['nome'], $id)) {
        $erros[] = 'Já existe um convênio com este nome';
    }
    
    if (empty($erros)) {
        $dados = [
            'nome' => $_POST['nome'],
            'codigo' => $_POST['codigo'] ?? '',
            'cor' => $_POST['cor']
        ];
        
        if ($id) {
            if ($convenioModel->atualizar($id, $dados)) {
                $sucesso = 'Convênio atualizado com sucesso';
            } else {
                $erros[] = 'Erro ao atualizar convênio';
            }
        } else {
            if ($convenioModel->criar($dados)) {
                $sucesso = 'Convênio criado com sucesso';
            } else {
                $erros[] = 'Erro ao criar convênio';
            }
        }
    }
}

// Buscar convênio para edição
$convenioEdicao = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $convenioEdicao = $convenioModel->buscarPorId($_GET['id']);
}

// Listar convênios
$busca = $_GET['busca'] ?? '';
$convenios = $convenioModel->listar($busca);

$pageTitle = 'Convênios';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
        <i class="fas fa-file-contract mr-3"></i>Convênios
    </h1>
    <p class="text-gray-600">Gerencie os convênios médicos disponíveis</p>
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

<?php if ($sucesso): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
    <?php echo htmlspecialchars($sucesso); ?>
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
        <?php echo $convenioEdicao ? 'Editar' : 'Novo'; ?> Convênio
    </h2>
    
    <form method="POST" action="" class="space-y-4">
        <?php if ($convenioEdicao): ?>
        <input type="hidden" name="id" value="<?php echo $convenioEdicao['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome do Convênio <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       required
                       value="<?php echo htmlspecialchars($convenioEdicao['nome'] ?? $_POST['nome'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Código Interno
                </label>
                <input type="text" 
                       name="codigo"
                       value="<?php echo htmlspecialchars($convenioEdicao['codigo'] ?? $_POST['codigo'] ?? ''); ?>"
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
                        'bg-emerald-200' => 'Verde Esmeralda',
                        'bg-sky-200' => 'Azul Céu',
                        'bg-violet-200' => 'Violeta',
                        'bg-cyan-200' => 'Ciano',
                        'bg-teal-200' => 'Teal',
                        'bg-lime-200' => 'Lima',
                        'bg-amber-200' => 'Âmbar',
                        'bg-rose-200' => 'Rosa',
                        'bg-fuchsia-200' => 'Fúcsia',
                        'bg-gray-200' => 'Cinza'
                    ];
                    $corSelecionada = $convenioEdicao['cor'] ?? $_POST['cor'] ?? '';
                    foreach ($cores as $classe => $nome):
                    ?>
                    <option value="<?php echo $classe; ?>" <?php echo $corSelecionada == $classe ? 'selected' : ''; ?>>
                        <?php echo $nome; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $convenioEdicao ? 'Atualizar' : 'Criar'; ?>
            </button>
            <?php if ($convenioEdicao): ?>
            <a href="<?php echo BASE_PATH; ?>convenios.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
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
               placeholder="Buscar convênio..."
               value="<?php echo htmlspecialchars($busca); ?>"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-search"></i>
        </button>
        <?php if ($busca): ?>
        <a href="<?php echo BASE_PATH; ?>convenios.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
            Limpar
        </a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabela -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <table class="min-w-full">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Código</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cor (Preview)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (count($convenios) > 0): ?>
                <?php foreach ($convenios as $conv): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <?php echo htmlspecialchars($conv['nome']); ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo htmlspecialchars($conv['codigo'] ?: '-'); ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="chip <?php echo gerarClasseChip($conv['cor']); ?>">
                            <?php echo htmlspecialchars($conv['nome']); ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-3">
                            <a href="?acao=editar&id=<?php echo $conv['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <?php if ($auth->isAdmin()): ?>
                            <a href="?acao=deletar&id=<?php echo $conv['id']; ?>" 
                               onclick="return confirmarExclusao('Deseja realmente excluir este convênio?')"
                               class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i> Excluir
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                        Nenhum convênio encontrado
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
