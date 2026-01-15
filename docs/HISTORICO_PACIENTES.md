# 👥 Módulo de Histórico de Pacientes

## ✅ **Implementação Concluída**

### **Sistema completo para visualizar todos os registros de um paciente**

---

## 🎯 **O QUE FOI CRIADO**

### **1. Página de Busca** (`pacientes.php`)
- ✅ Busca por nome ou CPF
- ✅ Lista de pacientes encontrados
- ✅ Estatísticas por paciente (total, agendados, pendentes)
- ✅ Link direto para histórico completo
- ✅ Acesso restrito a administradores

### **2. Página de Histórico** (`paciente_historico.php`)
- ✅ Timeline visual com todos os registros
- ✅ Cards detalhados para cada atendimento
- ✅ Diferenciação visual (agendado/pendente/urgente)
- ✅ Estatísticas resumidas no topo
- ✅ Links para visualizar e editar registros

### **3. Link no Menu** (`includes/header.php`)
- ✅ Botão "Pacientes" visível apenas para admin
- ✅ Integrado ao menu de navegação

---

## 🎨 **INTERFACE**

### **📋 Página de Busca:**

```
┌────────────────────────────────────────────┐
│ 👥 Histórico de Pacientes                 │
│ Busque pacientes e veja todo o histórico  │
│                        [← Voltar Dashboard]│
└────────────────────────────────────────────┘

┌────────────────────────────────────────────┐
│ 🔍 Buscar Paciente                         │
│ [Digite nome ou CPF...         ] [Buscar]  │
└────────────────────────────────────────────┘

┌────────────────────────────────────────────┐
│ 📋 Resultados (3 pacientes encontrados)    │
├────────────────────────────────────────────┤
│ 👤 Nome    CPF          Telefone           │
│ João Silva 123.456.789 (11) 98765-4321    │
│ Total: 5   Agendados: 3  Pendentes: 2     │
│                          [Ver Histórico]   │
└────────────────────────────────────────────┘
```

### **📊 Página de Histórico:**

```
┌────────────────────────────────────────────┐
│ 👤 João Silva                              │
│ CPF: 123.456.789-00 | Tel: (11) 98765-4321│
│                        [← Voltar à Busca]  │
└────────────────────────────────────────────┘

┌──────┬──────┬──────┬──────┐
│  5   │  3   │  2   │  1   │
│Total │Agend.│Pend. │Urgent│
└──────┴──────┴──────┴──────┘

┌────────────────────────────────────────────┐
│ ⏱️ Histórico Completo de Atendimentos      │
├────────────────────────────────────────────┤
│ ✓ Consulta [Agendado] [10/12/2024]        │
│   Dr. Carlos | Cardiologia | Unimed       │
│   Solicitado: 01/12 | Agendado: 10/12     │
│   [👁️] [✏️]                                 │
├────────────────────────────────────────────┤
│ ⏰ Exame [Aguardando] [05/12/2024]         │
│   Dra. Maria | Radiologia | Particular    │
│   Solicitado: 05/12                        │
│   [👁️] [✏️]                                 │
└────────────────────────────────────────────┘
```

---

## 🔍 **FUNCIONALIDADES**

### **Página de Busca:**

#### **1. Busca Inteligente:**
- ✅ Por nome (parcial ou completo)
- ✅ Por CPF (formatado ou não)
- ✅ Busca case-insensitive
- ✅ Limite de 50 resultados

#### **2. Informações Exibidas:**
- Nome do paciente
- CPF formatado
- Telefone formatado
- Total de registros
- Total agendados
- Total pendentes
- Data da última solicitação

#### **3. Ações:**
- Botão "Ver Histórico" para cada paciente

---

### **Página de Histórico:**

#### **1. Cabeçalho com Dados do Paciente:**
- Nome completo
- CPF formatado
- Telefone principal

#### **2. Estatísticas Visuais:**
```
📊 Total de Registros    | Número total
✅ Agendados            | Em verde
⏰ Pendentes            | Em amarelo
🚨 Urgentes             | Em vermelho
```

#### **3. Timeline de Registros:**

