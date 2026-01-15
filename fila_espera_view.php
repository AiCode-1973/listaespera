<?php
/**
 * Visualização Detalhada de Paciente na Fila
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/FilaEspera.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

// Inicializa model
$filaModel = new FilaEspera();

// Busca registro
if (!isset($_GET['id'])) {
    $_SESSION['mensagem_erro'] = 'Registro não especificado';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit();
}

$registro = $filaModel->buscarPorId($_GET['id']);

if (!$registro) {
    $_SESSION['mensagem_erro'] = 'Registro não encontrado';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit();
}

$pageTitle = 'Visualizar Paciente';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <!-- Cabeçalho -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-eye mr-3"></i>Detalhes do Paciente
            </h1>
            <p class="text-gray-600">Informações completas do registro na lista de espera</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?php echo BASE_PATH; ?>fila_espera_form.php?id=<?php echo $registro['id']; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="<?php echo BASE_PATH; ?>dashboard.php" 
               class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </div>

    <!-- Card de informações -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        
        <!-- Header do card com status -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4 flex items-center justify-between">
            <div class="text-white">
                <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($registro['nome_paciente']); ?></h2>
                <p class="text-blue-100 text-sm">CPF: <?php echo formatarCPF($registro['cpf']); ?></p>
            </div>
            <div>
                <?php if ($registro['agendado']): ?>
                <span class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>Agendado
                </span>
                <?php else: ?>
                <span class="bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold flex items-center">
                    <i class="fas fa-clock mr-2"></i>Aguardando
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Corpo do card -->
        <div class="p-6 space-y-6">
            
            <!-- Seção: Informações Médicas -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-200">
                    <i class="fas fa-stethoscope mr-2 text-blue-600"></i>Informações Médicas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Médico</label>
                        <div class="text-gray-900">
                            <?php if ($registro['medico_nome']): ?>
                            <span class="chip bg-purple-200 text-purple-800">
                                <?php echo htmlspecialchars($registro['medico_nome']); ?>
                            </span>
                            <?php else: ?>
                            <span class="chip bg-gray-200 text-gray-600">
                                Sem médico definido
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Especialidade</label>
                        <div class="text-gray-900">
                            <span class="chip <?php echo gerarClasseChip($registro['especialidade_cor'] ?? 'bg-blue-200'); ?>">
                                <?php echo htmlspecialchars($registro['especialidade_nome']); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Convênio</label>
                        <div class="text-gray-900">
                            <?php if ($registro['convenio_nome']): ?>
                            <span class="chip <?php echo gerarClasseChip($registro['convenio_cor'] ?? 'bg-green-200'); ?>">
                                <?php echo htmlspecialchars($registro['convenio_nome']); ?>
                            </span>
                            <?php else: ?>
                            <span class="text-gray-400">Não informado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Tipo/Informação</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo htmlspecialchars($registro['informacao'] ?: 'Não informado'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção: Dados do Paciente -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-200">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Dados do Paciente
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Nome Completo</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo htmlspecialchars($registro['nome_paciente']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">CPF</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo formatarCPF($registro['cpf']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Data de Nascimento</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo formatarData($registro['data_nascimento']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Idade Aproximada</label>
                        <div class="text-gray-900 font-medium">
                            <?php 
                            $idade = date_diff(date_create($registro['data_nascimento']), date_create('now'))->y;
                            echo $idade . ' anos';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção: Contato -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-200">
                    <i class="fas fa-phone mr-2 text-blue-600"></i>Contato
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Telefone Principal</label>
                        <div class="text-gray-900 font-medium">
                            <a href="tel:<?php echo $registro['telefone1']; ?>" class="text-blue-600 hover:underline">
                                <i class="fas fa-phone-alt mr-1"></i><?php echo formatarTelefone($registro['telefone1']); ?>
                            </a>
                        </div>
                    </div>
                    <?php if ($registro['telefone2']): ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Telefone Secundário</label>
                        <div class="text-gray-900 font-medium">
                            <a href="tel:<?php echo $registro['telefone2']; ?>" class="text-blue-600 hover:underline">
                                <i class="fas fa-phone-alt mr-1"></i><?php echo formatarTelefone($registro['telefone2']); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seção: Datas e Agendamento -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-200">
                    <i class="fas fa-calendar mr-2 text-blue-600"></i>Datas e Agendamento
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Data da Solicitação</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo formatarData($registro['data_solicitacao']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Status do Agendamento</label>
                        <div>
                            <?php if ($registro['agendado']): ?>
                            <span class="chip bg-green-200 text-green-800">
                                <i class="fas fa-check mr-1"></i>Agendado
                            </span>
                            <?php else: ?>
                            <span class="chip bg-red-200 text-red-800">
                                <i class="fas fa-times mr-1"></i>Não Agendado
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($registro['agendado']): ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Data do Agendamento</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo formatarData($registro['data_agendamento']); ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Agendado Por</label>
                        <div class="text-gray-900 font-medium">
                            <?php echo htmlspecialchars($registro['agendado_por'] ?: 'Não informado'); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seção: Observações -->
            <?php if ($registro['observacao']): ?>
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-blue-200">
                    <i class="fas fa-sticky-note mr-2 text-blue-600"></i>Observações
                </h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($registro['observacao']); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Seção: Metadados -->
            <div>
                <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b-2 border-gray-200">
                    <i class="fas fa-info-circle mr-2 text-gray-600"></i>Informações do Sistema
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <label class="block font-semibold mb-1">Registro Criado em</label>
                        <div><?php echo date('d/m/Y H:i:s', strtotime($registro['created_at'])); ?></div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Última Atualização</label>
                        <div><?php echo date('d/m/Y H:i:s', strtotime($registro['updated_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer do card com ações -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <a href="<?php echo BASE_PATH; ?>fila_espera_form.php?id=<?php echo $registro['id']; ?>" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-edit mr-2"></i>Editar Registro
            </a>
            <a href="<?php echo BASE_PATH; ?>dashboard.php" 
               class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Voltar para Lista
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
