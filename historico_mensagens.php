<?php
/**
 * Página de Histórico de Mensagens WhatsApp
 * Lista todas as mensagens enviadas pelo sistema
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/HistoricoMensagem.php';
require_once __DIR__ . '/models/Usuario.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$historicoModel = new HistoricoMensagem();
$usuarioModel = new Usuario();

// Filtros
$filtros = [];

if (!empty($_GET['paciente'])) {
    $filtros['busca'] = $_GET['paciente'];
}

if (!empty($_GET['usuario_id'])) {
    $filtros['usuario_id'] = $_GET['usuario_id'];
}

if (!empty($_GET['tipo_mensagem'])) {
    $filtros['tipo_mensagem'] = $_GET['tipo_mensagem'];
}

if (!empty($_GET['data_inicio'])) {
    $filtros['data_inicio'] = $_GET['data_inicio'];
}

if (!empty($_GET['data_fim'])) {
    $filtros['data_fim'] = $_GET['data_fim'];
}

// Paginação
$porPagina = 20;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $porPagina;

$filtros['limite'] = $porPagina;
$filtros['offset'] = $offset;

// Busca dados
$mensagens = $historicoModel->listar($filtros);
$total = $historicoModel->contar($filtros);
$totalPaginas = ceil($total / $porPagina);

// Estatísticas
$estatisticas = $historicoModel->estatisticas($filtros);

// Usuários para filtro
$usuarios = $usuarioModel->listar(['ativo' => 1]);

$pageTitle = 'Histórico de Mensagens WhatsApp';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- Título da página -->
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
        <i class="fab fa-whatsapp mr-3 text-green-600"></i>Histórico de Mensagens WhatsApp
    </h1>
    <p class="text-gray-600">Visualize todas as mensagens enviadas para pacientes</p>
</div>

<!-- Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-semibold">Total de Envios</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($estatisticas['total_envios'], 0, ',', '.'); ?></p>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-paper-plane text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-semibold">Pacientes Únicos</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($estatisticas['total_pacientes'], 0, ',', '.'); ?></p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-users text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-semibold">Usuários Ativos</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo number_format($estatisticas['total_usuarios'], 0, ',', '.'); ?></p>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-user-friends text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-semibold">Última Mensagem</p>
                <p class="text-sm font-bold text-gray-800">
                    <?php echo $estatisticas['ultima_mensagem'] ? date('d/m/Y', strtotime($estatisticas['ultima_mensagem'])) : '-'; ?>
                </p>
            </div>
            <div class="bg-orange-100 p-3 rounded-full">
                <i class="fas fa-calendar-day text-orange-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="bg-white rounded-lg shadow-md mb-6">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition" onclick="toggleFiltros()">
        <div class="flex items-center">
            <i class="fas fa-filter text-blue-600 text-xl mr-3"></i>
            <h2 class="text-lg font-bold text-gray-800">Filtros de Busca</h2>
        </div>
        <button type="button" class="text-gray-600 hover:text-gray-800 transition">
            <i id="iconeFiltros" class="fas fa-chevron-up text-xl"></i>
        </button>
    </div>

    <div id="areaFiltros" class="transition-all duration-300">
        <form method="GET" action="" class="p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Filtro Paciente -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-injured mr-1"></i>Nome do Paciente
                    </label>
                    <input type="text" 
                           name="paciente" 
                           value="<?php echo htmlspecialchars($_GET['paciente'] ?? ''); ?>"
                           placeholder="Digite o nome..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filtro Usuário -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-1"></i>Enviado por
                    </label>
                    <select name="usuario_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos os usuários</option>
                        <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?php echo $usuario['id']; ?>" <?php echo (isset($_GET['usuario_id']) && $_GET['usuario_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($usuario['nome']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro Data Início -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Data Início
                    </label>
                    <input type="date" 
                           name="data_inicio" 
                           value="<?php echo htmlspecialchars($_GET['data_inicio'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filtro Data Fim -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-1"></i>Data Fim
                    </label>
                    <input type="date" 
                           name="data_fim" 
                           value="<?php echo htmlspecialchars($_GET['data_fim'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Botões -->
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition flex items-center">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
                <a href="<?php echo BASE_PATH; ?>historico_mensagens.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition flex items-center">
                    <i class="fas fa-eraser mr-2"></i>Limpar Filtros
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Histórico -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full table-hover">
            <thead class="bg-gray-100 border-b-2 border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Data/Hora</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Paciente</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Telefone</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Agendamento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Enviado por</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($mensagens)): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3 block"></i>
                        <p class="font-semibold">Nenhuma mensagem encontrada</p>
                        <p class="text-sm">Tente ajustar os filtros de busca</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($mensagens as $msg): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            <i class="far fa-clock mr-1 text-blue-600"></i>
                            <?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($msg['paciente_nome']); ?></p>
                            <p class="text-xs text-gray-500">CPF: <?php echo htmlspecialchars($msg['paciente_cpf']); ?></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <i class="fab fa-whatsapp text-green-600 mr-1"></i>
                            <?php echo htmlspecialchars($msg['telefone']); ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($msg['data_agendamento']): ?>
                            <p class="text-sm text-gray-800">
                                <i class="fas fa-calendar-check text-blue-600 mr-1"></i>
                                <?php echo date('d/m/Y', strtotime($msg['data_agendamento'])); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo htmlspecialchars($msg['medico_nome'] ?? 'Médico não informado'); ?>
                            </p>
                            <?php else: ?>
                            <span class="text-xs text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <i class="fas fa-user text-purple-600 mr-1"></i>
                            <?php echo htmlspecialchars($msg['usuario_nome']); ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex gap-2 justify-center">
                                <button onclick="verMensagem(<?php echo $msg['id']; ?>)" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs transition"
                                        title="Ver detalhes da mensagem">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="excluirMensagem(<?php echo $msg['id']; ?>)" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition"
                                        title="Excluir mensagem">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPaginas > 1): ?>
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Mostrando <span class="font-semibold"><?php echo ($offset + 1); ?></span> até 
                <span class="font-semibold"><?php echo min($offset + $porPagina, $total); ?></span> de 
                <span class="font-semibold"><?php echo $total; ?></span> registros
            </div>
            <div class="flex space-x-2">
                <?php if ($paginaAtual > 1): ?>
                <a href="?pagina=<?php echo ($paginaAtual - 1) . '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])); ?>" 
                   class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition">
                    <i class="fas fa-chevron-left"></i> Anterior
                </a>
                <?php endif; ?>

                <?php for ($i = max(1, $paginaAtual - 2); $i <= min($totalPaginas, $paginaAtual + 2); $i++): ?>
                <a href="?pagina=<?php echo $i . '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])); ?>" 
                   class="px-3 py-1 <?php echo $i == $paginaAtual ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?> rounded text-sm transition">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>

                <?php if ($paginaAtual < $totalPaginas): ?>
                <a href="?pagina=<?php echo ($paginaAtual + 1) . '&' . http_build_query(array_diff_key($_GET, ['pagina' => ''])); ?>" 
                   class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition">
                    Próxima <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal de Visualização de Mensagem -->
<div id="modalMensagem" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full my-8 flex flex-col" style="max-height: calc(100vh - 4rem);">
        <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg flex items-center justify-between flex-shrink-0">
            <h3 class="text-xl font-bold flex items-center">
                <i class="fab fa-whatsapp mr-3 text-2xl"></i>
                Detalhes da Mensagem
            </h3>
            <button onclick="fecharModalMensagem()" class="text-white hover:text-gray-200 transition">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <div id="conteudoMensagem" class="p-6 overflow-y-auto flex-grow" style="scrollbar-width: thin; scrollbar-color: #10b981 #f3f4f6;">
            <!-- Conteúdo carregado via AJAX -->
            <div class="flex justify-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilo customizado para scrollbar do modal */
