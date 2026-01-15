<?php
/**
 * Agenda Visual - Calendário de Agendamentos
 * Acesso: Administradores e Recepção
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$usuarioLogado = $auth->getUsuarioLogado();

// Verifica se é administrador, recepção ou atendente
if (!in_array($usuarioLogado['perfil'], ['administrador', 'recepcao', 'atendente'])) {
    $_SESSION['mensagem_erro'] = 'Acesso negado. Apenas administradores e atendentes podem acessar a agenda.';
    header('Location: ' . BASE_PATH . 'dashboard.php');
    exit;
}

$pageTitle = 'Agenda Visual';
include __DIR__ . '/includes/header.php';
?>

<!-- Cabeçalho da Página -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-calendar-alt text-blue-600 mr-3"></i>
                Agenda Visual de Agendamentos
            </h1>
            <p class="text-gray-600 mt-2">Visualize todos os agendamentos em um calendário interativo</p>
        </div>
        <div class="text-right">
            <a href="<?php echo BASE_PATH; ?>dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar ao Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Legenda -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <h3 class="font-semibold text-gray-700 mb-3">
        <i class="fas fa-info-circle mr-2"></i>Legenda
    </h3>
    <div class="flex flex-wrap gap-4">
        <div class="flex items-center">
            <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700">Consulta</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-blue-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700">Exame</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-purple-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700">Consulta + Exame</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-yellow-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700">Retorno</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-red-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700">Cirurgia</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-orange-500 rounded mr-2"></div>
            <span class="text-sm text-gray-700">Procedimento</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-red-700 border-2 border-red-900 rounded mr-2"></div>
            <span class="text-sm text-gray-700 font-semibold">Urgente</span>
        </div>
    </div>
</div>

<!-- Calendário -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div id="calendar"></div>
</div>

<!-- Modal de Detalhes do Evento -->
<div id="eventoModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" style="display: none;" onclick="if(event.target.id === 'eventoModal') fecharModalAgenda();">
    <div id="modalConteiner" class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation();">
        <div class="bg-blue-600 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="text-xl font-bold">
                <i class="fas fa-info-circle mr-2"></i>Detalhes do Agendamento
            </h3>
            <button type="button" id="btnFecharModalX" onclick="fecharModalAgenda(); return false;" class="text-white hover:text-gray-200 transition" style="cursor: pointer;">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="eventoConteudo" class="p-6">
            <!-- Conteúdo será preenchido via JavaScript -->
        </div>
        <div class="bg-gray-50 p-4 rounded-b-lg flex justify-between items-center">
            <div>
                <button type="button" id="btnFecharModal" onclick="fecharModalAgenda(); return false;" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition" style="cursor: pointer;">
                    <i class="fas fa-times mr-2"></i>Fechar
                </button>
            </div>
            <div class="flex gap-3">
                <a id="btnExcluirEvento" href="#" onclick="return confirmarExclusaoEvento();" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition inline-flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i>Excluir
                </a>
                <a id="btnEditarEvento" href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        buttonText: {
            today: 'Hoje',
            month: 'Mês',
            week: 'Semana',
            day: 'Dia',
            list: 'Lista'
        },
        height: 'auto',
        events: '<?php echo BASE_PATH; ?>api/agenda_eventos.php',
        eventClick: function(info) {
            mostrarDetalhes(info.event);
        },
        eventDidMount: function(info) {
            // Adiciona tooltip
            info.el.title = info.event.extendedProps.tooltip || info.event.title;
        }
    });
    
    calendar.render();
    
    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            const modal = document.getElementById('eventoModal');
            if (modal && modal.style.display === 'flex') {
                fecharModalAgenda();
            }
        }
    });
    
    console.log('✅ Sistema de modal da agenda inicializado');
});

function mostrarDetalhes(event) {
    console.log('📋 Abrindo detalhes do evento:', event.title);
    const modal = document.getElementById('eventoModal');
    const conteudo = document.getElementById('eventoConteudo');
    const btnEditar = document.getElementById('btnEditarEvento');
    const btnExcluir = document.getElementById('btnExcluirEvento');
    
    if (!modal || !conteudo || !btnEditar || !btnExcluir) {
        console.error('❌ Elementos do modal não encontrados!');
        return;
    }
    
    // Salvar dados do evento para uso na exclusão
    window.eventoAtual = event;
    
    // Preparar HTML com detalhes
    let html = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-user mr-1"></i>Paciente
                    </label>
                    <p class="text-gray-900 font-medium">${event.title}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-calendar mr-1"></i>Data do Agendamento
                    </label>
                    <p class="text-gray-900">${event.extendedProps.dataFormatada || ''}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-user-md mr-1"></i>Médico
                    </label>
                    <p class="text-gray-900">${event.extendedProps.medico || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-stethoscope mr-1"></i>Especialidade
                    </label>
                    <p class="text-gray-900">${event.extendedProps.especialidade || 'N/A'}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-clipboard mr-1"></i>Tipo de Atendimento
                    </label>
                    <p class="text-gray-900">${event.extendedProps.tipoAtendimento || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-id-card mr-1"></i>Convênio
                    </label>
                    <p class="text-gray-900">${event.extendedProps.convenio || 'Particular'}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-phone mr-1"></i>Telefone
                    </label>
                    <p class="text-gray-900">${event.extendedProps.telefone || 'N/A'}</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-id-badge mr-1"></i>CPF
                    </label>
                    <p class="text-gray-900">${event.extendedProps.cpf || 'N/A'}</p>
                </div>
            </div>
            
            ${event.extendedProps.urgente ? `
                <div class="bg-red-100 border-l-4 border-red-600 p-3 rounded">
                    <p class="text-red-800 font-semibold">
                        <i class="fas fa-exclamation-triangle mr-2"></i>ATENDIMENTO URGENTE
                    </p>
                    ${event.extendedProps.motivoUrgencia ? `
                        <p class="text-sm text-red-700 mt-1">${event.extendedProps.motivoUrgencia}</p>
                    ` : ''}
                </div>
            ` : ''}
            
            ${event.extendedProps.observacoes ? `
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-comment mr-1"></i>Observações
                    </label>
                    <p class="text-gray-700 bg-gray-50 p-3 rounded">${event.extendedProps.observacoes}</p>
                </div>
            ` : ''}
        </div>
    `;
    
    conteudo.innerHTML = html;
    btnEditar.href = '<?php echo BASE_PATH; ?>fila_espera_form.php?id=' + event.extendedProps.id;
    btnExcluir.href = '<?php echo BASE_PATH; ?>fila_espera_deletar.php?id=' + event.extendedProps.id;
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    console.log('✅ Modal ABERTO');
}

// Função para confirmar exclusão de evento
function confirmarExclusaoEvento() {
    if (!window.eventoAtual) {
        alert('❌ Erro: Evento não encontrado!');
        return false;
    }
    
    const evento = window.eventoAtual;
    const paciente = evento.title || 'Sem nome';
    const medico = evento.extendedProps.medico || 'Não informado';
    const data = evento.extendedProps.dataFormatada || 'Sem data';
    const especialidade = evento.extendedProps.especialidade || 'Não informada';
    
    const mensagem = `⚠️ ATENÇÃO - EXCLUSÃO DE AGENDAMENTO!\n\n` +
                     `Deseja realmente EXCLUIR este registro?\n\n` +
                     `📋 Detalhes:\n` +
                     `• Paciente: ${paciente}\n` +
                     `• Médico: ${medico}\n` +
                     `• Especialidade: ${especialidade}\n` +
                     `• Data: ${data}\n\n` +
                     `⚠️ Esta ação NÃO pode ser desfeita!\n` +
                     `O registro será removido permanentemente do sistema.`;
    
    return confirm(mensagem);
}

// Função específica para fechar o modal da agenda (evita conflito com footer.php)
function fecharModalAgenda() {
    console.log('🔴 Fechando modal da agenda...');
    const modal = document.getElementById('eventoModal');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.add('hidden');
        console.log('✅ Modal da agenda FECHADO');
        // Limpar conteúdo
        setTimeout(() => {
            const conteudo = document.getElementById('eventoConteudo');
            if (conteudo) {
                conteudo.innerHTML = '';
            }
        }, 300);
    } else {
        console.error('❌ Modal da agenda não encontrado!');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