**Cada card mostra:**
- ✅ Tipo de atendimento
- ✅ Status (Agendado/Aguardando)
- ✅ Indicador de urgência
- ✅ Data de solicitação
- ✅ Data de agendamento (se houver)
- ✅ Médico responsável
- ✅ Especialidade
- ✅ Convênio
- ✅ Usuário que solicitou
- ✅ Usuário que agendou
- ✅ Motivo da urgência (se urgente)
- ✅ Observações
- ✅ Botões: Visualizar e Editar

---

## 🎨 **CORES E VISUAL**

### **Status dos Registros:**

| Status | Cor de Fundo | Borda | Ícone |
|--------|-------------|-------|-------|
| **Agendado** | Verde claro | Verde | ✓ |
| **Pendente** | Amarelo claro | Amarelo | ⏰ |
| **Urgente** | Vermelho claro | Vermelho | ⚠️ |

### **Badges:**
```
🟢 [✓ Agendado]      - Verde
🟡 [⏰ Aguardando]    - Amarelo  
🔴 [⚠️ URGENTE]      - Vermelho (pulsante)
```

---

## 🔒 **SEGURANÇA**

### **Proteção em Múltiplas Camadas:**

#### **1. `pacientes.php`:**
```php
if ($usuarioLogado['perfil'] !== 'administrador') {
    $_SESSION['mensagem_erro'] = 'Acesso negado.';
    header('Location: /listaespera/dashboard.php');
    exit;
}
```

#### **2. `paciente_historico.php`:**
```php
// Verifica perfil
if ($usuarioLogado['perfil'] !== 'administrador') {
    header('Location: /listaespera/dashboard.php');
    exit;
}

// Valida CPF
if (empty($cpf)) {
    header('Location: /listaespera/pacientes.php');
    exit;
}
```

#### **3. Menu:**
```php
<?php if ($usuario['perfil'] === 'administrador'): ?>
    <a href="/listaespera/pacientes.php">Pacientes</a>
<?php endif; ?>
```

---

## 💻 **CÓDIGO SQL**

### **Busca de Pacientes:**

```sql
SELECT DISTINCT 
    nome_paciente,
    cpf,
    telefone1,
    COUNT(*) as total_registros,
    SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as total_agendados,
    SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as total_pendentes,
    MAX(data_solicitacao) as ultima_solicitacao
FROM fila_espera
WHERE (nome_paciente LIKE :busca OR cpf LIKE :busca)
GROUP BY nome_paciente, cpf, telefone1
ORDER BY ultima_solicitacao DESC
LIMIT 50
```

### **Histórico do Paciente:**

```sql
SELECT f.*, 
    m.nome as medico_nome,
    e.nome as especialidade_nome,
    c.nome as convenio_nome,
    u.nome as usuario_nome,
    ua.nome as usuario_agendamento_nome
FROM fila_espera f
LEFT JOIN medicos m ON f.medico_id = m.id
LEFT JOIN especialidades e ON f.especialidade_id = e.id
LEFT JOIN convenios c ON f.convenio_id = c.id
LEFT JOIN usuarios u ON f.usuario_id = u.id
LEFT JOIN usuarios ua ON f.usuario_agendamento_id = ua.id
WHERE f.cpf = :cpf
ORDER BY f.data_solicitacao DESC, f.id DESC
```

---

## 🔄 **FLUXOS DE USO**

### **Fluxo 1: Buscar e Ver Histórico**
1. Admin acessa menu "Pacientes"
2. ✅ Abre página de busca
3. Digite "João" ou "123.456.789"
4. Clica em "Buscar"
5. ✅ Lista de pacientes aparece
6. Clica em "Ver Histórico"
7. ✅ Timeline completa com todos os registros

### **Fluxo 2: Visualizar Detalhes**
1. No histórico, vê timeline
2. Clica no ícone 👁️ (visualizar)
3. ✅ Abre `fila_espera_view.php` com detalhes

### **Fluxo 3: Editar Registro**
1. No histórico, clica no ícone ✏️ (editar)
2. ✅ Abre formulário de edição
3. Modifica dados
4. Salva
5. ✅ Volta ao histórico atualizado

### **Fluxo 4: Atendente Tenta Acessar**
1. Atendente faz login
2. ❌ Não vê botão "Pacientes"
3. Se digitar URL manualmente
4. ❌ Redirecionado ao Dashboard
5. ✅ Mensagem de erro

---

