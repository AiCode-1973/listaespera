<?php
/**
 * Dashboard - Lista de Espera
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/FilaEspera.php';
require_once __DIR__ . '/models/Medico.php';
require_once __DIR__ . '/models/Especialidade.php';
require_once __DIR__ . '/models/Convenio.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$usuarioLogado = $auth->getUsuarioLogado();

// Inicializa models
$filaModel = new FilaEspera();
$medicoModel = new Medico();
$especialidadeModel = new Especialidade();
$convenioModel = new Convenio();

// Filtros
$filtros = [
    'medico_id' => $_GET['medico_id'] ?? '',
    'especialidade_id' => $_GET['especialidade_id'] ?? '',
    'convenio_id' => $_GET['convenio_id'] ?? '',
    'agendado' => $_GET['agendado'] ?? '0', // Padrão: apenas não agendados
    'urgente' => $_GET['urgente'] ?? '',
    'tipo_atendimento' => $_GET['tipo_atendimento'] ?? '',
    'guia_autorizada' => $_GET['guia_autorizada'] ?? '',
    'nome_paciente' => $_GET['nome_paciente'] ?? '',
    'data_solicitacao_inicio' => $_GET['data_inicio'] ?? '',
    'data_solicitacao_fim' => $_GET['data_fim'] ?? ''
];

// Remove filtros vazios
$filtros = array_filter($filtros, function($value) {
    return $value !== '';
});

// Paginação
$pagina = $_GET['pagina'] ?? 1;
$registrosPorPagina = 20;
$offset = ($pagina - 1) * $registrosPorPagina;

// Busca registros
$registros = $filaModel->listar($filtros, $registrosPorPagina, $offset);
$totalRegistros = $filaModel->contar($filtros);
$paginacao = paginar($totalRegistros, $registrosPorPagina, $pagina);

// Dados para os selects de filtro
$medicos = $medicoModel->listar(['ativo' => true]);
$especialidades = $especialidadeModel->listar();
$convenios = $convenioModel->listar();

$pageTitle = 'Lista de Espera';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Título da página -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-list-ul mr-3"></i>Lista de Espera - Consultas e Exames
        </h1>
        <p class="text-gray-600">Gerencie a fila de espera de pacientes para consultas e exames</p>
    </div>
    <div class="flex-shrink-0">
        <a href="<?php echo BASE_PATH; ?>fila_espera_form.php" 
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition flex items-center justify-center shadow-lg hover:shadow-xl">
            <i class="fas fa-plus mr-2"></i>Adicionar Paciente
        </a>
    </div>
</div>

<!-- Mensagens -->
<?php if (isset($_SESSION['mensagem_sucesso'])): ?>
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['mensagem_sucesso']); unset($_SESSION['mensagem_sucesso']); ?></span>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['mensagem_erro'])): ?>
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
    <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['mensagem_erro']); unset($_SESSION['mensagem_erro']); ?></span>
</div>
<?php endif; ?>

<!-- Área de filtros -->
<div class="bg-white rounded-lg shadow-md mb-4">
    <!-- Cabeçalho com botão Toggle -->
    <div class="p-4 border-b border-gray-200 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition" onclick="toggleFiltros()">
        <div class="flex items-center">
            <i class="fas fa-filter text-blue-600 text-xl mr-3"></i>
            <h2 class="text-lg font-bold text-gray-800">Filtros de Busca</h2>
        </div>
        <button type="button" class="text-gray-600 hover:text-gray-800 transition">
            <i id="iconeFiltros" class="fas fa-chevron-up text-xl"></i>
        </button>
    </div>
    
    <!-- Conteúdo dos Filtros (Colapsável) -->
    <div id="areaFiltros" class="transition-all duration-300 ease-in-out overflow-hidden">
        <form method="GET" action="" class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-3">
            
            <!-- Filtro Médico -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-md mr-1"></i>Médico
                </label>
                <select name="medico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os médicos</option>
                    <?php foreach ($medicos as $medico): ?>
                    <option value="<?php echo $medico['id']; ?>" <?php echo (isset($_GET['medico_id']) && $_GET['medico_id'] == $medico['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($medico['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Especialidade -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-stethoscope mr-1"></i>Especialidade
                </label>
                <select name="especialidade_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as especialidades</option>
                    <?php foreach ($especialidades as $esp): ?>
                    <option value="<?php echo $esp['id']; ?>" <?php echo (isset($_GET['especialidade_id']) && $_GET['especialidade_id'] == $esp['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($esp['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Convênio -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-1"></i>Convênio
                </label>
                <select name="convenio_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os convênios</option>
                    <?php foreach ($convenios as $conv): ?>
                    <option value="<?php echo $conv['id']; ?>" <?php echo (isset($_GET['convenio_id']) && $_GET['convenio_id'] == $conv['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($conv['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtro Status Agendado -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status Agendamento</label>
                <select name="agendado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php 
                    $agendadoFiltro = $_GET['agendado'] ?? '0'; // Padrão: Não Agendado
                    ?>
                    <option value="" <?php echo $agendadoFiltro === '' ? 'selected' : ''; ?>>Todos</option>
                    <option value="1" <?php echo $agendadoFiltro === '1' ? 'selected' : ''; ?>>Agendado</option>
                    <option value="0" <?php echo $agendadoFiltro === '0' ? 'selected' : ''; ?>>Não Agendado</option>
                </select>
            </div>
            
            <!-- Filtro Urgência -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Urgência</label>
                <select name="urgente" class="w-full px-3 py-2 border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-red-50">
                    <option value="">Todos</option>
                    <option value="1" <?php echo (isset($_GET['urgente']) && $_GET['urgente'] == '1') ? 'selected' : ''; ?>>🚨 Somente Urgentes</option>
                    <option value="0" <?php echo (isset($_GET['urgente']) && $_GET['urgente'] === '0') ? 'selected' : ''; ?>>Normais</option>
                </select>
            </div>
            
            <!-- Filtro Status Guia -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-file-medical mr-1"></i>Status da Guia
                </label>
                <select name="guia_autorizada" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="0" <?php echo (isset($_GET['guia_autorizada']) && $_GET['guia_autorizada'] === '0') ? 'selected' : ''; ?>>⏳ Aguardando Guia</option>
                    <option value="1" <?php echo (isset($_GET['guia_autorizada']) && $_GET['guia_autorizada'] === '1') ? 'selected' : ''; ?>>✅ Guia Autorizada</option>
                    <option value="null" <?php echo (isset($_GET['guia_autorizada']) && $_GET['guia_autorizada'] === 'null') ? 'selected' : ''; ?>>➖ Não Requer Guia</option>
                </select>
            </div>
            
            <!-- Filtro Tipo Atendimento -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Atendimento</label>
                <select name="tipo_atendimento" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos os tipos</option>
                    <option value="Consulta" <?php echo (isset($_GET['tipo_atendimento']) && $_GET['tipo_atendimento'] == 'Consulta') ? 'selected' : ''; ?>>Consulta</option>
                    <option value="Exame" <?php echo (isset($_GET['tipo_atendimento']) && $_GET['tipo_atendimento'] == 'Exame') ? 'selected' : ''; ?>>Exame</option>
                    <option value="Consulta + Exame" <?php echo (isset($_GET['tipo_atendimento']) && $_GET['tipo_atendimento'] == 'Consulta + Exame') ? 'selected' : ''; ?>>Consulta + Exame</option>
                    <option value="Retorno" <?php echo (isset($_GET['tipo_atendimento']) && $_GET['tipo_atendimento'] == 'Retorno') ? 'selected' : ''; ?>>Retorno</option>
                    <option value="Cirurgia" <?php echo (isset($_GET['tipo_atendimento']) && $_GET['tipo_atendimento'] == 'Cirurgia') ? 'selected' : ''; ?>>Cirurgia</option>
                    <option value="Procedimento" <?php echo (isset($_GET['tipo_atendimento']) && $_GET['tipo_atendimento'] == 'Procedimento') ? 'selected' : ''; ?>>Procedimento</option>
                </select>
            </div>

            <!-- Busca por Nome -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nome do Paciente</label>
                <input type="text" 
                       name="nome_paciente" 
                       value="<?php echo htmlspecialchars($_GET['nome_paciente'] ?? ''); ?>"
                       placeholder="Buscar por nome..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Data Início -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Data Início</label>
                <input type="date" 
                       name="data_inicio" 
                       id="data_inicio"
                       value="<?php echo htmlspecialchars($_GET['data_inicio'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Data Fim -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Data Fim</label>
                <input type="date" 
                       name="data_fim" 
                       id="data_fim"
                       value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Atalhos de Data -->
            <div class="md:col-span-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-clock mr-1"></i>Atalhos de Período
                </label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setPeriodo('hoje')" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">
                        Hoje
                    </button>
                    <button type="button" onclick="setPeriodo('ontem')" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">
                        Ontem
                    </button>
                    <button type="button" onclick="setPeriodo('semana')" class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition">
                        Esta Semana
                    </button>
                    <button type="button" onclick="setPeriodo('mes')" class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition">
                        Este Mês
                    </button>
                    <button type="button" onclick="setPeriodo('ultimos7')" class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition">
                        Últimos 7 dias
                    </button>
                    <button type="button" onclick="setPeriodo('ultimos30')" class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition">
                        Últimos 30 dias
                    </button>
                    <button type="button" onclick="abrirConsultaMedico()" class="px-3 py-1 text-xs bg-yellow-100 text-yellow-800 rounded hover:bg-yellow-200 transition">
                        <i class="fas fa-search mr-1"></i>Consultar Médico
                    </button>
                    <button type="button" onclick="limparDatas()" class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                        <i class="fas fa-times mr-1"></i>Limpar
                    </button>
                </div>
            </div>
        </div>

        <!-- Botões de ação -->
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-filter mr-2"></i>Filtrar
            </button>
            <a href="<?php echo BASE_PATH; ?>dashboard.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-eraser mr-2"></i>Limpar Filtros
            </a>
            <!--<a href="<?php echo BASE_PATH; ?>exportar_csv.php?<?php echo http_build_query($filtros); ?>" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-file-excel mr-2"></i>Exportar CSV
            </a>-->
        </div>
        </form>
    </div>
</div>

<!-- Aviso de Filtro Ativo -->
<?php if ($agendadoFiltro === '0'): ?>
<div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
    <div class="flex items-center">
        <i class="fas fa-info-circle text-blue-600 text-xl mr-3"></i>
        <div>
            <p class="font-semibold text-blue-900">Visualizando apenas registros NÃO AGENDADOS</p>
            <p class="text-sm text-blue-700 mt-1">
                Para ver todos os registros, selecione "Todos" no filtro de Status Agendamento acima.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabela de registros -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full table-hover text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Urgência</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Médico</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Especialidade</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Convênio</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Nome Paciente</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Tipo Atend.</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">CPF</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">D.Solicitação</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Agendado</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Atendente</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Telefone</th>
                    <th class="px-2 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (count($registros) > 0): ?>
                    <?php foreach ($registros as $reg): ?>
                    <tr class="<?php echo $reg['urgente'] ? 'bg-red-50 border-l-4 border-red-600 hover:bg-red-100' : 'hover:bg-gray-50'; ?>">
                        <td class="px-2 py-2">
                            <?php if ($reg['urgente']): ?>
                            <span class="chip bg-red-600 text-white font-bold animate-pulse">
                                <i class="fas fa-exclamation-triangle mr-1"></i>URGENTE
                            </span>
                            <?php else: ?>
                            <span class="chip bg-green-200 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Normal
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-700">
                            <?php echo $reg['medico_nome'] ? htmlspecialchars(substr($reg['medico_nome'], 0, 30)) : '<span class="text-gray-400">Sem médico</span>'; ?>
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-700">
                            <?php echo htmlspecialchars($reg['especialidade_nome']); ?>
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-700">
                            <?php echo $reg['convenio_nome'] ? htmlspecialchars($reg['convenio_nome']) : '<span class="text-gray-400">-</span>'; ?>
                        </td>
                        <td class="px-2 py-2 font-medium <?php echo $reg['urgente'] ? 'text-red-900 font-bold' : 'text-gray-900'; ?>">
                            <?php if ($reg['urgente']): ?>
                            <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($reg['nome_paciente']); ?>
                            <?php if ($reg['urgente'] && $reg['motivo_urgencia']): ?>
                            <span class="block text-xs text-red-600 font-normal mt-1" title="<?php echo htmlspecialchars($reg['motivo_urgencia']); ?>">
                                <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars(substr($reg['motivo_urgencia'], 0, 40)) . (strlen($reg['motivo_urgencia']) > 40 ? '...' : ''); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-2">
                            <?php if ($reg['tipo_atendimento']): 
                                $tiposAtendimentoCor = [
                                    'Consulta' => 'bg-blue-200 text-blue-800',
                                    'Exame' => 'bg-purple-200 text-purple-800',
                                    'Consulta + Exame' => 'bg-orange-200 text-orange-800',
                                    'Retorno' => 'bg-teal-200 text-teal-800',
                                    'Cirurgia' => 'bg-red-200 text-red-800',
                                    'Procedimento' => 'bg-pink-200 text-pink-800'
                                ];
                                $corTipo = $tiposAtendimentoCor[$reg['tipo_atendimento']] ?? 'bg-gray-200 text-gray-800';
                                $requerGuia = in_array($reg['tipo_atendimento'], ['Exame', 'Cirurgia', 'Consulta + Exame']);
                            ?>
                            <div>
                                <span class="chip <?php echo $corTipo; ?>">
                                    <?php echo htmlspecialchars($reg['tipo_atendimento']); ?>
                                </span>
                                <?php if ($requerGuia): ?>
                                    <div class="mt-1">
                                        <?php if ($reg['guia_autorizada'] === null): ?>
                                        <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded" title="Status da guia não informado">
                                            <i class="fas fa-question-circle"></i> Guia: Não informado
                                        </span>
                                        <?php elseif ($reg['guia_autorizada'] == 1): ?>
                                        <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded font-semibold" title="Guia autorizada em <?php echo formatarData($reg['data_autorizacao_guia']); ?>">
                                            <i class="fas fa-check-circle"></i> Guia Autorizada
                                        </span>
                                        <?php else: ?>
                                        <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded animate-pulse font-semibold" title="Aguardando autorização da guia">
                                            <i class="fas fa-hourglass-half"></i> Aguardando Guia
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-gray-400 text-sm">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-600">
                            <?php echo formatarCPF($reg['cpf']); ?>
                        </td>
                        <td class="px-2 py-2 text-sm text-gray-600">
                            <?php echo formatarData($reg['data_solicitacao']); ?>
                        </td>
                        <td class="px-2 py-2">
                            <?php if ($reg['agendado']): ?>
                            <span class="chip bg-green-200 text-green-800">
                                <i class="fas fa-check mr-1"></i>Sim
                            </span>
                            <div class="text-xs text-gray-500 mt-1">
                                <?php echo formatarData($reg['data_agendamento']); ?>
                            </div>
                            <?php else: ?>
                            <span class="chip bg-red-200 text-red-800">
                                <i class="fas fa-times mr-1"></i>Não
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-2 text-sm">
                            <?php 
                            // Prioridade: Nome de quem agendou -> Nome de quem cadastrou -> Texto de agendamento manual -> "-"
                            $atendenteNome = $reg['usuario_agendamento_nome'] ?: ($reg['usuario_criacao_nome'] ?: $reg['agendado_por']);
                            
                            if ($atendenteNome): 
                            ?>
                            <div class="flex items-center">
                                <i class="fas <?php echo $reg['agendado'] ? 'fa-user-check text-green-600' : 'fa-user-edit text-blue-600'; ?> mr-1"></i>
                                <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($atendenteNome); ?></span>
                            </div>
                            <?php else: ?>
                            <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-2 text-sm">
                            <?php 
                            // Telefone 1
                            $telefoneFormatado = formatarTelefone($reg['telefone1']);
                            $telefoneWhatsApp = preg_replace('/[^0-9]/', '', $reg['telefone1']);
                            // Adicionar código do país Brasil (55) se ainda não tiver
                            if (strlen($telefoneWhatsApp) <= 11 && !str_starts_with($telefoneWhatsApp, '55')) {
                                $telefoneWhatsApp = '55' . $telefoneWhatsApp;
                            }
                            ?>
                            <a href="https://wa.me/<?php echo $telefoneWhatsApp; ?>" 
                               target="_blank"
                               class="inline-flex items-center text-green-600 hover:text-green-800 hover:underline transition"
                               title="Abrir WhatsApp com <?php echo $telefoneFormatado; ?>">
                                <i class="fab fa-whatsapp text-lg mr-1"></i>
                                <span><?php echo $telefoneFormatado; ?></span>
                            </a>
                            
                            <?php if (!empty($reg['telefone2'])): ?>
                                <?php 
                                $telefone2Formatado = formatarTelefone($reg['telefone2']);
                                $telefone2WhatsApp = preg_replace('/[^0-9]/', '', $reg['telefone2']);
                                if (strlen($telefone2WhatsApp) <= 11 && !str_starts_with($telefone2WhatsApp, '55')) {
                                    $telefone2WhatsApp = '55' . $telefone2WhatsApp;
                                }
                                ?>
                                <br>
                                <a href="https://wa.me/<?php echo $telefone2WhatsApp; ?>" 
                                   target="_blank"
                                   class="inline-flex items-center text-green-500 hover:text-green-700 hover:underline transition text-xs mt-1"
                                   title="Abrir WhatsApp com <?php echo $telefone2Formatado; ?>">
                                    <i class="fab fa-whatsapp text-sm mr-1"></i>
                                    <span><?php echo $telefone2Formatado; ?></span>
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="px-2 py-2">
                            <div class="flex space-x-2">
                                <a href="<?php echo BASE_PATH; ?>fila_espera_form.php?id=<?php echo $reg['id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo BASE_PATH; ?>fila_espera_view.php?id=<?php echo $reg['id']; ?>" 
                                   class="text-green-600 hover:text-green-800" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($usuarioLogado['perfil'] === 'administrador'): ?>
                                <a href="<?php echo BASE_PATH; ?>fila_espera_deletar.php?id=<?php echo $reg['id']; ?>" 
                                   onclick="return confirm('⚠️ ATENÇÃO!\n\nDeseja realmente EXCLUIR este registro?\n\nPaciente: <?php echo htmlspecialchars($reg['nome_paciente']); ?>\nMédico: <?php echo htmlspecialchars($reg['medico_nome']); ?>\n\nEsta ação NÃO pode ser desfeita!');"
                                   class="text-red-600 hover:text-red-800" 
                                   title="Excluir registro (somente admin)">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <?php if ($agendadoFiltro === '0'): ?>
                                <p class="font-semibold text-lg">✅ Nenhum registro aguardando agendamento!</p>
                                <p class="text-sm mt-2">Todos os pacientes já foram agendados.</p>
                                <a href="?agendado=" class="inline-block mt-3 text-blue-600 hover:text-blue-800 underline">
                                    Ver todos os registros (incluindo agendados)
                                </a>
                            <?php else: ?>
                                <p>Nenhum registro encontrado com os filtros selecionados</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($paginacao['total_paginas'] > 1): ?>
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando <span class="font-semibold"><?php echo ($offset + 1); ?></span> até 
                <span class="font-semibold"><?php echo min($offset + $registrosPorPagina, $totalRegistros); ?></span> de 
                <span class="font-semibold"><?php echo $totalRegistros; ?></span> registros
            </div>
            <div class="flex space-x-2">
                <?php if ($pagina > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" 
                   class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100">
                    Anterior
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $pagina - 2); $i <= min($paginacao['total_paginas'], $pagina + 2); $i++): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                   class="px-3 py-1 <?php echo $i == $pagina ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-100'; ?> rounded">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($pagina < $paginacao['total_paginas']): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" 
                   class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100">
                    Próximo
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Funções para atalhos de período de data
function setPeriodo(tipo) {
    const hoje = new Date();
    let dataInicio, dataFim;
    
    switch(tipo) {
        case 'hoje':
            dataInicio = dataFim = formatarDataISO(hoje);
            break;
            
        case 'ontem':
            const ontem = new Date(hoje);
            ontem.setDate(hoje.getDate() - 1);
            dataInicio = dataFim = formatarDataISO(ontem);
            break;
            
        case 'semana':
            // Primeiro dia da semana (domingo)
            const primeiroDia = new Date(hoje);
            primeiroDia.setDate(hoje.getDate() - hoje.getDay());
            dataInicio = formatarDataISO(primeiroDia);
            dataFim = formatarDataISO(hoje);
            break;
            
        case 'mes':
            // Primeiro dia do mês
            const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            dataInicio = formatarDataISO(primeiroDiaMes);
            dataFim = formatarDataISO(hoje);
            break;
            
        case 'ultimos7':
            const ultimos7 = new Date(hoje);
            ultimos7.setDate(hoje.getDate() - 6);
            dataInicio = formatarDataISO(ultimos7);
            dataFim = formatarDataISO(hoje);
            break;
            
        case 'ultimos30':
            const ultimos30 = new Date(hoje);
            ultimos30.setDate(hoje.getDate() - 29);
            dataInicio = formatarDataISO(ultimos30);
            dataFim = formatarDataISO(hoje);
            break;
    }
    
    document.getElementById('data_inicio').value = dataInicio;
    document.getElementById('data_fim').value = dataFim;
}

function limparDatas() {
    document.getElementById('data_inicio').value = '';
    document.getElementById('data_fim').value = '';
}

function formatarDataISO(data) {
    const ano = data.getFullYear();
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');
    return `${ano}-${mes}-${dia}`;
}

// Função para abrir/fechar os filtros
function toggleFiltros() {
    const areaFiltros = document.getElementById('areaFiltros');
    const iconeFiltros = document.getElementById('iconeFiltros');
    
    if (areaFiltros.style.maxHeight && areaFiltros.style.maxHeight !== '0px') {
        // Fechar
        areaFiltros.style.maxHeight = '0px';
        areaFiltros.style.opacity = '0';
        iconeFiltros.classList.remove('fa-chevron-up');
        iconeFiltros.classList.add('fa-chevron-down');
        localStorage.setItem('filtrosAbertos', 'false');
    } else {
        // Abrir
        areaFiltros.style.maxHeight = areaFiltros.scrollHeight + 'px';
        areaFiltros.style.opacity = '1';
        iconeFiltros.classList.remove('fa-chevron-down');
        iconeFiltros.classList.add('fa-chevron-up');
        localStorage.setItem('filtrosAbertos', 'true');
    }
}

// Inicializar estado dos filtros ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    const areaFiltros = document.getElementById('areaFiltros');
    const iconeFiltros = document.getElementById('iconeFiltros');
    const filtrosAbertos = localStorage.getItem('filtrosAbertos');
    
    // Por padrão, deixar aberto na primeira vez ou se estava aberto
    if (filtrosAbertos === 'false') {
        areaFiltros.style.maxHeight = '0px';
        areaFiltros.style.opacity = '0';
        iconeFiltros.classList.remove('fa-chevron-up');
        iconeFiltros.classList.add('fa-chevron-down');
    } else {
        // Aberto por padrão
        areaFiltros.style.maxHeight = areaFiltros.scrollHeight + 'px';
        areaFiltros.style.opacity = '1';
    }
});

// Função para abrir modal de consulta de médico
function abrirConsultaMedico() {
    document.getElementById('modalConsultaMedico').classList.remove('hidden');
    document.getElementById('buscaMedico').focus();
}

// Função para fechar modal de consulta de médico
function fecharConsultaMedico() {
    document.getElementById('modalConsultaMedico').classList.add('hidden');
    document.getElementById('buscaMedico').value = '';
    document.getElementById('resultadoBusca').innerHTML = '';
    document.getElementById('infoMedico').classList.add('hidden');
}

// Função para buscar médicos
function buscarMedicos() {
    const busca = document.getElementById('buscaMedico').value.trim();
    const resultadoDiv = document.getElementById('resultadoBusca');
    
    if (busca.length < 2) {
        resultadoDiv.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Digite pelo menos 2 caracteres para buscar</p>';
        return;
    }
    
    resultadoDiv.innerHTML = '<p class="text-sm text-gray-500 text-center py-4"><i class="fas fa-spinner fa-spin"></i> Buscando...</p>';
    
    // Busca via fetch
    fetch('<?php echo BASE_PATH; ?>buscar_medico.php?busca=' + encodeURIComponent(busca))
        .then(response => response.json())
        .then(data => {
            if (data.sucesso && data.medicos.length > 0) {
                let html = '<div class="space-y-2">';
                data.medicos.forEach(medico => {
                    html += `
                        <div onclick="selecionarMedico(${medico.id})" 
                             class="p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition">
                            <p class="font-semibold text-gray-800">${medico.nome}</p>
                            <p class="text-xs text-gray-500">${medico.especialidades || 'Sem especialidade'}</p>
                        </div>
                    `;
                });
                html += '</div>';
                resultadoDiv.innerHTML = html;
            } else {
                resultadoDiv.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Nenhum médico encontrado</p>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            resultadoDiv.innerHTML = '<p class="text-sm text-red-500 text-center py-4">Erro ao buscar médicos</p>';
        });
}

// Função para selecionar médico e exibir detalhes
function selecionarMedico(id) {
    const infoDiv = document.getElementById('infoMedico');
    infoDiv.innerHTML = '<p class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Carregando...</p>';
    infoDiv.classList.remove('hidden');
    
    fetch('<?php echo BASE_PATH; ?>buscar_medico.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.sucesso && data.medico) {
                const medico = data.medico;
                let html = `
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border-2 border-blue-200">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800 mb-1">
                                    <i class="fas fa-user-md text-blue-600 mr-2"></i>${medico.nome}
                                </h3>
                                ${medico.crm_cpf ? `<p class="text-sm text-gray-600">CRM/CPF: ${medico.crm_cpf}</p>` : ''}
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${medico.ativo ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-800'}">
                                ${medico.ativo ? 'Ativo' : 'Inativo'}
                            </span>
                        </div>
                        
                        <div class="space-y-4">
                            ${medico.telefone ? `
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-blue-600 w-6"></i>
                                    <span class="text-gray-700">${medico.telefone}</span>
                                </div>
                            ` : ''}
                            
                            ${medico.email ? `
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-blue-600 w-6"></i>
                                    <span class="text-gray-700">${medico.email}</span>
                                </div>
                            ` : ''}
                            
                            <div class="pt-3 border-t border-blue-200">
                                <p class="text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-stethoscope text-blue-600 mr-2"></i>Especialidades:
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    ${medico.especialidades ? medico.especialidades.split(',').map(esp => 
                                        `<span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">${esp.trim()}</span>`
                                    ).join('') : '<span class="text-sm text-gray-500">Nenhuma especialidade cadastrada</span>'}
                                </div>
                            </div>
                            
                            ${medico.observacao ? `
                                <div class="pt-3 border-t border-blue-200">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>Observações:
                                    </p>
                                    <div class="bg-white rounded-lg p-4 border border-blue-200">
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">${medico.observacao}</p>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                infoDiv.innerHTML = html;
            } else {
                infoDiv.innerHTML = '<p class="text-center text-red-500 py-4">Erro ao carregar informações do médico</p>';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            infoDiv.innerHTML = '<p class="text-center text-red-500 py-4">Erro ao carregar informações do médico</p>';
        });
}

// Buscar ao digitar
let timeoutBusca;
document.addEventListener('DOMContentLoaded', function() {
    const inputBusca = document.getElementById('buscaMedico');
    if (inputBusca) {
        inputBusca.addEventListener('input', function() {
            clearTimeout(timeoutBusca);
            timeoutBusca = setTimeout(buscarMedicos, 500);
        });
    }
});
</script>

<!-- Modal de Consulta de Médico -->
<div id="modalConsultaMedico" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-t-lg flex items-center justify-between flex-shrink-0">
            <h3 class="text-xl font-bold flex items-center">
                <i class="fas fa-search mr-3 text-2xl"></i>
                Consultar Médico
            </h3>
            <button onclick="fecharConsultaMedico()" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-grow">
            <!-- Campo de Busca -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-md mr-1"></i>Digite o nome do médico:
                </label>
                <input type="text" 
                       id="buscaMedico"
                       placeholder="Ex: João, Maria, Dr. Silva..."
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       autocomplete="off">
            </div>
            
            <!-- Resultados da Busca -->
            <div id="resultadoBusca" class="mb-6">
                <p class="text-sm text-gray-500 text-center py-4">Digite para buscar médicos...</p>
            </div>
            
            <!-- Informações do Médico Selecionado -->
            <div id="infoMedico" class="hidden">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end flex-shrink-0">
            <button onclick="fecharConsultaMedico()" 
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Fechar
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
