<?php
/**
 * Funções Auxiliares do Sistema
 * Funções de formatação, validação e utilidades gerais
 */

require_once __DIR__ . '/../config.php';

/**
 * Formata CPF no padrão XXX.XXX.XXX-XX
 */
function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) == 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    return $cpf;
}

/**
 * Remove formatação do CPF
 */
function limparCPF($cpf) {
    return preg_replace('/[^0-9]/', '', $cpf);
}

/**
 * Valida CPF (verifica dígitos verificadores)
 */
function validarCPF($cpf) {
    // Remove todos os caracteres não numéricos e espaços
    $cpf = preg_replace('/[^0-9]/', '', trim($cpf));
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Valida dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        $d = 0;
        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

/**
 * Formata data do formato YYYY-MM-DD para DD/MM/YYYY
 */
function formatarData($data) {
    if (empty($data)) return '';
    
    $timestamp = strtotime($data);
    if ($timestamp === false) return $data;
    
    return date('d/m/Y', $timestamp);
}

/**
 * Converte data do formato DD/MM/YYYY para YYYY-MM-DD
 */
function converterDataBanco($data) {
    if (empty($data)) return null;
    
    $partes = explode('/', $data);
    if (count($partes) == 3) {
        return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    }
    return $data;
}

/**
 * Formata telefone
 */
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    if (strlen($telefone) == 11) {
        // Celular: (XX) XXXXX-XXXX
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7, 4);
    } elseif (strlen($telefone) == 10) {
        // Fixo: (XX) XXXX-XXXX
        return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6, 4);
    }
    
    return $telefone;
}

/**
 * Prepara telefone para WhatsApp (limpa e adiciona código do país)
 */
function prepararTelefoneWhatsApp($telefone) {
    // Remove toda formatação
    $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
    
    // Adicionar código do país Brasil (55) se ainda não tiver
    if (strlen($telefoneLimpo) <= 11 && !str_starts_with($telefoneLimpo, '55')) {
        $telefoneLimpo = '55' . $telefoneLimpo;
    }
    
    return $telefoneLimpo;
}

/**
 * Gera link do WhatsApp formatado
 */
function gerarLinkWhatsApp($telefone, $mensagem = '') {
    $telefoneWhatsApp = prepararTelefoneWhatsApp($telefone);
    $url = 'https://wa.me/' . $telefoneWhatsApp;
    
    if (!empty($mensagem)) {
        $url .= '?text=' . urlencode($mensagem);
    }
    
    return $url;
}

/**
 * Renderiza link do WhatsApp com ícone
 */
function renderizarLinkWhatsApp($telefone, $classe = '', $mostrarIcone = true) {
    if (empty($telefone)) {
        return '';
    }
    
    $telefoneFormatado = formatarTelefone($telefone);
    $linkWhatsApp = gerarLinkWhatsApp($telefone);
    $classeBase = 'inline-flex items-center text-green-600 hover:text-green-800 hover:underline transition';
    $classeCompleta = $classeBase . (!empty($classe) ? ' ' . $classe : '');
    
    $icone = $mostrarIcone ? '<i class="fab fa-whatsapp text-lg mr-1"></i>' : '';
    
    return sprintf(
        '<a href="%s" target="_blank" class="%s" title="Abrir WhatsApp com %s">%s<span>%s</span></a>',
        htmlspecialchars($linkWhatsApp),
        htmlspecialchars($classeCompleta),
        htmlspecialchars($telefoneFormatado),
        $icone,
        htmlspecialchars($telefoneFormatado)
    );
}

/**
 * Sanitiza string para prevenir XSS
 */
function sanitizar($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Gera classes CSS para chips coloridos baseado na cor armazenada
 */
function gerarClasseChip($cor) {
    // Se a cor já é uma classe Tailwind, retorna ela
    if (strpos($cor, 'bg-') === 0) {
        // Extrai a cor base (ex: 'blue' de 'bg-blue-200')
        preg_match('/bg-(\w+)-/', $cor, $matches);
        $corBase = $matches[1] ?? 'gray';
        
        return $cor . ' text-' . $corBase . '-800';
    }
    
    // Cor padrão se não reconhecer
    return 'bg-gray-200 text-gray-800';
}

/**
 * Gera mensagem de alerta formatada
 */
function exibirAlerta($tipo, $mensagem) {
    $cores = [
        'sucesso' => 'bg-green-100 border-green-400 text-green-700',
        'erro' => 'bg-red-100 border-red-400 text-red-700',
        'aviso' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $classe = $cores[$tipo] ?? $cores['info'];
    
    return '<div class="' . $classe . ' px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">' . sanitizar($mensagem) . '</span>
            </div>';
}

/**
 * Redireciona para uma página
 */
function redirecionar($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Verifica se o usuário está logado
 */
function verificarLogin() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        redirecionar(BASE_PATH . 'login.php');
    }
}

/**
 * Verifica se o usuário tem permissão (perfil)
 */
function verificarPermissao($perfisPermitidos = []) {
    verificarLogin();
    
    if (!empty($perfisPermitidos) && !in_array($_SESSION['usuario_perfil'], $perfisPermitidos)) {
        redirecionar(BASE_PATH . 'dashboard.php?erro=sem_permissao');
    }
}

/**
 * Obtém informações do usuário logado
 */
function getUsuarioLogado() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'nome' => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'perfil' => $_SESSION['usuario_perfil'] ?? ''
    ];
}

/**
 * Paginar resultados
 */
function paginar($totalRegistros, $registrosPorPagina = 20, $paginaAtual = 1) {
    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
    $paginaAtual = max(1, min($totalPaginas, $paginaAtual));
    $offset = ($paginaAtual - 1) * $registrosPorPagina;
    
    return [
        'total_registros' => $totalRegistros,
        'total_paginas' => $totalPaginas,
        'pagina_atual' => $paginaAtual,
        'registros_por_pagina' => $registrosPorPagina,
        'offset' => $offset
    ];
}

/**
 * Gera token CSRF
 */
function gerarTokenCSRF() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verifica token CSRF
 */
function verificarTokenCSRF($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