#conteudoMensagem::-webkit-scrollbar,
.mensagem-scroll::-webkit-scrollbar {
    width: 8px;
}

#conteudoMensagem::-webkit-scrollbar-track,
.mensagem-scroll::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 4px;
}

#conteudoMensagem::-webkit-scrollbar-thumb,
.mensagem-scroll::-webkit-scrollbar-thumb {
    background: #10b981;
    border-radius: 4px;
}

#conteudoMensagem::-webkit-scrollbar-thumb:hover,
.mensagem-scroll::-webkit-scrollbar-thumb:hover {
    background: #059669;
}

/* Firefox */
#conteudoMensagem,
.mensagem-scroll {
    scrollbar-width: thin;
    scrollbar-color: #10b981 #f3f4f6;
}
</style>

<script>
function toggleFiltros() {
    const area = document.getElementById('areaFiltros');
    const icone = document.getElementById('iconeFiltros');
    
    if (area.style.maxHeight === '0px' || area.style.maxHeight === '') {
        area.style.maxHeight = area.scrollHeight + 'px';
        icone.classList.remove('fa-chevron-down');
        icone.classList.add('fa-chevron-up');
    } else {
        area.style.maxHeight = '0px';
        icone.classList.remove('fa-chevron-up');
        icone.classList.add('fa-chevron-down');
    }
}

function verMensagem(id) {
    // Abre modal
    document.getElementById('modalMensagem').classList.remove('hidden');
    
    // Busca detalhes via AJAX
    fetch('<?php echo BASE_PATH; ?>detalhes_mensagem.php?id=' + id)
        .then(response => response.text())
        .then(html => {
            document.getElementById('conteudoMensagem').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('conteudoMensagem').innerHTML = `
                <div class="text-center text-red-600 py-8">
                    <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
                    <p>Erro ao carregar mensagem</p>
                </div>
            `;
        });
}

function fecharModalMensagem() {
    document.getElementById('modalMensagem').classList.add('hidden');
}

function excluirMensagem(id) {
    if (!confirm('Deseja realmente excluir esta mensagem do histórico?\n\nEsta ação não pode ser desfeita.')) {
        return;
    }
    
    // Mostra loading no botão
    const btn = event.target.closest('button');
    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Envia requisição de exclusão
    fetch('<?php echo BASE_PATH; ?>excluir_mensagem.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            // Remove a linha da tabela com animação
            const linha = btn.closest('tr');
            linha.style.backgroundColor = '#fee';
            setTimeout(() => {
                linha.style.transition = 'opacity 0.5s';
                linha.style.opacity = '0';
                setTimeout(() => {
                    location.reload();
                }, 500);
            }, 300);
        } else {
            alert('Erro ao excluir mensagem: ' + (data.erro || 'Erro desconhecido'));
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao excluir mensagem');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