## 📊 **DADOS EXIBIDOS**

### **Campos do Paciente:**
- Nome completo
- CPF (formatado)
- Telefone principal
- Total de registros
- Registros agendados
- Registros pendentes
- Registros urgentes

### **Campos de Cada Registro:**
- ID do registro
- Tipo de atendimento
- Status (agendado/pendente)
- Urgência (sim/não)
- Data de solicitação
- Data de agendamento
- Médico
- Especialidade
- Convênio
- Usuário solicitante
- Usuário agendador
- Motivo urgência
- Observações

---

## 🎯 **CASOS DE USO**

### **Caso 1: Paciente com Múltiplos Atendimentos**
```
João Silva tem:
- 5 consultas registradas
- 3 já agendadas
- 2 aguardando agendamento
- 1 urgente

Administrador pode:
✅ Ver todo o histórico em ordem cronológica
✅ Identificar quais estão pendentes
✅ Ver quais são urgentes
✅ Editar qualquer registro
```

### **Caso 2: Busca Rápida**
```
Paciente liga reclamando que não foi agendado

Administrador:
1. Acessa "Pacientes"
2. Busca por CPF
3. ✅ Vê histórico completo
4. Identifica registros pendentes
5. Toma ação
```

### **Caso 3: Auditoria**
```
Verificar histórico completo de atendimento

Administrador:
1. Busca paciente
2. ✅ Vê quem solicitou cada registro
3. ✅ Vê quem agendou
4. ✅ Vê datas e horários
5. Rastreabilidade completa
```

---

## 📁 **ESTRUTURA DE ARQUIVOS**

```
listaespera/
├── pacientes.php              ← Busca de pacientes
├── paciente_historico.php     ← Histórico detalhado
├── includes/
│   └── header.php             ← Menu com link
└── HISTORICO_PACIENTES.md     ← Esta documentação
```

---

## 🎨 **COMPONENTES VISUAIS**

### **Cards Estatísticos:**
```html
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center">
        <div class="bg-blue-100 rounded-full p-3">
            <i class="fas fa-file-medical text-blue-600"></i>
        </div>
        <div class="ml-4">
            <p class="text-sm text-gray-600">Total de Registros</p>
            <p class="text-3xl font-bold">5</p>
        </div>
    </div>
</div>
```

### **Timeline Item:**
```html
<div class="relative">
    <!-- Ícone Timeline -->
    <div class="h-12 w-12 rounded-full bg-green-500">
        <i class="fas fa-check text-white"></i>
    </div>
    
    <!-- Card -->
    <div class="bg-gray-50 border-l-4 border-green-500 p-5">
        <h3>Consulta</h3>
        <span class="badge">Agendado</span>
        <!-- Detalhes -->
    </div>
</div>
```

---

## ✅ **FUNCIONALIDADES IMPLEMENTADAS**

- [x] Busca por nome ou CPF
- [x] Lista de pacientes encontrados
- [x] Estatísticas por paciente
- [x] Histórico completo em timeline
- [x] Diferenciação visual de status
- [x] Cards detalhados para cada registro
- [x] Links para visualizar e editar
- [x] Destaque para urgentes
- [x] Exibição de observações
- [x] Rastreabilidade (quem solicitou/agendou)
- [x] Acesso restrito a administradores
- [x] Link no menu
- [x] Responsivo

---

## 📝 **POSSÍVEIS MELHORIAS FUTURAS**

### **Funcionalidades:**
- [ ] Exportar histórico para PDF
- [ ] Filtros na timeline (só agendados, só pendentes, etc.)
- [ ] Gráfico de evolução do paciente
- [ ] Busca por período de datas
- [ ] Estatísticas agregadas (tempo médio de espera)
- [ ] Ordenação diferente (por data agendamento, por médico)
- [ ] Paginação da timeline (se >50 registros)
- [ ] Busca por telefone também
- [ ] Marcação de registros favoritos

### **Visual:**
- [ ] Gráfico de linha do tempo visual
- [ ] Cores personalizadas por tipo de atendimento
- [ ] Impressão otimizada do histórico
- [ ] Modo de visualização compacto/expandido

---

## 🧪 **COMO TESTAR**

