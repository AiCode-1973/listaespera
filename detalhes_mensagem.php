<?php
/**
 * Detalhes de uma Mensagem WhatsApp
 * Exibido no modal via AJAX
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/HistoricoMensagem.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo '<div class="text-center text-red-600 py-8">
            <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
            <p>ID da mensagem não informado</p>
          </div>';
    exit();
}

$historicoModel = new HistoricoMensagem();
$mensagem = $historicoModel->buscarPorId($id);

if (!$mensagem) {
    echo '<div class="text-center text-red-600 py-8">
            <i class="fas fa-exclamation-circle text-4xl mb-3"></i>
            <p>Mensagem não encontrada</p>
          </div>';
    exit();
}
?>

<!-- Informações do Envio -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-xs text-blue-600 font-semibold mb-1">PACIENTE</p>
        <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($mensagem['paciente_nome']); ?></p>
        <p class="text-xs text-gray-600">CPF: <?php echo htmlspecialchars($mensagem['paciente_cpf']); ?></p>
    </div>

    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
        <p class="text-xs text-green-600 font-semibold mb-1">TELEFONE</p>
        <p class="text-sm font-bold text-gray-800">
            <i class="fab fa-whatsapp mr-1"></i>
            <?php echo htmlspecialchars($mensagem['telefone']); ?>
        </p>
    </div>

    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
        <p class="text-xs text-purple-600 font-semibold mb-1">ENVIADO POR</p>
        <p class="text-sm font-bold text-gray-800"><?php echo htmlspecialchars($mensagem['usuario_nome']); ?></p>
        <p class="text-xs text-gray-600">
            <i class="far fa-clock mr-1"></i>
            <?php echo date('d/m/Y \à\s H:i', strtotime($mensagem['data_envio'])); ?>
        </p>
    </div>

    <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
        <p class="text-xs text-orange-600 font-semibold mb-1">AGENDAMENTO</p>
        <?php if ($mensagem['data_agendamento']): ?>
        <p class="text-sm font-bold text-gray-800">
            <i class="fas fa-calendar-check mr-1"></i>
            <?php echo date('d/m/Y', strtotime($mensagem['data_agendamento'])); ?>
        </p>
        <p class="text-xs text-gray-600"><?php echo htmlspecialchars($mensagem['tipo_atendimento'] ?? '-'); ?></p>
        <?php else: ?>
        <p class="text-sm text-gray-500">Não agendado</p>
        <?php endif; ?>
    </div>
</div>

<!-- Detalhes do Atendimento -->
<?php if ($mensagem['medico_nome'] || $mensagem['especialidade_nome']): ?>
<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
    <p class="text-xs text-gray-600 font-semibold mb-2">DETALHES DO ATENDIMENTO</p>
    <div class="grid grid-cols-2 gap-3">
        <?php if ($mensagem['medico_nome']): ?>
        <div>
            <p class="text-xs text-gray-500">Médico:</p>
            <p class="text-sm font-semibold text-gray-800">
                <i class="fas fa-user-md mr-1 text-blue-600"></i>
                <?php echo htmlspecialchars($mensagem['medico_nome']); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($mensagem['especialidade_nome']): ?>
        <div>
            <p class="text-xs text-gray-500">Especialidade:</p>
            <p class="text-sm font-semibold text-gray-800">
                <i class="fas fa-stethoscope mr-1 text-purple-600"></i>
                <?php echo htmlspecialchars($mensagem['especialidade_nome']); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Mensagem Enviada -->
<div class="bg-green-50 border-2 border-green-300 rounded-lg p-4">
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm text-green-700 font-semibold">
            <i class="fab fa-whatsapp mr-2"></i>MENSAGEM ENVIADA
        </p>
        <button onclick="copiarMensagemModal()" 
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition">
            <i class="fas fa-copy mr-1"></i>Copiar
        </button>
    </div>
    
    <div class="bg-white rounded-lg p-4 border border-green-200 max-h-64 overflow-y-auto mensagem-scroll">
        <pre id="mensagemTexto" class="whitespace-pre-wrap font-sans text-sm text-gray-800"><?php echo htmlspecialchars($mensagem['mensagem']); ?></pre>
    </div>
    
    <div id="mensagemCopiada" class="hidden mt-2 text-green-600 text-sm font-semibold">
        <i class="fas fa-check-circle mr-1"></i>Mensagem copiada para área de transferência!
    </div>
</div>

<!-- Botões de Ação -->
<div class="mt-6 flex flex-wrap gap-3 justify-end">
    <button onclick="fecharModalMensagem()" 
            class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg transition">
        <i class="fas fa-times mr-2"></i>Fechar
    </button>
    <a href="https://web.whatsapp.com/send?phone=55<?php echo preg_replace('/\D/', '', $mensagem['telefone']); ?>" 
       target="_blank"
       class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg transition inline-block">
        <i class="fab fa-whatsapp mr-2"></i>Abrir WhatsApp
    </a>
</div>

<script>
function copiarMensagemModal() {
    const texto = document.getElementById('mensagemTexto').innerText;
    
    navigator.clipboard.writeText(texto).then(() => {
        const aviso = document.getElementById('mensagemCopiada');
        aviso.classList.remove('hidden');
        
        setTimeout(() => {
            aviso.classList.add('hidden');
        }, 3000);
    }).catch(err => {
        alert('Erro ao copiar mensagem');
        console.error('Erro:', err);
    });
}
</script>
