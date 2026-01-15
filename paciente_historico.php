<?php
/**
 * Histórico Completo do Paciente
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
    $_SESSION['mensagem_erro'] = 'Acesso negado.';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit;
}

$cpf = $_GET['cpf'] ?? '';

if (empty($cpf)) {
    $_SESSION['mensagem_erro'] = 'CPF não informado.';
    header('Location: ' . BASE_PATH . 'pacientes.php');
    exit;
}

// Limpa o CPF (remove formatação)
$cpfOriginal = $cpf;
$cpf = limparCPF($cpf);

// Debug - remover depois
error_log("CPF Original: " . $cpfOriginal);
error_log("CPF Limpo: " . $cpf);

// Busca todos os registros do paciente
$sql = "SELECT f.*, 
            m.nome as medico_nome,
            e.nome as especialidade_nome,
            c.nome as convenio_nome,
            ua.nome as usuario_agendamento_nome
        FROM fila_espera f
        LEFT JOIN medicos m ON f.medico_id = m.id
        LEFT JOIN especialidades e ON f.especialidade_id = e.id
        LEFT JOIN convenios c ON f.convenio_id = c.id
        LEFT JOIN usuarios ua ON f.usuario_agendamento_id = ua.id
        WHERE f.cpf = :cpf
        ORDER BY f.data_solicitacao DESC, f.id DESC";

try {
    $database = new Database();
    $conn = $database->getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cpf', $cpf);
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug - remover depois
    error_log("Total de registros encontrados: " . count($registros));
    
} catch (PDOException $e) {
    error_log("Erro SQL: " . $e->getMessage());
    $erro = "Erro ao buscar histórico: " . $e->getMessage();
    $registros = [];
}

// Se não encontrou registros, mostra erro
if (empty($registros)) {
    $pageTitle = 'Erro - Histórico de Paciente';
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-6 rounded-lg mb-6">
        <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-triangle text-3xl mr-4"></i>
            <div>
                <h2 class="text-xl font-bold">Nenhum registro encontrado</h2>
                <p class="mt-2">CPF buscado: <strong><?php echo formatarCPF($cpf); ?></strong></p>
            </div>
        </div>
        
        <div class="mt-4 bg-white p-4 rounded">
            <h3 class="font-semibold mb-2">Informações de Debug:</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
                <li>CPF original da URL: <code class="bg-gray-200 px-2 py-1 rounded"><?php echo htmlspecialchars($cpfOriginal); ?></code></li>
                <li>CPF limpo (sem formatação): <code class="bg-gray-200 px-2 py-1 rounded"><?php echo htmlspecialchars($cpf); ?></code></li>
                <li>Query executada com sucesso: <?php echo isset($erro) ? 'Não - ' . htmlspecialchars($erro) : 'Sim'; ?></li>
            </ul>
        </div>
        
        <div class="mt-4">
            <a href="<?php echo BASE_PATH; ?>pacientes.php?busca=<?php echo urlencode($cpfOriginal); ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Voltar e tentar novamente
            </a>
            
            <a href="<?php echo BASE_PATH; ?>test_cpf.php" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded inline-flex items-center ml-2">
                <i class="fas fa-list mr-2"></i>Ver lista de CPFs
            </a>
        </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Dados do paciente (pega do primeiro registro)
$paciente = $registros[0];

// Estatísticas
$total_registros = count($registros);
$total_agendados = count(array_filter($registros, fn($r) => $r['agendado'] == 1));
$total_pendentes = $total_registros - $total_agendados;
$total_urgentes = count(array_filter($registros, fn($r) => $r['urgente'] == 1));

$pageTitle = 'Histórico - ' . $paciente['nome_paciente'];
include __DIR__ . '/includes/header.php';
?>

<!-- Cabeçalho da Página -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <div class="flex items-center mb-3">
                <div class="flex-shrink-0 h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <?php echo htmlspecialchars($paciente['nome_paciente']); ?>
                    </h1>
                    <p class="text-gray-600 mt-1">
                        <i class="fas fa-id-badge mr-1"></i>CPF: <?php echo formatarCPF($paciente['cpf']); ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-phone mr-1"></i><?php echo formatarTelefone($paciente['telefone1']); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="text-right">
            <a href="<?php echo BASE_PATH; ?>pacientes.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar à Busca
            </a>
        </div>
    </div>
</div>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-blue-100 rounded-full p-3">
                <i class="fas fa-file-medical text-blue-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Total de Registros</p>
                <p class="text-3xl font-bold text-gray-900"><?php echo $total_registros; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-green-100 rounded-full p-3">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Agendados</p>
                <p class="text-3xl font-bold text-green-700"><?php echo $total_agendados; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-100 rounded-full p-3">
                <i class="fas fa-clock text-yellow-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Pendentes</p>
                <p class="text-3xl font-bold text-yellow-700"><?php echo $total_pendentes; ?></p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-red-100 rounded-full p-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Urgentes</p>
                <p class="text-3xl font-bold text-red-700"><?php echo $total_urgentes; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Timeline de Registros -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-history mr-2 text-blue-600"></i>
        Histórico Completo de Atendimentos
    </h2>
    
    <div class="space-y-6">
        <?php foreach ($registros as $index => $reg): ?>
        <div class="relative <?php echo $index < count($registros) - 1 ? 'pb-6' : ''; ?>">
            <!-- Linha vertical -->
            <?php if ($index < count($registros) - 1): ?>
            <div class="absolute left-6 top-12 bottom-0 w-0.5 bg-gray-300"></div>
            <?php endif; ?>
            
            <!-- Card do Registro -->
            <div class="relative flex items-start">
                <!-- Ícone Timeline -->
                <div class="flex-shrink-0">
                    <div class="h-12 w-12 rounded-full flex items-center justify-center <?php echo $reg['agendado'] ? 'bg-green-500' : 'bg-yellow-500'; ?> shadow-lg">
                        <i class="fas <?php echo $reg['agendado'] ? 'fa-check' : 'fa-clock'; ?> text-white text-xl"></i>
                    </div>
                </div>
                
                <!-- Conteúdo -->
                <div class="ml-6 flex-1">
                    <div class="bg-gray-50 border-l-4 <?php echo $reg['urgente'] ? 'border-red-600 bg-red-50' : ($reg['agendado'] ? 'border-green-500' : 'border-yellow-500'); ?> rounded-lg p-5 shadow-sm hover:shadow-md transition">
                        <!-- Cabeçalho -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($reg['tipo_atendimento']); ?>
                                    </h3>
                                    
                                    <?php if ($reg['agendado']): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                        <i class="fas fa-check mr-1"></i>Agendado
                                    </span>
                                    <?php else: ?>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                        <i class="fas fa-clock mr-1"></i>Aguardando
                                    </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($reg['urgente']): ?>
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full animate-pulse">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>URGENTE
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-1"></i>Solicitado em: <strong><?php echo formatarData($reg['data_solicitacao']); ?></strong>
                                    <?php if ($reg['agendado'] && $reg['data_agendamento']): ?>
                                        <span class="mx-2">•</span>
                                        <i class="fas fa-calendar-check mr-1"></i>Agendado para: <strong class="text-green-700"><?php echo formatarData($reg['data_agendamento']); ?></strong>
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="<?php echo BASE_PATH; ?>fila_espera_view.php?id=<?php echo $reg['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 px-3 py-1 rounded hover:bg-blue-50 transition" 
                                   title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_PATH; ?>fila_espera_form.php?id=<?php echo $reg['id']; ?>" 
                                   class="text-green-600 hover:text-green-800 px-3 py-1 rounded hover:bg-green-50 transition" 
                                   title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Detalhes em Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-user-md mr-1"></i>Médico
                                </p>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $reg['medico_nome'] ? htmlspecialchars($reg['medico_nome']) : '<span class="text-gray-400">Sem médico</span>'; ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-stethoscope mr-1"></i>Especialidade
                                </p>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($reg['especialidade_nome']); ?>
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-id-card mr-1"></i>Convênio
                                </p>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $reg['convenio_nome'] ? htmlspecialchars($reg['convenio_nome']) : 'Particular'; ?>
                                </p>
                            </div>
                            
                            <?php if ($reg['agendado'] && $reg['usuario_agendamento_nome']): ?>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-user-check mr-1"></i>Agendado por
                                </p>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($reg['usuario_agendamento_nome']); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($reg['agendado'] && $reg['data_hora_agendamento']): ?>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-clock mr-1"></i>Data/Hora Agendamento
                                </p>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo date('d/m/Y H:i', strtotime($reg['data_hora_agendamento'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Motivo Urgência -->
                        <?php if ($reg['urgente'] && !empty($reg['motivo_urgencia'])): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 p-3 rounded mb-4">
                            <p class="text-xs text-red-700 font-semibold mb-1">
                                <i class="fas fa-exclamation-circle mr-1"></i>Motivo da Urgência:
                            </p>
                            <p class="text-sm text-red-800">
                                <?php echo htmlspecialchars($reg['motivo_urgencia']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Observações -->
                        <?php if (!empty($reg['observacoes'])): ?>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
                            <p class="text-xs text-blue-700 font-semibold mb-1">
                                <i class="fas fa-comment mr-1"></i>Observações:
                            </p>
                            <p class="text-sm text-blue-900">
                                <?php echo nl2br(htmlspecialchars($reg['observacoes'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