### **Teste 1: Busca de Paciente**
1. Faça login como administrador
2. Clique em "Pacientes" no menu
3. ✅ Página de busca abre
4. Digite "João" no campo de busca
5. Clique em "Buscar"
6. ✅ Lista de pacientes aparece

### **Teste 2: Ver Histórico**
1. Na lista de resultados
2. Clique em "Ver Histórico"
3. ✅ Abre página com timeline
4. ✅ Vê estatísticas no topo
5. ✅ Vê todos os registros em ordem

### **Teste 3: Badges de Status**
1. No histórico, observe os cards
2. ✅ Agendados têm badge verde
3. ✅ Pendentes têm badge amarelo
4. ✅ Urgentes têm badge vermelho pulsante

### **Teste 4: Editar do Histórico**
1. Clique no ícone ✏️ em um registro
2. ✅ Abre formulário de edição
3. Modifique algo
4. Salve
5. Volte ao histórico
6. ✅ Mudança refletida

### **Teste 5: Busca por CPF**
1. Digite CPF: "123.456.789-00"
2. ✅ Encontra paciente
3. Digite sem formatação: "12345678900"
4. ✅ Também encontra

### **Teste 6: Acesso Negado**
1. Faça login como atendente
2. ❌ Não vê "Pacientes" no menu
3. Digite URL: `/listaespera/pacientes.php`
4. ❌ Redirecionado
5. ✅ Mensagem de erro

---

## 🐛 **TROUBLESHOOTING**

### **Nenhum paciente encontrado:**
```
✅ Verificar se digitou nome/CPF correto
✅ Confirmar que paciente existe na fila_espera
✅ Tentar busca parcial (ex: "João" em vez de "João Silva")
```

### **Histórico vazio:**
```
✅ Verificar se CPF está correto na URL
✅ Confirmar que há registros para este CPF
✅ Ver Console (F12) para erros SQL
```

### **Timeline não aparece:**
```
✅ Verificar erros no Console
✅ Confirmar que há registros retornados
✅ Verificar permissões de banco
```

### **Estatísticas incorretas:**
```
✅ Verificar query SQL de agregação
✅ Confirmar campo 'agendado' no banco
✅ Ver dados retornados no debug
```

---

## 📈 **BENEFÍCIOS**

### **Para Administradores:**
- 👁️ **Visão completa** do histórico do paciente
- 📊 **Estatísticas rápidas** (total, agendados, pendentes)
- ⏱️ **Timeline visual** fácil de entender
- 🔍 **Busca rápida** por nome ou CPF
- ✏️ **Edição direta** de qualquer registro
- 🚨 **Identificação rápida** de urgências
- 📝 **Rastreabilidade** completa

### **Para o Hospital:**
- 📋 **Histórico centralizado** de atendimentos
- 🔐 **Seguro** - apenas admins acessam
- 📊 **Dados organizados** e fáceis de consultar
- ⚡ **Rápido** para localizar informações
- 📱 **Responsivo** - funciona em qualquer dispositivo

---

## 📚 **DEPENDÊNCIAS**

### **Já Incluídas:**
- ✅ Tailwind CSS (header.php)
- ✅ Font Awesome (header.php)
- ✅ PDO (database.php)
- ✅ Funções auxiliares (functions.php)

### **Modelos Utilizados:**
- `FilaEspera.php` - Acesso aos dados
- `AuthController.php` - Autenticação

---

## 📐 **LAYOUT RESPONSIVO**

### **Desktop (>768px):**
```
┌────────────────────────────────────┐
│ Estatísticas em 4 colunas         │
│ [Total] [Agend] [Pend] [Urgent]   │
└────────────────────────────────────┘

Timeline com cards largos
```

### **Mobile (<768px):**
```
┌──────────────┐
│ Estatísticas │
│ empilhadas   │
│ [Total]      │
│ [Agendados]  │
│ [Pendentes]  │
│ [Urgentes]   │
└──────────────┘

Timeline compacta
```

---

**Data**: 04/12/2024  
**Status**: ✅ Implementado e funcionando  
**Arquivos criados**:
- `pacientes.php`
- `paciente_historico.php`
- `HISTORICO_PACIENTES.md`

**Arquivo modificado**:
- `includes/header.php`

**Acesso**: 👑 Apenas Administradores  
**Tecnologias**: PHP, PDO, Tailwind CSS, Font Awesome
