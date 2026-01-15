# 📅 Agenda Visual - FullCalendar.js

## ✅ **Implementação Concluída**

### **Calendário interativo de agendamentos exclusivo para administradores**

---

## 🎯 **O QUE FOI CRIADO**

### **1. Página da Agenda** (`agenda.php`)
- ✅ Calendário visual com FullCalendar.js
- ✅ Acesso restrito a administradores
- ✅ Visualização de agendamentos em diferentes formatos
- ✅ Modal com detalhes completos do paciente
- ✅ Cores por tipo de atendimento
- ✅ Destaque para casos urgentes

### **2. API de Eventos** (`api/agenda_eventos.php`)
- ✅ Endpoint JSON para fornecer eventos
- ✅ Busca apenas registros agendados
- ✅ Formatação de dados para FullCalendar
- ✅ Proteção por autenticação e perfil

### **3. Link no Menu** (`includes/header.php`)
- ✅ Botão "Agenda" visível apenas para admin
- ✅ Integrado ao menu de navegação

---

## 🎨 **RECURSOS VISUAIS**

### **📆 Visualizações Disponíveis:**

1. **Mês (dayGridMonth)** - Padrão
   - Visualização mensal completa
   - Eventos agrupados por dia

2. **Semana (timeGridWeek)**
   - Grade de horários por semana
   - Detalhamento dia a dia

3. **Dia (timeGridDay)**
   - Foco em um único dia
   - Ideal para dias com muitos agendamentos

4. **Lista (listMonth)**
   - Lista de eventos do mês
   - Formato de tabela

---

## 🎨 **CORES E LEGENDA**

### **Por Tipo de Atendimento:**

| Tipo | Cor | Código |
|------|-----|--------|
| **Consulta** | 🟢 Verde | `#10b981` |
| **Exame** | 🔵 Azul | `#3b82f6` |
| **Consulta + Exame** | 🟣 Roxo | `#8b5cf6` |
| **Retorno** | 🟡 Amarelo | `#eab308` |
| **Cirurgia** | 🔴 Vermelho | `#ef4444` |
| **Procedimento** | 🟠 Laranja | `#f97316` |

### **Casos Urgentes:**
- 🔴 **Vermelho Escuro** (`#b91c1c`)
- Prefixo "🚨 URGENTE" no tooltip
- Destaque visual no calendário

---

## 📊 **MODAL DE DETALHES**

Ao clicar em um evento, abre modal com:

### **Informações Exibidas:**
- ✅ Nome do Paciente
- ✅ Data do Agendamento
- ✅ Médico responsável
- ✅ Especialidade
- ✅ Tipo de Atendimento
- ✅ Convênio
- ✅ Telefone (formatado)
- ✅ CPF (formatado)
- ✅ Indicador de urgência (se aplicável)
- ✅ Motivo da urgência (se houver)
- ✅ Observações (se houver)

### **Ações no Modal:**
- 🔵 **Editar**: Vai para `fila_espera_form.php?id={id}`
- ⚪ **Fechar**: Fecha o modal

---

## 🔒 **SEGURANÇA**

### **Proteção em Múltiplas Camadas:**

#### **1. Página `agenda.php`:**
```php
// Verifica se é administrador
if ($usuarioLogado['perfil'] !== 'administrador') {
    $_SESSION['mensagem_erro'] = 'Acesso negado.';
    header('Location: /listaespera/dashboard.php');
    exit;
}
```

#### **2. API `api/agenda_eventos.php`:**
```php
// Verifica autenticação
$auth->verificarAutenticacao();

// Verifica se é administrador
if ($usuarioLogado['perfil'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}
```

#### **3. Menu `includes/header.php`:**
```php
<?php if ($usuario['perfil'] === 'administrador'): ?>
    <a href="/listaespera/agenda.php">Agenda</a>
<?php endif; ?>
```

---

## 💻 **CÓDIGO JAVASCRIPT**

### **Inicialização do FullCalendar:**

```javascript
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
    events: '/listaespera/api/agenda_eventos.php',
    eventClick: function(info) {
        mostrarDetalhes(info.event);
    }
});
```

### **Estrutura do Evento (JSON):**

```json
{
  "id": 123,
  "title": "João da Silva",
  "start": "2024-12-10",
  "color": "#10b981",
  "extendedProps": {
    "id": 123,
    "medico": "Dr. Carlos Souza",
    "especialidade": "Cardiologia",
    "convenio": "Unimed",
    "tipoAtendimento": "Consulta",
    "telefone": "(11) 98765-4321",
    "cpf": "123.456.789-00",
    "urgente": false,
    "motivoUrgencia": "",
    "observacoes": "Paciente com histórico...",
    "dataFormatada": "10/12/2024",
    "tooltip": "João da Silva - Consulta"
  }
}
```

