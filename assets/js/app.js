/**
 * JavaScript Principal do Sistema de Lista de Espera
 * Funções de validação, máscaras e interações
 */

// =====================================================
// MÁSCARAS DE INPUT
// =====================================================

/**
 * Aplica máscara de CPF (XXX.XXX.XXX-XX)
 */
function mascaraCPF(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length <= 11) {
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    }
    
    input.value = valor;
}

/**
 * Aplica máscara de Telefone
 */
function mascaraTelefone(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length <= 11) {
        if (valor.length <= 10) {
            // Telefone fixo: (XX) XXXX-XXXX
            valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            // Celular: (XX) XXXXX-XXXX
            valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
        }
    }
    
    input.value = valor;
}

/**
 * Aplica máscara de Data (DD/MM/AAAA)
 */
function mascaraData(input) {
    let valor = input.value.replace(/\D/g, '');
    
    if (valor.length <= 8) {
        valor = valor.replace(/(\d{2})(\d)/, '$1/$2');
        valor = valor.replace(/(\d{2})(\d)/, '$1/$2');
    }
    
    input.value = valor;
}

// =====================================================
// VALIDAÇÕES
// =====================================================

/**
 * Valida CPF (dígitos verificadores)
 */
function validarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }
    
    let soma = 0;
    let resto;
    
    // Valida primeiro dígito
    for (let i = 1; i <= 9; i++) {
        soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
    }
    
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(9, 10))) return false;
    
    soma = 0;
    
    // Valida segundo dígito
    for (let i = 1; i <= 10; i++) {
        soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
    }
    
    resto = (soma * 10) % 11;
    if (resto === 10 || resto === 11) resto = 0;
    if (resto !== parseInt(cpf.substring(10, 11))) return false;
    
    return true;
}

/**
 * Valida data no formato DD/MM/AAAA
 */
function validarData(data) {
    const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    
    if (!regex.test(data)) return false;
    
    const partes = data.split('/');
    const dia = parseInt(partes[0], 10);
    const mes = parseInt(partes[1], 10);
    const ano = parseInt(partes[2], 10);
    
    if (mes < 1 || mes > 12) return false;
    
    const diasNoMes = new Date(ano, mes, 0).getDate();
    
    return dia >= 1 && dia <= diasNoMes;
}

/**
 * Valida e-mail
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// =====================================================
// MODAIS
// =====================================================

/**
 * Abre modal
 */
function abrirModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Fecha modal
 */
function fecharModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Fecha modal ao clicar no backdrop
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-backdrop')) {
        const modals = document.querySelectorAll('.modal-backdrop');
        modals.forEach(function(modal) {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = 'auto';
    }
});

// Fecha modal com tecla ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal-backdrop:not(.hidden)');
        modals.forEach(function(modal) {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = 'auto';
    }
});

// =====================================================
// ALERTAS
// =====================================================

/**
 * Fecha alerta
 */
function fecharAlerta(elemento) {
    elemento.parentElement.style.display = 'none';
}

/**
 * Auto-fecha alertas após 5 segundos
 */
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alertas = document.querySelectorAll('[role="alert"]');
        alertas.forEach(function(alerta) {
            alerta.style.transition = 'opacity 0.5s';
            alerta.style.opacity = '0';
            setTimeout(function() {
                alerta.style.display = 'none';
            }, 500);
        });
    }, 5000);
});

// =====================================================
// CONFIRMAÇÕES
// =====================================================

/**
 * Confirmação de exclusão
 */
function confirmarExclusao(mensagem) {
    return confirm(mensagem || 'Deseja realmente excluir este registro?');
}

/**
 * Confirmação genérica
 */
function confirmar(mensagem) {
    return confirm(mensagem);
}

// =====================================================
// UTILITÁRIOS
// =====================================================

/**
 * Formata número como moeda BRL
 */
function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { 
        style: 'currency', 
        currency: 'BRL' 
    });
}

/**
 * Copia texto para clipboard
 */
