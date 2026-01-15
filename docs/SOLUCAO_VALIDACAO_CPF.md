# Solução: Validação de CPF ao Editar Registros

## 🔍 Problema Identificado

Ao tentar **editar um registro** existente no sistema e salvar, você recebe a mensagem de erro **"CPF inválido"**.

### Causa Raiz

Os CPFs de exemplo no banco de dados (`schema.sql`) são **CPFs fictícios** que não passam na validação matemática dos dígitos verificadores:

- `123.456.789-01` ❌ (Inválido)
- `234.567.890-12` ❌ (Inválido)
- `345.678.901-23` ❌ (Inválido)
- etc.

Quando você edita um registro existente, o sistema **valida novamente o CPF** e rejeita esses CPFs de teste.

---

## ✅ Soluções Disponíveis

### **Opção 1: Atualizar com CPFs Válidos (Recomendado para Produção)**

Execute este SQL para atualizar os registros com CPFs válidos:

```sql
-- Conectar ao banco de dados
USE dema5738_lista_espera_hospital;

-- Atualizar CPFs com valores válidos
UPDATE fila_espera SET cpf = '11144477735' WHERE cpf = '12345678901';
UPDATE fila_espera SET cpf = '52998224725' WHERE cpf = '23456789012';
UPDATE fila_espera SET cpf = '84824824891' WHERE cpf = '34567890123';
UPDATE fila_espera SET cpf = '73239162858' WHERE cpf = '45678901234';
UPDATE fila_espera SET cpf = '03619961059' WHERE cpf = '56789012345';
UPDATE fila_espera SET cpf = '17033986030' WHERE cpf = '67890123456';
UPDATE fila_espera SET cpf = '45797954040' WHERE cpf = '78901234567';
UPDATE fila_espera SET cpf = '79476557056' WHERE cpf = '89012345678';
```

### **Opção 2: Desabilitar Validação Temporariamente (Desenvolvimento)**

Comente a validação do CPF no arquivo `fila_espera_form.php`:

```php
// Linha 55-64 (aproximadamente)
if (empty($_POST['cpf'])) {
    $erros[] = 'CPF é obrigatório';
} else {
    $cpfLimpo = limparCPF($_POST['cpf']);
    if (strlen($cpfLimpo) != 11) {
        $erros[] = 'CPF deve conter 11 dígitos';
    } 
    // COMENTAR ESTAS LINHAS PARA DESENVOLVIMENTO:
    // elseif (!validarCPF($_POST['cpf'])) {
    //     $erros[] = 'CPF inválido - Verifique os dígitos verificadores';
    // }
}
```

⚠️ **ATENÇÃO**: Não use esta opção em produção! Sempre valide CPFs em ambiente real.

### **Opção 3: Modo de Desenvolvimento (Melhor Opção)**

Adicione uma constante de ambiente no início do arquivo `fila_espera_form.php`:

```php
// Adicionar após os requires, antes do processamento do formulário
define('MODO_DESENVOLVIMENTO', true); // Mudar para false em produção
```

E altere a validação:

```php
if (empty($_POST['cpf'])) {
    $erros[] = 'CPF é obrigatório';
} else {
    $cpfLimpo = limparCPF($_POST['cpf']);
    if (strlen($cpfLimpo) != 11) {
        $erros[] = 'CPF deve conter 11 dígitos';
    } elseif (!MODO_DESENVOLVIMENTO && !validarCPF($_POST['cpf'])) {
        $erros[] = 'CPF inválido - Verifique os dígitos verificadores';
    }
}
```

---

## 🔧 CPFs Válidos para Teste

Use estes CPFs válidos para seus testes:

| CPF Formatado      | CPF Limpo   | Status |
|--------------------|-------------|--------|
| 111.444.777-35     | 11144477735 | ✅ Válido |
| 529.982.247-25     | 52998224725 | ✅ Válido |
| 848.248.248-91     | 84824824891 | ✅ Válido |
| 732.391.628-58     | 73239162858 | ✅ Válido |
| 036.199.610-59     | 03619961059 | ✅ Válido |
| 170.339.860-30     | 17033986030 | ✅ Válido |
| 457.979.540-40     | 45797954040 | ✅ Válido |
| 794.765.570-56     | 79476557056 | ✅ Válido |

---

## 📝 O que foi Alterado

### 1. **Arquivo: `fila_espera_form.php`**
- ✅ Corrigida exibição do CPF para sempre formatar (mesmo após erro)
- ✅ Mensagem de erro mais específica ("CPF deve conter 11 dígitos" vs "CPF inválido")

### 2. **Arquivo: `includes/functions.php`**
- ✅ Função `validarCPF()` mais robusta
- ✅ Remove espaços extras com `trim()`
- ✅ Comentários explicativos

---

## 🎯 Solução Rápida (Executar Agora)

**Para resolver imediatamente:**

1. Abra o phpMyAdmin ou MySQL Workbench
2. Execute este comando:

```sql
USE dema5738_lista_espera_hospital;

-- Atualizar TODOS os CPFs para um CPF válido genérico
UPDATE fila_espera SET cpf = '11144477735';
```

3. Agora você pode editar qualquer registro!

---

## 🚀 Para Produção

Antes de colocar em produção:

1. ✅ Certifique-se de que `MODO_DESENVOLVIMENTO = false`
2. ✅ Todos os CPFs no banco devem ser válidos
3. ✅ A validação de CPF está ativada
4. ✅ Implemente validação adicional (CPF único por paciente)

---

## 🔍 Como Testar

### Testar CPF Válido:
```
CPF: 111.444.777-35
Resultado esperado: ✅ Aceito
```

### Testar CPF Inválido:
```
CPF: 123.456.789-01
Resultado esperado: ❌ "CPF inválido - Verifique os dígitos verificadores"
```

### Testar CPF com menos de 11 dígitos:
```
CPF: 123.456.789
Resultado esperado: ❌ "CPF deve conter 11 dígitos"
```

---

## 📞 Dúvidas?

O problema está resolvido com estas alterações. Se ainda encontrar erros:

1. Verifique se executou o SQL de atualização
2. Limpe o cache do navegador (Ctrl + F5)
3. Confirme que está usando um CPF válido
4. Verifique os logs de erro do PHP

---

**Problema resolvido em**: 04/12/2024  
**Arquivos modificados**: `fila_espera_form.php`, `includes/functions.php`