---

## 📱 **RESPONSIVIDADE**

### **Desktop:**
```
┌─────────────────────────────────────────┐
│  ← Dezembro 2024 →    [Mês][Semana][Dia]│
├─────────────────────────────────────────┤
│ Dom Seg Ter Qua Qui Sex Sáb             │
│  1   2   3   4   5   6   7              │
│ [E] [E]     [E]                         │
│  8   9  10  11  12  13  14              │
│     [E] [E]                             │
└─────────────────────────────────────────┘
```

### **Mobile:**
- Calendário se ajusta automaticamente
- Botões de navegação empilham
- Modal ocupa 90% da tela

---

## 🔄 **FLUXO DE USO**

### **Fluxo 1: Visualizar Agenda**
1. Administrador faz login
2. ✅ Vê botão "Agenda" no menu
3. Clica em "Agenda"
4. ✅ Abre calendário com agendamentos
5. Navega entre meses/semanas

### **Fluxo 2: Ver Detalhes de Agendamento**
1. Na agenda, vê evento colorido
2. Passa mouse → ✅ Tooltip aparece
3. Clica no evento
4. ✅ Modal abre com todos os detalhes
5. Pode clicar em "Editar" para modificar

### **Fluxo 3: Atendente tenta acessar**
1. Atendente faz login
2. ❌ Não vê botão "Agenda" no menu
3. Se digitar URL manualmente
4. ❌ É redirecionado ao Dashboard
5. ✅ Vê mensagem "Acesso negado"

---

## 📊 **DADOS EXIBIDOS**

### **Fonte de Dados:**
- Tabela: `fila_espera`
- Filtro: `agendado = 1`
- Ordenação: Por `data_agendamento`

### **Campos Utilizados:**
```sql
SELECT 
    f.id,
    f.nome_paciente,
    f.data_agendamento,
    f.tipo_atendimento,
    f.urgente,
    f.motivo_urgencia,
    f.observacoes,
    f.telefone1,
    f.cpf,
    m.nome as medico_nome,
    e.nome as especialidade_nome,
    c.nome as convenio_nome
FROM fila_espera f
LEFT JOIN medicos m ON f.medico_id = m.id
LEFT JOIN especialidades e ON f.especialidade_id = e.id
LEFT JOIN convenios c ON f.convenio_id = c.id
WHERE f.agendado = 1
```

---

## 🎯 **FUNCIONALIDADES**

### **✅ Implementadas:**
- [x] Calendário visual com FullCalendar.js
- [x] Múltiplas visualizações (mês, semana, dia, lista)
- [x] Cores por tipo de atendimento
- [x] Destaque para urgentes
- [x] Modal com detalhes completos
- [x] Tooltip ao passar mouse
- [x] Botão de edição rápida
- [x] Localização em português (pt-br)
- [x] Acesso restrito a administradores
- [x] API segura com JSON
- [x] Link no menu apenas para admin

### **📝 Possíveis Melhorias Futuras:**
- [ ] Filtros por médico/especialidade/convênio
- [ ] Exportar agenda para PDF
- [ ] Imprimir calendário
- [ ] Arrastar e soltar para reagendar
- [ ] Adicionar evento diretamente do calendário
- [ ] Notificações de agendamentos próximos
- [ ] Visualização de disponibilidade
- [ ] Integração com Google Calendar

---

## 📁 **ESTRUTURA DE ARQUIVOS**

```
listaespera/
├── agenda.php                     ← Página principal da agenda
├── api/
│   └── agenda_eventos.php         ← API que retorna eventos JSON
├── includes/
│   └── header.php                 ← Menu com link da agenda
└── AGENDA_VISUAL.md               ← Esta documentação
```

---

## 🔧 **DEPENDÊNCIAS**

### **Bibliotecas Externas:**

#### **FullCalendar v6.1.10:**
```html
<!-- CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- JavaScript -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/pt-br.global.min.js'></script>
```

### **Já Incluídas:**
- ✅ Tailwind CSS (via CDN no header)
- ✅ Font Awesome (via CDN no header)

---

## 🎨 **INTERFACE**

### **Cabeçalho da Página:**
```
┌──────────────────────────────────────────────┐
│ 📅 Agenda Visual de Agendamentos             │
│ Visualize todos os agendamentos em calendário│
│                          [← Voltar Dashboard] │
└──────────────────────────────────────────────┘
```

