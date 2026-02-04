<?php
/**
 * Formulário de Cadastro/Edição de Fila de Espera
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/FilaEspera.php';
require_once __DIR__ . '/models/Medico.php';
require_once __DIR__ . '/models/Especialidade.php';
require_once __DIR__ . '/models/Convenio.php';
require_once __DIR__ . '/includes/functions.php';

// MODO DESENVOLVIMENTO: Desabilita validação estrita de CPF
// Altere para FALSE em produção!
define('MODO_DESENVOLVIMENTO', true);

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();
$usuarioLogado = $auth->getUsuarioLogado();

// Inicializa models
$filaModel = new FilaEspera();
$medicoModel = new Medico();
$especialidadeModel = new Especialidade();
$convenioModel = new Convenio();

$erros = [];
$registro = null;
$isEdicao = false;

/**
 * Registra mensagem WhatsApp pendente no histórico
 */
function registrarMensagemPendente($filaEsperaId) {
    if (!isset($_SESSION['mensagem_whatsapp_pendente'])) {
        return;
    }
    
    require_once __DIR__ . '/models/HistoricoMensagem.php';
    $historicoModel = new HistoricoMensagem();
    
    $mensagemPendente = $_SESSION['mensagem_whatsapp_pendente'];
    
    $dados = [
        'fila_espera_id' => $filaEsperaId,
        'paciente_id' => null,
        'usuario_id' => $_SESSION['usuario_id'],
        'telefone' => $mensagemPendente['telefone'],
        'mensagem' => $mensagemPendente['mensagem'],
        'tipo_mensagem' => 'confirmacao_agendamento'
    ];
    
    $historicoModel->registrar($dados);
    
    // Limpa mensagem pendente da sessão
    unset($_SESSION['mensagem_whatsapp_pendente']);
}

// Se está editando
if (isset($_GET['id'])) {
    $isEdicao = true;
    $registro = $filaModel->buscarPorId($_GET['id']);
    
    if (!$registro) {
        $_SESSION['mensagem_erro'] = 'Registro não encontrado';
        header('Location: ' . BASE_PATH . 'dashboard.php');
        exit();
    }
}