function copiarTexto(texto) {
    navigator.clipboard.writeText(texto).then(function() {
        alert('Texto copiado!');
    }).catch(function(err) {
        console.error('Erro ao copiar texto:', err);
    });
}

/**
 * Toggle de elemento (show/hide)
 */
function toggleElemento(elementoId) {
    const elemento = document.getElementById(elementoId);
    if (elemento) {
        if (elemento.style.display === 'none') {
            elemento.style.display = 'block';
        } else {
            elemento.style.display = 'none';
        }
    }
}

/**
 * Scroll suave para elemento
 */
function scrollPara(elementoId) {
    const elemento = document.getElementById(elementoId);
    if (elemento) {
        elemento.scrollIntoView({ behavior: 'smooth' });
    }
}

// =====================================================
// FORMULÁRIOS
// =====================================================

/**
 * Validação de formulário antes de submeter
 */
function validarFormulario(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const campos = form.querySelectorAll('[required]');
    let valido = true;
    
    campos.forEach(function(campo) {
        if (!campo.value.trim()) {
            campo.classList.add('border-red-500');
            valido = false;
        } else {
            campo.classList.remove('border-red-500');
        }
    });
    
    if (!valido) {
        alert('Por favor, preencha todos os campos obrigatórios.');
    }
    
    return valido;
}

/**
 * Limpa formulário
 */
function limparFormulario(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
        // Remove classes de erro
        const campos = form.querySelectorAll('input, select, textarea');
        campos.forEach(function(campo) {
            campo.classList.remove('border-red-500');
        });
    }
}

// =====================================================
// TABELAS
// =====================================================

/**
 * Ordena tabela por coluna
 */
function ordenarTabela(tabelaId, colunaIndex) {
    const tabela = document.getElementById(tabelaId);
    if (!tabela) return;
    
    const tbody = tabela.querySelector('tbody');
    const linhas = Array.from(tbody.querySelectorAll('tr'));
    
    linhas.sort((a, b) => {
        const valorA = a.cells[colunaIndex].textContent.trim();
        const valorB = b.cells[colunaIndex].textContent.trim();
        
        return valorA.localeCompare(valorB, 'pt-BR', { numeric: true });
    });
    
    linhas.forEach(linha => tbody.appendChild(linha));
}

/**
 * Filtro de tabela em tempo real
 */
function filtrarTabela(inputId, tabelaId) {
    const input = document.getElementById(inputId);
    const tabela = document.getElementById(tabelaId);
    
    if (!input || !tabela) return;
    
    input.addEventListener('keyup', function() {
        const filtro = input.value.toLowerCase();
        const linhas = tabela.querySelectorAll('tbody tr');
        
        linhas.forEach(function(linha) {
            const texto = linha.textContent.toLowerCase();
            linha.style.display = texto.includes(filtro) ? '' : 'none';
        });
    });
}

// =====================================================
// LOADING
// =====================================================

/**
 * Mostra indicador de loading
 */
function mostrarLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.remove('hidden');
    }
}

/**
 * Esconde indicador de loading
 */
function esconderLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.classList.add('hidden');
    }
}

// =====================================================
// IMPRESSÃO
// =====================================================

/**
 * Imprime elemento específico
 */
function imprimirElemento(elementoId) {
    const elemento = document.getElementById(elementoId);
    if (!elemento) return;
    
    const conteudoOriginal = document.body.innerHTML;
    const conteudoImprimir = elemento.innerHTML;
    
    document.body.innerHTML = conteudoImprimir;
    window.print();
    document.body.innerHTML = conteudoOriginal;
    location.reload();
}

// =====================================================
// CONSOLE LOG (DEV)
// =====================================================

console.log('✅ Sistema de Lista de Espera - JavaScript carregado');
console.log('📋 Versão: 1.0.0');
console.log('🔧 Ambiente:', window.location.hostname === 'localhost' ? 'Desenvolvimento' : 'Produção');