### **Legenda:**
```
🟢 Consulta  🔵 Exame  🟣 Consulta + Exame
🟡 Retorno   🔴 Cirurgia  🟠 Procedimento
🔴 Urgente (com borda escura)
```

### **Calendário:**
```
┌──────────────────────────────────────────────┐
│  ←  Dezembro 2024  →   [Mês][Semana][Dia][Lista] │
├──────────────────────────────────────────────┤
│ Dom  Seg  Ter  Qua  Qui  Sex  Sáb           │
│  1    2    3    4    5    6    7            │
│ [João] [Maria]  [Pedro]                      │
│  8    9   10   11   12   13   14            │
│      [Ana] [Carlos]                          │
└──────────────────────────────────────────────┘
```

---

## 🧪 **COMO TESTAR**

### **Teste 1: Acesso como Administrador**
1. Faça login como administrador
2. ✅ Veja botão "Agenda" no menu
3. Clique em "Agenda"
4. ✅ Calendário abre com eventos
5. Clique em um evento
6. ✅ Modal abre com detalhes

### **Teste 2: Diferentes Visualizações**
1. Na agenda, clique em "Semana"
2. ✅ Visualização muda para semana
3. Clique em "Dia"
4. ✅ Visualização muda para dia
5. Clique em "Lista"
6. ✅ Mostra lista de eventos

### **Teste 3: Acesso como Atendente**
1. Faça login como atendente
2. ❌ Não vê botão "Agenda"
3. Digite URL: `/listaespera/agenda.php`
4. ❌ Redirecionado ao Dashboard
5. ✅ Mensagem de erro aparece

### **Teste 4: API Direta**
1. Faça login como atendente
2. Digite URL: `/listaespera/api/agenda_eventos.php`
3. ❌ Retorna erro 403
4. ✅ `{"error":"Acesso negado"}`

### **Teste 5: Modal de Detalhes**
1. Acesse agenda como admin
2. Clique em evento urgente
3. ✅ Ver banner vermelho de urgência
4. Clique em "Editar"
5. ✅ Vai para formulário de edição

---

## 📈 **BENEFÍCIOS**

### **Para Administradores:**
- 📅 **Visão geral** de todos os agendamentos
- 🎨 **Identificação rápida** por cores
- 🚨 **Destaque visual** para urgências
- 📊 **Diferentes perspectivas** (mês/semana/dia)
- ⚡ **Acesso rápido** aos detalhes
- 🔧 **Edição direta** do calendário

### **Para o Sistema:**
- 🔒 **Seguro** - Apenas admins acessam
- ⚡ **Rápido** - JSON via API
- 📱 **Responsivo** - Funciona em mobile
- 🌐 **Profissional** - Interface moderna
- 🔄 **Interativo** - FullCalendar.js

---

## 🐛 **TROUBLESHOOTING**

### **Calendário não aparece:**
```
✅ Verificar se CDN do FullCalendar está carregando
✅ Abrir Console (F12) e ver erros JavaScript
✅ Confirmar que está logado como administrador
```

### **Eventos não aparecem:**
```
✅ Verificar se há registros com agendado=1 no banco
✅ Testar API diretamente: /listaespera/api/agenda_eventos.php
✅ Ver Console do navegador (F12) → Network
```

### **Modal não abre:**
```
✅ Verificar erros no Console (F12)
✅ Confirmar que evento tem ID válido
✅ Testar função mostrarDetalhes() manualmente
```

### **Erro 403 na API:**
```
✅ Confirmar que está logado
✅ Verificar perfil: deve ser 'administrador'
✅ Limpar cache e fazer login novamente
```

---

## 📚 **DOCUMENTAÇÃO ADICIONAL**

### **FullCalendar Docs:**
- 📖 Documentação oficial: https://fullcalendar.io/docs
- 🎨 Demos: https://fullcalendar.io/demos
- 🔧 API: https://fullcalendar.io/docs/api

### **Personalizações Possíveis:**
- Alterar cores em `agenda_eventos.php`
- Modificar textos dos botões em `agenda.php`
- Ajustar layout do modal
- Adicionar mais campos ao evento

---

**Data**: 04/12/2024  
**Status**: ✅ Implementado e funcionando  
**Arquivos criados**:
- `agenda.php`
- `api/agenda_eventos.php`
- `AGENDA_VISUAL.md`

**Arquivo modificado**:
- `includes/header.php`

**Tecnologia**: FullCalendar.js v6.1.10  
**Acesso**: 👑 Apenas Administradores