// Processa formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validações
    // Médico não é mais obrigatório
    // if (empty($_POST['medico_id'])) {
    //     $erros[] = 'Médico é obrigatório';
    // }
    
    if (empty($_POST['especialidade_id'])) {
        $erros[] = 'Especialidade é obrigatória';
    }
    
    if (empty($_POST['nome_paciente'])) {
        $erros[] = 'Nome do paciente é obrigatório';
    }
    
    if (empty($_POST['cpf'])) {
        $erros[] = 'CPF é obrigatório';
    } else {
        $cpfLimpo = limparCPF($_POST['cpf']);
        if (strlen($cpfLimpo) != 11) {
            $erros[] = 'CPF deve conter 11 dígitos';
        } elseif (!MODO_DESENVOLVIMENTO && !validarCPF($_POST['cpf'])) {
            // Em modo desenvolvimento, a validação de CPF é desabilitada
            $erros[] = 'CPF inválido - Verifique os dígitos verificadores';
        }
    }
    
    if (empty($_POST['data_nascimento'])) {
        $erros[] = 'Data de nascimento é obrigatória';
    }
    
    if (empty($_POST['data_solicitacao'])) {
        $erros[] = 'Data de solicitação é obrigatória';
    }
    
    if (empty($_POST['telefone1'])) {
        $erros[] = 'Telefone é obrigatório';
    }
    
    // Validar urgência
    $urgente = isset($_POST['urgente']) ? 1 : 0;
    if ($urgente && empty($_POST['motivo_urgencia'])) {
        $erros[] = 'Motivo da urgência é obrigatório quando marcado como urgente';
    }
    
    // Se agendado, validar data e horário de agendamento
    $agendado = isset($_POST['agendado']) ? 1 : 0;
    if ($agendado && empty($_POST['data_agendamento'])) {
        $erros[] = 'Data de agendamento é obrigatória quando marcado como agendado';
    }
    if ($agendado && empty($_POST['horario_agendamento'])) {
        $erros[] = 'Horário de agendamento é obrigatório quando marcado como agendado';
    }
    
    // Validar autorização de guia para exames e cirurgias
    $tipoAtendimento = $_POST['tipo_atendimento'] ?? null;
    $requerGuia = in_array($tipoAtendimento, ['Exame', 'Cirurgia', 'Consulta + Exame']);
    
    if ($requerGuia && $agendado) {
        if (!isset($_POST['guia_autorizada']) || $_POST['guia_autorizada'] === '') {
            $erros[] = 'Para agendar exames ou cirurgias, é necessário informar se a guia está autorizada';
        } elseif ($_POST['guia_autorizada'] == '0') {
            $erros[] = 'Não é possível agendar sem a guia autorizada. Aguarde a autorização da guia.';
        }
    }
    
    if (empty($erros)) {
        $dados = [
            'medico_id' => $_POST['medico_id'] ?: null,
            'especialidade_id' => $_POST['especialidade_id'],
            'convenio_id' => $_POST['convenio_id'] ?: null,
            'nome_paciente' => $_POST['nome_paciente'],
            'cpf' => limparCPF($_POST['cpf']),
            'data_nascimento' => converterDataBanco($_POST['data_nascimento']),
            'data_solicitacao' => converterDataBanco($_POST['data_solicitacao']),
            'informacao' => $_POST['informacao'] ?? null,
            'observacao' => $_POST['observacao'] ?? null,
            'agendado' => $agendado,
            'data_agendamento' => $agendado ? converterDataBanco($_POST['data_agendamento']) : null,
            'horario_agendamento' => $agendado ? $_POST['horario_agendamento'] : null,
            'telefone1' => $_POST['telefone1'],
            'telefone2' => $_POST['telefone2'] ?? null,
            'agendado_por' => $agendado ? $usuarioLogado['nome'] : null,
            'usuario_agendamento_id' => $agendado ? $usuarioLogado['id'] : null,
            'data_hora_agendamento' => $agendado ? date('Y-m-d H:i:s') : null,
            'urgente' => $urgente,
            'motivo_urgencia' => $urgente ? $_POST['motivo_urgencia'] : null,
            'tipo_atendimento' => $_POST['tipo_atendimento'] ?? null,
            'guia_autorizada' => $requerGuia ? ($_POST['guia_autorizada'] ?? null) : null,
            'data_autorizacao_guia' => ($requerGuia && isset($_POST['guia_autorizada']) && $_POST['guia_autorizada'] == '1' && !empty($_POST['data_autorizacao_guia'])) ? converterDataBanco($_POST['data_autorizacao_guia']) : null,
            'observacao_guia' => $requerGuia ? ($_POST['observacao_guia'] ?? null) : null,
            'usuario_id' => $usuarioLogado['id']
        ];
        
        // Verifica duplicidade
        $excluirId = $isEdicao ? $_GET['id'] : null;
        if ($filaModel->verificarDuplicidade($dados['cpf'], $dados['medico_id'], $dados['data_solicitacao'], $excluirId)) {
            $erros[] = 'Já existe um registro para este paciente/médico na mesma data de solicitação';
        }
        
        if (empty($erros)) {
            if ($isEdicao) {
                if ($filaModel->atualizar($_GET['id'], $dados)) {
                    // Registra mensagem se houver pendente
                    registrarMensagemPendente($_GET['id']);
                    
                    $_SESSION['mensagem_sucesso'] = 'Registro atualizado com sucesso';
                    header('Location: ' . BASE_PATH . 'dashboard.php');
                    exit();
                } else {
                    $erros[] = 'Erro ao atualizar registro';
                }
            } else {
                $novoId = $filaModel->criar($dados);
                if ($novoId) {
                    // Registra mensagem se houver pendente
                    registrarMensagemPendente($novoId);
                    
                    $_SESSION['mensagem_sucesso'] = 'Paciente adicionado à fila com sucesso';
                    header('Location: ' . BASE_PATH . 'dashboard.php');
                    exit();
                } else {
                    $erros[] = 'Erro ao criar registro';
                }
            }
        }
    }
}

// Busca dados para selects
$medicos = $medicoModel->listar(['ativo' => true]);
$especialidades = $especialidadeModel->listar();
$convenios = $convenioModel->listar();

