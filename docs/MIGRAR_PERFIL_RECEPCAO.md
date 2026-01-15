# Migração de Perfil: Recepção → Atendente

## 🔍 Problema Identificado

Usuários com perfil **"recepcao"** (nome antigo) não apareciam corretamente na tabela porque o sistema agora usa **"atendente"**.

---

## ✅ Soluções Implementadas

### **1. Suporte Temporário ao Perfil "Recepção"**

Os usuários com perfil "recepcao" agora aparecem na tabela com:
- ✅ Badge **amarelo** com texto "Recepção (Atualizar)"
- ✅ Aviso visual para migrar

### **2. Opções de Migração**

Você tem 2 opções para resolver isso:

---

## 🚀 **OPÇÃO 1: Migração Automática via SQL (Recomendado)**

Execute o script SQL para atualizar **todos** os usuários de uma vez:

### **Passo 1: Executar SQL**

```bash
mysql -u usuario -p dema5738_lista_espera_hospital < sql/migrar_perfil_recepcao.sql
```

Ou via **phpMyAdmin**:

1. Abra phpMyAdmin
2. Selecione o banco `dema5738_lista_espera_hospital`
3. Vá em **SQL**
4. Cole e execute:

```sql
USE dema5738_lista_espera_hospital;

-- Ver quantos usuários têm perfil "recepcao"
SELECT COUNT(*) as total FROM usuarios WHERE perfil = 'recepcao';

-- Atualizar todos de "recepcao" para "atendente"
UPDATE usuarios 
SET perfil = 'atendente' 
WHERE perfil = 'recepcao';

-- Verificar resultado
SELECT id, nome, email, perfil FROM usuarios ORDER BY perfil;
```

### **Passo 2: Remover Suporte Legado (Opcional)**

Após migrar todos os dados, você pode limpar o código:

**Em `usuarios.php`, REMOVER estas linhas:**

1. Linha 74 (validação):
```php
// ANTES
} elseif (!in_array($_POST['perfil'], ['administrador', 'atendente', 'medico', 'recepcao'])) {

// DEPOIS
} elseif (!in_array($_POST['perfil'], ['administrador', 'atendente', 'medico'])) {
```

2. Linha 354 (exibição na tabela):
```php
// REMOVER esta linha:
'recepcao' => ['Recepção (Atualizar)', 'bg-yellow-200 text-yellow-800'], // Legado
```

3. Linhas 232-245 (opção no formulário):
```php
// REMOVER este bloco completo:
<?php if (($usuarioEdicao['perfil'] ?? '') === 'recepcao'): ?>
    <option value="recepcao" selected style="background-color: #fef3c7;">
        ⚠️ Recepção (Atualizar para Atendente)
    </option>
<?php endif; ?>
<?php if (($usuarioEdicao['perfil'] ?? '') === 'recepcao'): ?>
    <p class="text-xs text-yellow-700 mt-1 bg-yellow-50 p-2 rounded">
        <i class="fas fa-exclamation-triangle"></i> Este usuário tem perfil antigo "Recepção". Altere para "Atendente" e salve.
    </p>
<?php endif; ?>
```

4. Linha 311 (filtro):
```php
// REMOVER esta linha:
<option value="recepcao" <?php echo $filtroPerfil === 'recepcao' ? 'selected' : ''; ?>>⚠️ Recepção (Legado)</option>
```

---

## 🔧 **OPÇÃO 2: Migração Manual (Um por Um)**

Se preferir migrar manualmente:

### **Passo 1: Filtrar Usuários "Recepção"**
1. Acesse **Usuários**
2. No filtro de perfil, selecione **"⚠️ Recepção (Legado)"**
3. Clique em **Buscar**

### **Passo 2: Editar Cada Usuário**
1. Clique em **Editar** no usuário com perfil "Recepção"
2. Você verá um **aviso amarelo**: "Este usuário tem perfil antigo..."
3. No select de perfil, altere de **"⚠️ Recepção (Atualizar para Atendente)"** para **"Atendente"**
4. Clique em **Atualizar**
5. ✅ O usuário agora aparece corretamente como "Atendente"

### **Passo 3: Repetir**
Repita para todos os usuários com perfil "Recepção"

---

## 📊 **Visualização na Tabela**

### **ANTES (não aparecia)**
```
❌ Nome: João Silva
❌ Perfil: Desconhecido (cinza)
```

### **AGORA (com suporte temporário)**
```
⚠️ Nome: João Silva
⚠️ Perfil: Recepção (Atualizar) (amarelo)
```

### **DEPOIS DA MIGRAÇÃO**
```
✅ Nome: João Silva
✅ Perfil: Atendente (azul)
```

---

## 🎯 **Recomendação**

### **Use a OPÇÃO 1 (SQL)** se:
- ✅ Você tem **muitos** usuários com perfil "recepcao"
- ✅ Quer migrar **todos de uma vez**
- ✅ Quer limpar o código depois

### **Use a OPÇÃO 2 (Manual)** se:
- ✅ Tem **poucos** usuários (1-5)
- ✅ Quer revisar cada um individualmente
- ✅ Prefere não mexer no banco diretamente

---

## 📝 **Arquivos Criados**

1. ✅ `sql/migrar_perfil_recepcao.sql` - Script de migração automática
2. ✅ `MIGRAR_PERFIL_RECEPCAO.md` - Este guia

---

## ⚠️ **Importante**

- O suporte para "recepcao" é **temporário**
- Após migrar todos os dados, **remova o código legado**
- Se criar novos usuários, use apenas: **Administrador**, **Atendente** ou **Médico**

---

## ✅ **Checklist de Migração**

- [ ] Executar SQL de migração (Opção 1) OU editar manualmente (Opção 2)
- [ ] Verificar que todos aparecem como "Atendente"
- [ ] Remover código legado do `usuarios.php`
- [ ] Testar filtros e edição

---

**Data**: 04/12/2024  
**Status**: Suporte legado temporário ativado  
**Próximo passo**: Executar migração SQL
