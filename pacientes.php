<?php
/**
 * Pacientes - Busca e Listagem
 * Apenas para Administradores
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$usuarioLogado = $auth->getUsuarioLogado();

// Verifica se é administrador ou atendente
if (!in_array($usuarioLogado['perfil'], ['administrador', 'atendente'])) {
    $_SESSION['mensagem_erro'] = 'Acesso negado. Apenas administradores e atendentes podem acessar o histórico de pacientes.';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit;
}

// Busca de pacientes
$busca = $_GET['busca'] ?? '';
$pacientes = [];

if (!empty($busca)) {
    // Busca pacientes únicos na fila de espera (um registro por CPF)
    $sql = "SELECT 
                MIN(nome_paciente) as nome_paciente,
                cpf,
                MIN(telefone1) as telefone1,
                COUNT(*) as total_registros,
                SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as total_agendados,
                SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as total_pendentes,
                MAX(data_solicitacao) as ultima_solicitacao
            FROM fila_espera
            WHERE (nome_paciente LIKE :busca_nome OR cpf LIKE :busca_cpf)
            GROUP BY cpf
            ORDER BY ultima_solicitacao DESC
            LIMIT 50";
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        $stmt = $conn->prepare($sql);
        $buscaParam = '%' . $busca . '%';
        $stmt->bindParam(':busca_nome', $buscaParam);
        $stmt->bindParam(':busca_cpf', $buscaParam);
        $stmt->execute();
        $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $erro = "Erro ao buscar pacientes: " . $e->getMessage();
    }
}

$pageTitle = 'Histórico de Pacientes';
include __DIR__ . '/includes/header.php';
?>

<!-- Cabeçalho da Página -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-injured text-blue-600 mr-3"></i>
                Histórico de Pacientes
            </h1>
            <p class="text-gray-600 mt-2">Busque pacientes e visualize todo o histórico de atendimentos</p>
        </div>
        <div class="text-right">
            <a href="<?php echo BASE_PATH; ?>dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<?php if (isset($erro)): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
    <span class="block sm:inline"><?php echo htmlspecialchars($erro); ?></span>
</div>
<?php endif; ?>

<!-- Formulário de Busca -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form method="GET" action="" class="space-y-4">
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-search mr-1"></i>Buscar Paciente
                </label>
                <input type="text" 
                       name="busca" 
                       value="<?php echo htmlspecialchars($busca); ?>"
                       placeholder="Digite o nome ou CPF do paciente..."
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       autofocus>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition flex items-center">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
            </div>
        </div>
        <p class="text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i>
            Digite pelo menos parte do nome ou CPF do paciente para iniciar a busca
        </p>
    </form>
</div>

<?php if (!empty($busca)): ?>
<!-- Resultados da Busca -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gray-100 px-6 py-3 border-b">
        <h2 class="text-lg font-semibold text-gray-800">
            <i class="fas fa-list mr-2"></i>Resultados da Busca
            <?php if (count($pacientes) > 0): ?>
                <span class="text-blue-600">(<?php echo count($pacientes); ?> paciente<?php echo count($pacientes) > 1 ? 's' : ''; ?> encontrado<?php echo count($pacientes) > 1 ? 's' : ''; ?>)</span>
            <?php endif; ?>
        </h2>
    </div>
    
    <?php if (count($pacientes) > 0): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Nome do Paciente
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        CPF
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Telefone
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Total de Registros
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Agendados
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Pendentes
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Última Solicitação
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($pacientes as $paciente): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($paciente['nome_paciente']); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <?php echo formatarCPF($paciente['cpf']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <?php echo formatarTelefone($paciente['telefone1']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            <i class="fas fa-file-medical mr-1"></i>
                            <?php echo $paciente['total_registros']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>
                            <?php echo $paciente['total_agendados']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-1"></i>
                            <?php echo $paciente['total_pendentes']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <?php echo formatarData($paciente['ultima_solicitacao']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="<?php echo BASE_PATH; ?>paciente_historico.php?cpf=<?php echo urlencode($paciente['cpf']); ?>" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition inline-flex items-center">
                            <i class="fas fa-history mr-2"></i>Ver Histórico
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="px-6 py-12 text-center text-gray-500">
        <i class="fas fa-search text-6xl mb-4 text-gray-300"></i>
        <p class="text-lg font-semibold">Nenhum paciente encontrado</p>
        <p class="text-sm mt-2">Tente buscar por outro nome ou CPF</p>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (empty($busca)): ?>
<!-- Instruções de Uso -->
<div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-600 text-2xl"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Como usar este módulo</h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Digite o nome ou CPF do paciente no campo de busca acima</li>
                <li>Clique em "Buscar" para ver a lista de pacientes encontrados</li>
                <li>Clique em "Ver Histórico" para visualizar todos os atendimentos do paciente</li>
                <li>O histórico mostrará todos os registros, agendados e não agendados</li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
