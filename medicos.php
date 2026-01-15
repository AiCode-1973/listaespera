<?php
/**
 * Gerenciamento de Médicos
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/Medico.php';
require_once __DIR__ . '/models/Especialidade.php';
require_once __DIR__ . '/includes/functions.php';

// Verifica autenticação
$auth = new AuthController();
$auth->verificarAutenticacao();

$medicoModel = new Medico();
$especialidadeModel = new Especialidade();
$erros = [];

// Processar ações
$acao = $_GET['acao'] ?? '';

// Inativar
if ($acao === 'inativar' && isset($_GET['id'])) {
    $auth->verificarPermissao(['administrador']);
    
    if ($medicoModel->inativar($_GET['id'])) {
        $_SESSION['mensagem_sucesso'] = 'Médico inativado com sucesso';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao inativar médico';
    }
    header('Location: ' . BASE_PATH . 'medicos.php');
    exit();
}

// Excluir
if ($acao === 'excluir' && isset($_GET['id'])) {
    $auth->verificarPermissao(['administrador']);
    
    try {
        if ($medicoModel->deletar($_GET['id'])) {
            $_SESSION['mensagem_sucesso'] = 'Médico excluído com sucesso';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir médico';
        }
    } catch (PDOException $e) {
        // Se houver erro de chave estrangeira (médico tem pacientes vinculados)
        if ($e->getCode() == '23000') {
            $_SESSION['mensagem_erro'] = 'Não é possível excluir este médico pois existem pacientes vinculados a ele. Use a opção Inativar.';
        } else {
            $_SESSION['mensagem_erro'] = 'Erro ao excluir médico: ' . $e->getMessage();
        }
    }
    header('Location: ' . BASE_PATH . 'medicos.php');
    exit();
}

// Criar/Editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if (empty($_POST['nome'])) {
        $erros[] = 'Nome é obrigatório';
    }
    
    if (empty($_POST['especialidades']) || !is_array($_POST['especialidades'])) {
        $erros[] = 'Selecione ao menos uma especialidade';
    }
    
    // Verifica duplicidade de CRM/CPF (apenas se preenchido)
    $crmCpf = trim($_POST['crm_cpf'] ?? '');
    if (!empty($crmCpf) && $medicoModel->crmCpfExiste($crmCpf, $id)) {
        $erros[] = 'Já existe um médico com este CRM/CPF';
    }
    
    if (empty($erros)) {
        $dados = [
            'nome' => $_POST['nome'],
            'crm_cpf' => !empty($crmCpf) ? $crmCpf : null,
            'telefone' => $_POST['telefone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'observacao' => $_POST['observacao'] ?? '',
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'especialidades' => $_POST['especialidades']
        ];
        
        if ($id) {
            if ($medicoModel->atualizar($id, $dados)) {
                $_SESSION['mensagem_sucesso'] = 'Médico atualizado com sucesso';
                header('Location: ' . BASE_PATH . 'medicos.php');
                exit();
            } else {
                $erros[] = 'Erro ao atualizar médico';
            }
        } else {
            $dados['ativo'] = 1; // Novo médico sempre ativo
            if ($medicoModel->criar($dados)) {
                $_SESSION['mensagem_sucesso'] = 'Médico criado com sucesso';
                header('Location: ' . BASE_PATH . 'medicos.php');
                exit();
            } else {
                $erros[] = 'Erro ao criar médico';
            }
        }
    }
}

// Buscar médico para edição
$medicoEdicao = null;
$especialidadesMedico = [];
if ($acao === 'editar' && isset($_GET['id'])) {
    $medicoEdicao = $medicoModel->buscarPorId($_GET['id']);
    if ($medicoEdicao) {
        // Busca IDs das especialidades e converte para array de inteiros
        $especialidadesMedico = array_map('intval', $medicoModel->buscarEspecialidades($_GET['id']));
        
        // Debug temporário - REMOVER em produção
        // error_log("DEBUG - Médico ID: " . $_GET['id']);
        // error_log("DEBUG - Especialidades IDs: " . print_r($especialidadesMedico, true));
    } else {
        $_SESSION['mensagem_erro'] = 'Médico não encontrado';
        header('Location: ' . BASE_PATH . 'medicos.php');
        exit();
    }
}

// Listar médicos com paginação
$busca = $_GET['busca'] ?? '';
$filtros = ['busca' => $busca];

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registrosPorPagina = 10;
$offset = ($pagina - 1) * $registrosPorPagina;

// Busca dados
$medicos = $medicoModel->listar($filtros, $registrosPorPagina, $offset);
$totalRegistros = $medicoModel->contar($filtros);
$paginacao = paginar($totalRegistros, $registrosPorPagina, $pagina);

// Listar especialidades
$especialidades = $especialidadeModel->listar();

$pageTitle = 'Médicos';
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
        <i class="fas fa-user-md mr-3"></i>Médicos
    </h1>
    <p class="text-gray-600">Gerencie os médicos cadastrados no sistema</p>
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
<div class="bg-white rounded-lg shadow-md p-6 mb-6 <?php echo $medicoEdicao ? 'border-2 border-blue-500' : ''; ?>">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">
            <?php if ($medicoEdicao): ?>
                <i class="fas fa-edit text-blue-600 mr-2"></i>Editar Médico
            <?php else: ?>
                <i class="fas fa-plus-circle text-green-600 mr-2"></i>Novo Médico
            <?php endif; ?>
        </h2>
        <?php if ($medicoEdicao): ?>
        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
            <i class="fas fa-info-circle mr-1"></i>Modo Edição
        </span>
        <?php endif; ?>
    </div>
    
    <?php if ($medicoEdicao): ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 mb-4">
        <p class="text-sm text-blue-800">
            <i class="fas fa-lightbulb mr-2"></i>
            Você está editando: <strong><?php echo htmlspecialchars($medicoEdicao['nome']); ?></strong>
        </p>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="space-y-4">
        <?php if ($medicoEdicao): ?>
        <input type="hidden" name="id" value="<?php echo $medicoEdicao['id']; ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="nome" 
                       required
                       value="<?php echo htmlspecialchars($medicoEdicao['nome'] ?? $_POST['nome'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    CRM ou CPF
                </label>
                <input type="text" 
                       name="crm_cpf" 
                       value="<?php echo htmlspecialchars($medicoEdicao['crm_cpf'] ?? $_POST['crm_cpf'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Opcional">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Telefone</label>
                <input type="text" 
                       name="telefone"
                       onkeyup="mascaraTelefone(this)"
                       maxlength="15"
                       value="<?php echo isset($medicoEdicao['telefone']) ? formatarTelefone($medicoEdicao['telefone']) : htmlspecialchars($_POST['telefone'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
                <input type="email" 
                       name="email"
                       value="<?php echo htmlspecialchars($medicoEdicao['email'] ?? $_POST['email'] ?? ''); ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-sticky-note mr-1 text-gray-500"></i>Observações
            </label>
            <textarea name="observacao"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                      placeholder="Informações adicionais sobre o médico (horários, dias de atendimento, observações, etc.)"><?php echo htmlspecialchars($medicoEdicao['observacao'] ?? $_POST['observacao'] ?? ''); ?></textarea>
            <p class="text-xs text-gray-500 mt-1">
                <i class="fas fa-info-circle"></i> Use este campo para registrar informações importantes como horários de atendimento, dias disponíveis, observações relevantes, etc.
            </p>
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Especialidades <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 border border-gray-300 rounded-lg">
                <?php foreach ($especialidades as $esp): ?>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" 
                           name="especialidades[]" 
                           value="<?php echo $esp['id']; ?>"
                           <?php 
                           // Verifica se está marcado (edição ou após POST com erro)
                           $espId = (int)$esp['id'];
                           $checked = in_array($espId, $especialidadesMedico) || 
                                     (isset($_POST['especialidades']) && in_array($espId, array_map('intval', $_POST['especialidades'])));
                           echo $checked ? 'checked' : ''; 
                           ?>
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="text-sm"><?php echo htmlspecialchars($esp['nome']); ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if ($medicoEdicao): ?>
        <div>
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" 
                       name="ativo" 
                       value="1"
                       <?php echo ($medicoEdicao['ativo'] ?? true) ? 'checked' : ''; ?>
                       class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                <span class="text-sm font-semibold text-gray-700">Médico Ativo</span>
            </label>
        </div>
        <?php endif; ?>
        
        <div class="flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i><?php echo $medicoEdicao ? 'Atualizar' : 'Criar'; ?>
            </button>
            <?php if ($medicoEdicao): ?>
            <a href="<?php echo BASE_PATH; ?>medicos.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Busca -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form method="GET" action="" class="flex gap-3">
        <!-- Remove o parâmetro pagina ao fazer nova busca -->
        <input type="text" 
               name="busca" 
               placeholder="Buscar médico por nome ou CRM/CPF..."
               value="<?php echo htmlspecialchars($busca); ?>"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
            <i class="fas fa-search mr-2"></i>Buscar
        </button>
        <?php if ($busca): ?>
        <a href="<?php echo BASE_PATH; ?>medicos.php" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg transition">
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
                <i class="fas fa-users mr-2"></i>
                <span class="font-semibold"><?php echo $totalRegistros; ?></span> 
                médico<?php echo $totalRegistros != 1 ? 's' : ''; ?> encontrado<?php echo $totalRegistros != 1 ? 's' : ''; ?>
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
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">CRM/CPF</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Especialidades</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Telefone</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            <?php if (count($medicos) > 0): ?>
                <?php foreach ($medicos as $medico): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">
                            <?php echo htmlspecialchars($medico['nome']); ?>
                        </div>
                        <?php if (!empty($medico['observacao'])): ?>
                        <div class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-sticky-note mr-1"></i>
                            <?php echo htmlspecialchars(mb_substr($medico['observacao'], 0, 60)) . (mb_strlen($medico['observacao']) > 60 ? '...' : ''); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo htmlspecialchars($medico['crm_cpf']); ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo htmlspecialchars($medico['especialidades'] ?: '-'); ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?php echo $medico['telefone'] ? formatarTelefone($medico['telefone']) : '-'; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($medico['ativo']): ?>
                        <span class="chip bg-green-200 text-green-800">Ativo</span>
                        <?php else: ?>
                        <span class="chip bg-gray-200 text-gray-800">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-3">
                            <a href="?acao=editar&id=<?php echo $medico['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800"
                               title="Editar médico">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($auth->isAdmin()): ?>
                                <?php if ($medico['ativo']): ?>
                                <a href="?acao=inativar&id=<?php echo $medico['id']; ?>" 
                                   onclick="return confirm('Deseja realmente inativar este médico?')"
                                   class="text-orange-600 hover:text-orange-800"
                                   title="Inativar médico">
                                    <i class="fas fa-ban"></i>
                                </a>
                                <?php endif; ?>
                                <a href="?acao=excluir&id=<?php echo $medico['id']; ?>" 
                                   onclick="return confirm('⚠️ ATENÇÃO: Deseja realmente EXCLUIR este médico?\n\nEsta ação não pode ser desfeita!\n\nSe houver pacientes vinculados, a exclusão será bloqueada.')"
                                   class="text-red-600 hover:text-red-800"
                                   title="Excluir médico permanentemente">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-user-md text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg font-semibold">Nenhum médico encontrado</p>
                            <?php if ($busca): ?>
                            <p class="text-gray-400 text-sm mt-2">
                                Tente alterar os critérios de busca
                            </p>
                            <a href="<?php echo BASE_PATH; ?>medicos.php" 
                               class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-redo mr-2"></i>Ver todos
                            </a>
                            <?php else: ?>
                            <p class="text-gray-400 text-sm mt-2">
                                Cadastre o primeiro médico usando o formulário acima
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
            médico<?php echo $totalRegistros != 1 ? 's' : ''; ?>
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
