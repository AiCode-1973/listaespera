# 📱 Links do WhatsApp no Sistema

## ✅ **Implementação Concluída**

### **Onde está funcionando:**
- ✅ `dashboard.php` - Tabela de pacientes na fila

---

## 🎨 **Visual no Dashboard**

### **Telefone com WhatsApp:**
```
📱 (11) 98765-4321  ← Clicável, abre WhatsApp
```

### **Dois Telefones:**
```
📱 (11) 98765-4321  ← Telefone 1
📱 (11) 91234-5678  ← Telefone 2 (menor)
```

**Comportamento:**
- ✅ Ícone verde do WhatsApp
- ✅ Hover muda cor para verde escuro
- ✅ Sublinhado ao passar mouse
- ✅ Abre em nova aba
- ✅ Adiciona automaticamente código do Brasil (+55)

---

## 🔧 **Funções Criadas** (`includes/functions.php`)

### **1. `prepararTelefoneWhatsApp($telefone)`**
Remove formatação e adiciona código do país (+55)

```php
$tel = prepararTelefoneWhatsApp('(11) 98765-4321');
// Retorna: 5511987654321
```

### **2. `gerarLinkWhatsApp($telefone, $mensagem = '')`**
Gera URL completa do WhatsApp

```php
$link = gerarLinkWhatsApp('11987654321');
// Retorna: https://wa.me/5511987654321

// Com mensagem
$link = gerarLinkWhatsApp('11987654321', 'Olá, sobre sua consulta...');
// Retorna: https://wa.me/5511987654321?text=Ol%C3%A1%2C+sobre+sua+consulta...
```

### **3. `renderizarLinkWhatsApp($telefone, $classe = '', $mostrarIcone = true)`**
Renderiza HTML completo do link

```php
echo renderizarLinkWhatsApp('11987654321');
// Saída: <a href="..." class="..." target="_blank">
//           <i class="fab fa-whatsapp"></i>
//           <span>(11) 98765-4321</span>
//        </a>
```

---

## 📝 **Código Implementado**

### **Dashboard.php - Coluna Telefone:**

```php
<td class="px-2 py-2 text-sm">
    <?php 
    // Telefone 1 com WhatsApp
    $telefoneFormatado = formatarTelefone($reg['telefone1']);
    $telefoneWhatsApp = preg_replace('/[^0-9]/', '', $reg['telefone1']);
    if (strlen($telefoneWhatsApp) <= 11 && !str_starts_with($telefoneWhatsApp, '55')) {
        $telefoneWhatsApp = '55' . $telefoneWhatsApp;
    }
    ?>
    <a href="https://wa.me/<?php echo $telefoneWhatsApp; ?>" 
       target="_blank"
       class="inline-flex items-center text-green-600 hover:text-green-800 hover:underline transition"
       title="Abrir WhatsApp">
        <i class="fab fa-whatsapp text-lg mr-1"></i>
        <span><?php echo $telefoneFormatado; ?></span>
    </a>
    
    <?php if (!empty($reg['telefone2'])): ?>
        <!-- Telefone 2 -->
        <br>
        <a href="https://wa.me/<?php echo prepararTelefoneWhatsApp($reg['telefone2']); ?>" 
           target="_blank"
           class="inline-flex items-center text-green-500 hover:text-green-700 hover:underline transition text-xs mt-1">
            <i class="fab fa-whatsapp text-sm mr-1"></i>
            <span><?php echo formatarTelefone($reg['telefone2']); ?></span>
        </a>
    <?php endif; ?>
</td>
```

---

## 🚀 **Como Usar em Outros Lugares**

### **Exemplo 1: Simples**
```php
<?php echo renderizarLinkWhatsApp($paciente['telefone1']); ?>
```

### **Exemplo 2: Com Mensagem Pré-definida**
```php
<?php 
$mensagem = "Olá {$paciente['nome']}, sua consulta está agendada para " . formatarData($paciente['data_agendamento']);
$link = gerarLinkWhatsApp($paciente['telefone1'], $mensagem);
?>
<a href="<?php echo $link; ?>" target="_blank">
    Enviar confirmação
</a>
```

### **Exemplo 3: Customizado**
```php
<?php echo renderizarLinkWhatsApp(
    $paciente['telefone1'], 
    'text-blue-600', // Classe customizada
    true // Mostrar ícone
); ?>
```

---

## 📱 **Formato do Link WhatsApp**

### **Estrutura:**
```
https://wa.me/[CÓDIGO_PAÍS][DDD][NÚMERO]
```

### **Exemplos:**
```
https://wa.me/5511987654321          ← Celular SP
https://wa.me/5521987654321          ← Celular RJ
https://wa.me/5511987654321?text=Oi  ← Com mensagem
```

### **Regras:**
- ✅ Código do país: `55` (Brasil)
- ✅ DDD: 2 dígitos (11, 21, 48, etc)
- ✅ Número: 8 ou 9 dígitos
- ✅ Sem espaços, parênteses ou hífens
- ✅ Total: 13 dígitos (55 + DDD + número)

---

## 🎨 **Cores Usadas**

| Elemento | Cor Normal | Cor Hover |
|----------|-----------|-----------|
| Telefone 1 | `text-green-600` | `text-green-800` |
| Telefone 2 | `text-green-500` | `text-green-700` |
| Ícone | `fab fa-whatsapp` | - |

---

## ✅ **Checklist de Funcionalidades**

- [x] Link abre WhatsApp Web/App
- [x] Código do país (+55) adicionado automaticamente
- [x] Formatação do telefone mantida visualmente
- [x] Ícone do WhatsApp visível
- [x] Suporte para telefone 1 e telefone 2
- [x] Abre em nova aba (target="_blank")
- [x] Tooltip ao passar mouse
- [x] Efeito hover (sublinhado)
- [x] Funções reutilizáveis criadas

---

## 🔄 **Próximos Passos (Opcional)**

### **Melhorias Futuras:**

1. **Mensagens Automáticas:**
   ```php
   $mensagem = "Olá! Aqui é do Hospital. Sua consulta com Dr. {$medico} está agendada.";
   ```

2. **Botão de Envio em Massa:**
   - Selecionar pacientes
   - Enviar mensagem para todos

3. **Histórico de Mensagens:**
   - Registrar quando foi enviado
   - Quem enviou

4. **Templates de Mensagens:**
   - Confirmação de agendamento
   - Lembrete 1 dia antes
   - Reagendamento

---

## 📊 **Onde Adicionar Links WhatsApp**

### **Sugestões:**

- [ ] `fila_espera_view.php` - Página de detalhes do paciente
- [ ] Relatórios de agendamento
- [ ] Lista de pacientes urgentes
- [ ] Exportação Excel com links clicáveis

---

## 🐛 **Resolução de Problemas**

### **Link não funciona:**
1. Verificar se telefone tem DDD
2. Verificar se tem 10 ou 11 dígitos
3. Remover espaços e caracteres especiais

### **Ícone não aparece:**
1. Verificar se Font Awesome está carregado:
   ```html
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   ```

### **Abre navegador mas não WhatsApp:**
- Usuário precisa ter WhatsApp instalado
- Ou usar WhatsApp Web

---

**Data**: 04/12/2024  
**Status**: ✅ Implementado e funcionando  
**Arquivos modificados**: 
- `dashboard.php`
- `includes/functions.php`