$pageTitle = $isEdicao ? 'Editar Paciente' : 'Adicionar Paciente';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="max-w-4xl mx-auto">
    <!-- Título -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-<?php echo $isEdicao ? 'edit' : 'plus'; ?> mr-3"></i>
            <?php echo $isEdicao ? 'Editar Paciente na Fila' : 'Adicionar Paciente na Fila'; ?>
        </h1>
        <p class="text-gray-600">Preencha os dados do paciente para <?php echo $isEdicao ? 'atualizar o' : 'incluir na'; ?> lista de espera</p>
    </div>

    <!-- Erros -->
    <?php if (!empty($erros)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
        <ul class="list-disc list-inside">
            <?php foreach ($erros as $erro): ?>
            <li><?php echo htmlspecialchars($erro); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Formulário -->
    <form method="POST" action="" class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Médico -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Médico
                </label>
                <select name="medico_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione um médico</option>
                    <?php foreach ($medicos as $medico): ?>
                    <option value="<?php echo $medico['id']; ?>" 
                            <?php echo (($registro['medico_id'] ?? $_POST['medico_id'] ?? '') == $medico['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($medico['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Especialidade -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Especialidade <span class="text-red-500">*</span>
                </label>
                <select name="especialidade_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione uma especialidade</option>
                    <?php foreach ($especialidades as $esp): ?>
                    <option value="<?php echo $esp['id']; ?>" 
                            <?php echo (($registro['especialidade_id'] ?? $_POST['especialidade_id'] ?? '') == $esp['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($esp['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Convênio -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Convênio</label>
                <select name="convenio_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione um convênio</option>
                    <?php foreach ($convenios as $conv): ?>
                    <option value="<?php echo $conv['id']; ?>" 
                            <?php echo (($registro['convenio_id'] ?? $_POST['convenio_id'] ?? '') == $conv['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($conv['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nome Paciente -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome do Paciente <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome_paciente" 
                       required
                       value="<?php echo htmlspecialchars($registro['nome_paciente'] ?? $_POST['nome_paciente'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- CPF -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    CPF <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="cpf" 
                       required
                       onkeyup="mascaraCPF(this)"
                       maxlength="14"
                       value="<?php 
                           if (isset($registro['cpf'])) {
                               echo formatarCPF($registro['cpf']);
                           } elseif (isset($_POST['cpf'])) {
                               echo formatarCPF($_POST['cpf']);
                           }
                       ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Data Nascimento -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Data de Nascimento <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="data_nascimento" 
                       required
                       onkeyup="mascaraData(this)"
                       maxlength="10"
                       placeholder="DD/MM/AAAA"
                       value="<?php echo isset($registro['data_nascimento']) ? formatarData($registro['data_nascimento']) : htmlspecialchars($_POST['data_nascimento'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Data Solicitação -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Data de Solicitação <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="data_solicitacao" 
                       required
                       onkeyup="mascaraData(this)"
                       maxlength="10"
                       placeholder="DD/MM/AAAA"
                       value="<?php echo isset($registro['data_solicitacao']) ? formatarData($registro['data_solicitacao']) : (htmlspecialchars($_POST['data_solicitacao'] ?? '') ?: date('d/m/Y')); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Tipo de Atendimento -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Atendimento</label>
                <select name="tipo_atendimento" 
                        id="tipo_atendimento"
                        onchange="toggleCamposGuia()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione</option>
                    <?php 
                    $tiposAtendimento = ['Consulta', 'Exame', 'Consulta + Exame', 'Retorno', 'Cirurgia', 'Procedimento'];
                    foreach ($tiposAtendimento as $tipo):
                    ?>
                    <option value="<?php echo $tipo; ?>" 
                            <?php echo (($registro['tipo_atendimento'] ?? $_POST['tipo_atendimento'] ?? '') == $tipo) ? 'selected' : ''; ?>>
                        <?php echo $tipo; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Seção de Autorização de Guia (Exames/Cirurgias) -->
        <?php 
        $tipoRequerGuia = in_array(($registro['tipo_atendimento'] ?? ''), ['Exame', 'Cirurgia', 'Consulta + Exame']);
        ?>
        <div id="divAutorizacaoGuia" class="mt-6 p-4 border-2 border-yellow-300 rounded-lg bg-yellow-50" style="display: <?php echo $tipoRequerGuia ? 'block' : 'none'; ?>;">
            <h3 class="text-lg font-bold text-yellow-800 mb-4">
                <i class="fas fa-file-medical mr-2"></i>Autorização de Guia (Obrigatório para Exames/Cirurgias)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status da Guia -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Guia Autorizada? <span class="text-red-500">*</span>
                    </label>
                    <select name="guia_autorizada" 
                            id="guia_autorizada"
                            onchange="toggleDataAutorizacaoGuia()"
                            class="w-full px-3 py-2 border-2 border-yellow-400 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white">
                        <option value="">Selecione</option>
                        <option value="1" <?php echo (isset($registro['guia_autorizada']) && $registro['guia_autorizada'] == 1) ? 'selected' : ''; ?>>
                            ✅ Sim - Guia Autorizada
                        </option>
                        <option value="0" <?php echo (isset($registro['guia_autorizada']) && $registro['guia_autorizada'] === 0) ? 'selected' : ''; ?>>
                            ⏳ Não - Aguardando Autorização
                        </option>
                    </select>
                    <p class="text-xs text-yellow-700 mt-1">
                        <i class="fas fa-info-circle"></i> Não é possível agendar sem autorização
                    </p>
                </div>
                
                <!-- Data de Autorização da Guia -->
                <div id="divDataAutorizacaoGuia" style="display: <?php echo (isset($registro['guia_autorizada']) && $registro['guia_autorizada'] == 1) ? 'block' : 'none'; ?>;">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Data da Autorização
                    </label>
                    <input type="text" 
                           name="data_autorizacao_guia" 
                           id="data_autorizacao_guia"
                           onkeyup="mascaraData(this)"
                           maxlength="10"
                           placeholder="DD/MM/AAAA"
                           value="<?php echo isset($registro['data_autorizacao_guia']) ? formatarData($registro['data_autorizacao_guia']) : htmlspecialchars($_POST['data_autorizacao_guia'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- Observação da Guia (número, código, etc) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Observação (Número da guia, código de autorização, etc)
                    </label>
                    <textarea name="observacao_guia" 
                              rows="2"
                              placeholder="Ex: Guia nº 12345, autorizada em..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($registro['observacao_guia'] ?? $_POST['observacao_guia'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <!-- Telefone 1 -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Telefone 1 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="telefone1" 
                       required
                       onkeyup="mascaraTelefone(this)"
                       maxlength="15"
                       placeholder="(XX) XXXXX-XXXX"
                       value="<?php echo isset($registro['telefone1']) ? formatarTelefone($registro['telefone1']) : htmlspecialchars($_POST['telefone1'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Telefone 2 -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone 2</label>
                <input type="text" 
                       name="telefone2" 
                       onkeyup="mascaraTelefone(this)"
                       maxlength="15"
                       placeholder="(XX) XXXXX-XXXX"
                       value="<?php echo isset($registro['telefone2']) ? formatarTelefone($registro['telefone2']) : htmlspecialchars($_POST['telefone2'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Urgente -->
            <div class="md:col-span-2">
                <label class="flex items-center space-x-3 cursor-pointer p-3 border-2 border-red-300 rounded-lg bg-red-50 hover:bg-red-100 transition">
                    <input type="checkbox" 
                           name="urgente" 
                           id="urgente"
                           value="1"
                           <?php echo (($registro['urgente'] ?? $_POST['urgente'] ?? false)) ? 'checked' : ''; ?>
                           onchange="toggleMotivoUrgencia()"
                           class="w-6 h-6 text-red-600 border-red-300 rounded focus:ring-2 focus:ring-red-500">
                    <span class="text-base font-bold text-red-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i>MARCAR COMO URGENTE
                    </span>
                </label>
            </div>
            
            <!-- Motivo da Urgência -->
            <div id="divMotivoUrgencia" class="md:col-span-2" style="display: <?php echo (($registro['urgente'] ?? false)) ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-semibold text-red-700 mb-2">
                    Motivo da Urgência <span class="text-red-500">*</span>
                </label>
                <textarea name="motivo_urgencia" 
                          id="motivo_urgencia"
                          rows="3"
                          placeholder="Descreva o motivo da urgência..."
                          class="w-full px-3 py-2 border-2 border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 bg-red-50"><?php echo htmlspecialchars($registro['motivo_urgencia'] ?? $_POST['motivo_urgencia'] ?? ''); ?></textarea>
            </div>
            
            <!-- Agendado -->
            <div class="md:col-span-2">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" 
                           name="agendado" 
                           id="agendado"
                           value="1"
                           <?php echo (($registro['agendado'] ?? $_POST['agendado'] ?? false)) ? 'checked' : ''; ?>
                           onchange="toggleDataAgendamento()"
                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="text-sm font-semibold text-gray-700">Marcar como agendado</span>
                </label>
            </div>

            <!-- Data Agendamento -->
            <div id="divDataAgendamento" style="display: <?php echo (($registro['agendado'] ?? false)) ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-1 text-blue-600"></i>Data do Agendamento <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       name="data_agendamento"
                       id="data_agendamento"
                       value="<?php echo $registro['data_agendamento'] ?? $_POST['data_agendamento'] ?? ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Horário Agendamento -->
            <div id="divHorarioAgendamento" style="display: <?php echo (($registro['agendado'] ?? false)) ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-clock mr-1 text-blue-600"></i>Horário do Agendamento <span class="text-red-500">*</span>
                </label>
                <input type="time" 
                       name="horario_agendamento"
                       id="horario_agendamento"
                       value="<?php echo $registro['horario_agendamento'] ?? $_POST['horario_agendamento'] ?? ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Informe o horário da consulta/exame
                </p>
            </div>
            
            <!-- Atendente Responsável -->
            <div id="divAtendenteAgendamento" style="display: <?php echo (($registro['agendado'] ?? false)) ? 'block' : 'none'; ?>;">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-check mr-1 text-blue-600"></i>Agendado por
                </label>
                <div class="w-full px-4 py-3 bg-blue-50 border-2 border-blue-300 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-user-circle text-2xl text-blue-600 mr-3"></i>
                        <div>
                            <p class="font-bold text-blue-900"><?php echo htmlspecialchars($usuarioLogado['nome']); ?></p>
                            <p class="text-xs text-blue-700">
                                <?php 
                                $perfil = $usuarioLogado['perfil'] ?? 'atendente';
                                echo ucfirst($perfil); 
                                ?> - 
                                <?php if ($isEdicao && isset($registro['data_hora_agendamento'])): ?>
                                    Agendado em <?php echo date('d/m/Y às H:i', strtotime($registro['data_hora_agendamento'])); ?>
                                <?php else: ?>
                                    Agendando agora
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Observação -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Observação</label>
                <textarea name="observacao" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($registro['observacao'] ?? $_POST['observacao'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Botões -->
        <div class="mt-6 flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-save mr-2"></i>Salvar
            </button>
            <a href="<?php echo BASE_PATH; ?>dashboard.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition flex items-center">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
        </div>
    </form>
</div>

<!-- 
    ====================================================================
    MODAL DE CONFIRMAÇÃO DE AGENDAMENTO - DESABILITADO
    ====================================================================
    Este modal foi desabilitado por solicitação.
    Para reabilitar, descomente o código na função verificarDataAgendamento()
    ====================================================================
-->

<!-- Modal de Confirmação de Agendamento (DESABILITADO) -->
<div id="modalConfirmacaoAgendamento" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center transition-opacity duration-200" style="display: none !important;">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all duration-200 scale-95 modal-content">
        <div class="bg-blue-600 text-white px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold flex items-center">
                <i class="fas fa-calendar-check mr-3 text-2xl"></i>
                Confirmação de Agendamento
            </h3>
        </div>
        
        <div class="p-6">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-phone-alt text-blue-600 text-3xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-lg text-gray-800 font-semibold mb-2">
                        ✅ Data de agendamento definida!
                    </p>
                    <p class="text-lg text-blue-700 font-semibold mb-3">
                        O paciente já foi comunicado?
                    </p>
                    <p class="text-sm text-gray-600 mb-2">
                        Gere uma mensagem profissional para enviar ao paciente com todos os detalhes:
                    </p>
                    <ul class="text-sm text-gray-700 mt-2 space-y-1">
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Data e horário do agendamento</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Local do atendimento</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Documentos necessários</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i>Informações importantes</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-end space-x-3">
            <button type="button" 
                    onclick="cancelarAgendamento()" 
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded-lg transition font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Voltar e corrigir data
            </button>
            <button type="button" 
                    onclick="gerarMensagemWhatsApp()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition font-semibold">
                <i class="fab fa-whatsapp mr-2"></i>Gerar mensagem WhatsApp
            </button>
        </div>
    </div>
</div>

<!-- Modal de Mensagem WhatsApp -->
<div id="modalWhatsApp" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 animate-fade-in">
        <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold flex items-center">
                <i class="fab fa-whatsapp mr-3 text-2xl"></i>
                Mensagem para o Paciente
            </h3>
        </div>
        
        <div class="p-6">
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Copie a mensagem abaixo e envie para o paciente via WhatsApp:
                </p>
                
                <div class="relative">
                    <textarea id="mensagemWhatsApp" 
                              rows="12"
                              class="w-full px-4 py-3 border-2 border-green-300 rounded-lg bg-green-50 text-gray-800 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="A mensagem será gerada automaticamente..."></textarea>
                    
                    <button type="button"
                            onclick="copiarMensagem()"
                            class="absolute top-2 right-2 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm transition">
                        <i class="fas fa-copy mr-1"></i>Copiar
                    </button>
                </div>
                
                <div id="mensagemCopiada" class="hidden mt-2 text-green-600 text-sm font-semibold">
                    <i class="fas fa-check-circle mr-1"></i>Mensagem copiada! Cole no WhatsApp do paciente.
                </div>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded text-sm text-blue-800">
                <i class="fas fa-lightbulb mr-2"></i>
                <strong>Dica:</strong> Você pode editar a mensagem antes de enviar para personalizar ainda mais.
            </div>
        </div>
        
        <div class="bg-gray-50 px-6 py-4 rounded-b-lg flex justify-between">
            <button type="button" 
                    onclick="voltarParaConfirmacao()" 
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded-lg transition font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </button>
            <div class="flex space-x-3">
                <button type="button" 
                        onclick="abrirWhatsAppWeb()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition font-semibold">
                    <i class="fab fa-whatsapp mr-2"></i>Abrir WhatsApp Web
                </button>
                <button type="button" 
                        onclick="finalizarAgendamento()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition font-semibold">
                    <i class="fas fa-check mr-2"></i>Concluir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.animate-fade-in {
    animation: fade-in 0.2s ease-out;
}
</style>

<script>
let checkboxAgendadoAnterior = false;
let dataAgendamentoPreenchida = false;

function toggleDataAgendamento() {
    const checkbox = document.getElementById('agendado');
    const divData = document.getElementById('divDataAgendamento');
    const divHorario = document.getElementById('divHorarioAgendamento');
    const divAtendente = document.getElementById('divAtendenteAgendamento');
    const inputData = document.getElementById('data_agendamento');
    const inputHorario = document.getElementById('horario_agendamento');
    
    // Atualiza estado anterior
    checkboxAgendadoAnterior = checkbox.checked;
    
    if (checkbox.checked) {
        // Apenas mostra os campos, não abre o modal ainda
        divData.style.display = 'block';
        divHorario.style.display = 'block';
        divAtendente.style.display = 'block';
        inputData.required = true;
        inputHorario.required = true;
    } else {
        divData.style.display = 'none';
        divHorario.style.display = 'none';
        divAtendente.style.display = 'none';
        inputData.required = false;
        inputHorario.required = false;
        inputData.value = '';
        inputHorario.value = '';
        dataAgendamentoPreenchida = false;
    }
    
    // Verificar se precisa mostrar campos de guia
    toggleCamposGuia();
}

// Variável para controlar o timeout
let timeoutModalAgendamento = null;

function verificarDataAgendamento() {
    const dataInput = document.getElementById('data_agendamento');
    const horarioInput = document.getElementById('horario_agendamento');
    const checkbox = document.getElementById('agendado');
    
    // MODAL DESABILITADO - Comentado por solicitação do usuário
    // O modal de confirmação de agendamento não será mais exibido
    
    /* CÓDIGO DO MODAL COMENTADO
    // Verifica se TANTO data QUANTO horário foram preenchidos
    if (dataInput.value && horarioInput.value && checkbox.checked && !dataAgendamentoPreenchida) {
        console.log('📅 Data de agendamento preenchida:', dataInput.value);
        console.log('🕐 Horário de agendamento preenchido:', horarioInput.value);
        dataAgendamentoPreenchida = true;
        
        // Mostra modal de confirmação com animação suave
        const modal = document.getElementById('modalConfirmacaoAgendamento');
        const modalContent = modal.querySelector('.modal-content');
        
        modal.classList.remove('hidden');
        
        // Adiciona animação após um pequeno delay para garantir transição CSS
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
    }
    */
    
    // Apenas registra no console que o agendamento foi preenchido
    if (dataInput.value && horarioInput.value && checkbox.checked) {
        console.log('✅ Agendamento preenchido (modal desabilitado)');
    }
}

function verificarDataAgendamentoComDelay() {
    // Limpa timeout anterior se existir
    if (timeoutModalAgendamento) {
        clearTimeout(timeoutModalAgendamento);
    }
    
    // Aguarda 800ms antes de verificar e mostrar o modal
    timeoutModalAgendamento = setTimeout(() => {
        verificarDataAgendamento();
    }, 800);
}

function gerarMensagemWhatsApp() {
    console.log('🚀 Função gerarMensagemWhatsApp() chamada!');
    
    // Pega os dados do formulário usando querySelector
    const nomePaciente = document.querySelector('input[name="nome_paciente"]')?.value || '';
    const dataAgendamento = document.getElementById('data_agendamento')?.value || '';
    const horarioAgendamento = document.getElementById('horario_agendamento')?.value || '';
    const tipoAtendimento = document.getElementById('tipo_atendimento')?.value || '';
    const medicoSelect = document.querySelector('select[name="medico_id"]');
    const medicoNome = medicoSelect?.options[medicoSelect.selectedIndex]?.text || '[Médico]';
    const especialidadeSelect = document.querySelector('select[name="especialidade_id"]');
    const especialidadeNome = especialidadeSelect?.options[especialidadeSelect.selectedIndex]?.text || '[Especialidade]';
    const observacao = document.querySelector('textarea[name="observacao"]')?.value || '';
    
    console.log('📋 Dados capturados:', {
        nomePaciente, 
        dataAgendamento,
        horarioAgendamento,
        tipoAtendimento, 
        medicoNome, 
        especialidadeNome
    });
    
    // Validação básica
    if (!nomePaciente) {
        alert('⚠️ Nome do paciente não informado!');
        return;
    }
    
    // Formata a data
    let dataFormatada = '';
    if (dataAgendamento) {
        const partes = dataAgendamento.split('-');
        if (partes.length === 3) {
            dataFormatada = `${partes[2]}/${partes[1]}/${partes[0]}`;
        }
    }
    
    // Formata o horário
    let horarioFormatado = '';
    if (horarioAgendamento) {
        horarioFormatado = horarioAgendamento;
    }
    
    console.log('📅 Data formatada:', dataFormatada);
    console.log('🕐 Horário formatado:', horarioFormatado);
    
    // Monta a mensagem
    let mensagem = `🏥 *CONFIRMAÇÃO DE AGENDAMENTO* \n\n`;
    mensagem += `🏥 *HOSPITAL SANTO EXPEDITO*\n\n`;
    mensagem += `Olá, *${nomePaciente}*!\n\n`;
    mensagem += `Seu atendimento foi agendado com sucesso! 📅\n\n`;
    mensagem += `📋 *DETALHES DO AGENDAMENTO:*\n`;
    mensagem += `━━━━━━━━━━━━━━━━━━━━━━\n`;
    mensagem += `📅 *Data:* ${dataFormatada || '[Preencher data]'}\n`;
    mensagem += `🕐 *Horário:* ${horarioFormatado || '[Preencher horário]'}\n`;
    mensagem += `🩺 *Tipo:* ${tipoAtendimento || '[Tipo de atendimento]'}\n`;
    mensagem += `👨‍⚕️ *Profissional:* ${medicoNome || '[Médico]'}\n`;
    mensagem += `🏥 *Especialidade:* ${especialidadeNome || '[Especialidade]'}\n`;
    mensagem += `📍 *Local:* Rua Carvalho de Mendonça, 335\n`;
    mensagem += `━━━━━━━━━━━━━━━━━━━━━━\n\n`;
    
    mensagem += `📌 *IMPORTANTE - LEMBRE-SE DE TRAZER:*\n`;
    mensagem += `✅ Documento oficial com foto (RG ou CNH)\n`;
    mensagem += `✅ Cartão do convênio (se houver)\n`;
    mensagem += `✅ Pedido médico ou guia autorizada\n`;
    mensagem += `✅ Exames anteriores (se houver)\n\n`;
    
    if (observacao) {
        mensagem += `💬 *OBSERVAÇÕES:*\n`;
        mensagem += `${observacao}\n\n`;
    }
    
    mensagem += `⏰ *ATENÇÃO:*\n`;
    mensagem += `• Chegue com 15 minutos de antecedência\n`;
    mensagem += `• Em caso de imprevistos, entre em contato o quanto antes\n\n`;
    
    
    mensagem += `📞 *Dúvidas?*\n`;
    mensagem += `Entre em contato conosco:\n`;
    mensagem += `Telefone: (13) 3226-5000\n`;
    mensagem += `WhatsApp: (13) 99622-9894\n\n`;
    
    mensagem += `Aguardamos você! 🙏\n`;
    mensagem += `_Atendimento agendado em ${new Date().toLocaleDateString('pt-BR')}_`;
    
    // Coloca a mensagem no textarea
    const textareaMensagem = document.getElementById('mensagemWhatsApp');
    if (textareaMensagem) {
        textareaMensagem.value = mensagem;
        console.log('✅ Mensagem inserida no textarea');
    } else {
        console.error('❌ Textarea mensagemWhatsApp não encontrado!');
        alert('Erro: Campo de mensagem não encontrado!');
        return;
    }
    
    // Fecha modal de confirmação e abre modal do WhatsApp
    const modalConfirmacao = document.getElementById('modalConfirmacaoAgendamento');
    const modalWhatsApp = document.getElementById('modalWhatsApp');
    
    if (modalConfirmacao && modalWhatsApp) {
        modalConfirmacao.classList.add('hidden');
        modalWhatsApp.classList.remove('hidden');
        console.log('✅ Modal do WhatsApp aberto!');
    } else {
        console.error('❌ Modais não encontrados!', {modalConfirmacao, modalWhatsApp});
        alert('Erro: Modais não encontrados!');
    }
}

function copiarMensagem() {
    const textarea = document.getElementById('mensagemWhatsApp');
    textarea.select();
    textarea.setSelectionRange(0, 99999); // Para mobile
    
    try {
        document.execCommand('copy');
        document.getElementById('mensagemCopiada').classList.remove('hidden');
        
        // Esconde a mensagem após 3 segundos
        setTimeout(() => {
            document.getElementById('mensagemCopiada').classList.add('hidden');
        }, 3000);
    } catch (err) {
        alert('Erro ao copiar. Por favor, copie manualmente.');
    }
}

function abrirWhatsAppWeb() {
    const telefone = document.querySelector('input[name="telefone1"]')?.value || '';
    const mensagem = document.getElementById('mensagemWhatsApp').value;
    
    // Remove caracteres não numéricos do telefone
    const telefoneNumeros = telefone.replace(/\D/g, '');
    
    // Registra a mensagem no histórico
    registrarMensagemHistorico(telefone, mensagem);
    
    // Codifica a mensagem para URL
    const mensagemCodificada = encodeURIComponent(mensagem);
    
    // Abre WhatsApp Web com a mensagem pré-preenchida
    if (telefoneNumeros) {
        window.open(`https://web.whatsapp.com/send?phone=55${telefoneNumeros}&text=${mensagemCodificada}`, '_blank');
    } else {
        alert('Telefone não informado. Abrindo WhatsApp Web sem número.');
        window.open(`https://web.whatsapp.com`, '_blank');
    }
}

// Função para registrar mensagem no histórico
function registrarMensagemHistorico(telefone, mensagem) {
    // Pega o ID da fila de espera
    const filaEsperaId = document.querySelector('input[name="id"]')?.value;
    
    // Se tem ID (editando registro existente), registra imediatamente
    if (filaEsperaId) {
        const dados = {
            fila_espera_id: filaEsperaId,
            paciente_id: null,
            telefone: telefone,
            mensagem: mensagem,
            tipo_mensagem: 'confirmacao_agendamento'
        };
        
        fetch('<?php echo BASE_PATH; ?>registrar_mensagem_whatsapp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                console.log('✅ Mensagem registrada no histórico:', data);
            } else {
                console.error('❌ Erro ao registrar:', data.erro);
            }
        })
        .catch(error => console.error('❌ Erro:', error));
    } else {
        // Se não tem ID (cadastro novo), salva na sessão para registrar após salvar
        console.log('⏳ Cadastro novo. Mensagem será registrada ao salvar o formulário.');
        
        fetch('<?php echo BASE_PATH; ?>salvar_mensagem_sessao.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ telefone: telefone, mensagem: mensagem })
        })
        .then(response => response.json())
        .then(data => {
            if (data.sucesso) {
                console.log('✅ Mensagem salva. Lembre-se de SALVAR o cadastro!');
            }
        })
        .catch(error => console.error('❌ Erro:', error));
    }
}

function voltarParaConfirmacao() {
    document.getElementById('modalWhatsApp').classList.add('hidden');
    document.getElementById('modalConfirmacaoAgendamento').classList.remove('hidden');
}

function finalizarAgendamento() {
    // Fecha o modal do WhatsApp
    document.getElementById('modalWhatsApp').classList.add('hidden');
    
    // Atualiza o estado e mostra os campos de agendamento
    checkboxAgendadoAnterior = true;
    const checkbox = document.getElementById('agendado');
    const divData = document.getElementById('divDataAgendamento');
    const divAtendente = document.getElementById('divAtendenteAgendamento');
    const input = document.getElementById('data_agendamento');
    
    divData.style.display = 'block';
    divAtendente.style.display = 'block';
    input.required = true;
    
    // Verificar se precisa mostrar campos de guia
    toggleCamposGuia();
}

function cancelarAgendamento() {
    // Anima o fechamento do modal
    const modal = document.getElementById('modalConfirmacaoAgendamento');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.classList.remove('opacity-100');
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    
    // Aguarda a animação terminar antes de esconder
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
    
    // Limpa a data
    const dataInput = document.getElementById('data_agendamento');
    dataInput.value = '';
    
    // Reseta a flag
    dataAgendamentoPreenchida = false;
    
    console.log('❌ Agendamento cancelado - pode preencher data novamente');
}

// Inicializa o estado ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('agendado');
    const dataInput = document.getElementById('data_agendamento');
    
    checkboxAgendadoAnterior = checkbox.checked;
    
    // Se já tem data preenchida (modo edição), marca como preenchida
    if (dataInput.value) {
        dataAgendamentoPreenchida = true;
        console.log('📝 Modo edição - data já preenchida:', dataInput.value);
    }
});

function toggleMotivoUrgencia() {
    const checkbox = document.getElementById('urgente');
    const div = document.getElementById('divMotivoUrgencia');
    const textarea = document.getElementById('motivo_urgencia');
    
    if (checkbox.checked) {
        div.style.display = 'block';
        textarea.required = true;
    } else {
        div.style.display = 'none';
        textarea.required = false;
        textarea.value = '';
    }
}

function toggleCamposGuia() {
    const select = document.getElementById('tipo_atendimento');
    const div = document.getElementById('divAutorizacaoGuia');
    const tiposQueRequeremGuia = ['Exame', 'Cirurgia', 'Consulta + Exame'];
    
    if (tiposQueRequeremGuia.includes(select.value)) {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
        // Limpar campos quando não for necessário
        document.getElementById('guia_autorizada').value = '';
        document.getElementById('data_autorizacao_guia').value = '';
        document.getElementById('divDataAutorizacaoGuia').style.display = 'none';
    }
}

function toggleDataAutorizacaoGuia() {
    const select = document.getElementById('guia_autorizada');
    const div = document.getElementById('divDataAutorizacaoGuia');
    
    if (select.value == '1') {
        div.style.display = 'block';
    } else {
        div.style.display = 'none';
        document.getElementById('data_autorizacao_guia').value = '';
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
