# Autorização de Guia para Exames e Cirurgias

## 📋 Resumo da Funcionalidade

Sistema de controle de autorização de guias para **Exames**, **Cirurgias** e **Consulta + Exame**. 

### ⚠️ **Regra de Negócio Principal**
- **NÃO é possível agendar** exames ou cirurgias **SEM** a guia autorizada
- O sistema **bloqueia** o agendamento se a guia não estiver autorizada
- Validação obrigatória no momento de marcar como "Agendado"

---

## 🗂️ Arquivos Criados/Modificados

### 1. **SQL Migration** - `sql/adicionar_autorizacao_guia.sql`
Adiciona 3 novos campos à tabela `fila_espera`:

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `guia_autorizada` | BOOLEAN | NULL = não se aplica, FALSE = aguardando, TRUE = autorizada |
| `data_autorizacao_guia` | DATE | Data em que a guia foi autorizada |
| `observacao_guia` | TEXT | Número da guia, código de autorização, etc |

### 2. **Model** - `models/FilaEspera.php`
- ✅ Métodos `criar()` e `atualizar()` incluem os 3 novos campos
- ✅ Campos com bind correto (PARAM_INT para boolean)

### 3. **Formulário** - `fila_espera_form.php`
- ✅ **Validação Server-Side**: Bloqueia agendamento sem guia autorizada
- ✅ **Seção Destacada** (fundo amarelo) para autorização de guia
- ✅ **Exibição Condicional**: Só aparece para Exame/Cirurgia/Consulta + Exame
- ✅ **JavaScript Dinâmico**: Campos aparecem/desaparecem automaticamente
- ✅ **3 Campos**:
  - Status da Guia (Sim/Não)
  - Data de Autorização (condicional)
  - Observação (número da guia, etc)

### 4. **Dashboard** - `dashboard.php`
- ✅ **Indicador Visual** abaixo do chip de tipo de atendimento
- ✅ **3 Estados Possíveis**:
  - ⏳ **Aguardando Guia** (amarelo pulsante)
  - ✅ **Guia Autorizada** (verde)
  - ❓ **Não Informado** (cinza)

---

## 🚀 Como Usar

### **1. Execute a Migration SQL**

```bash
mysql -u usuario -p dema5738_lista_espera_hospital < sql/adicionar_autorizacao_guia.sql
```

Ou via phpMyAdmin:
```sql
USE dema5738_lista_espera_hospital;
source c:/xampp/htdocs/listaespera/sql/adicionar_autorizacao_guia.sql;
```

### **2. Cadastrar Exame/Cirurgia**

1. Acesse **Adicionar Paciente**
2. Preencha dados normais
3. Selecione **Tipo de Atendimento**: `Exame`, `Cirurgia` ou `Consulta + Exame`
4. 🎯 **Seção amarela aparece automaticamente**
5. Selecione status da guia:
   - **✅ Sim - Guia Autorizada**
   - **⏳ Não - Aguardando Autorização**
6. Se autorizada, informe a data e observações
7. Salve

### **3. Tentar Agendar SEM Guia Autorizada**

❌ **BLOQUEADO!** Mensagem de erro:
> "Não é possível agendar sem a guia autorizada. Aguarde a autorização da guia."

### **4. Agendar COM Guia Autorizada**

✅ **PERMITIDO!** Funciona normalmente

---

## 🎨 Visualização no Dashboard

### **Exame - Guia Autorizada**
```
┌─────────────────────────┐
│ 🟣 Exame                │
│ ✅ Guia Autorizada      │
└─────────────────────────┘
```

### **Cirurgia - Aguardando Guia**
```
┌─────────────────────────┐
│ 🔴 Cirurgia             │
│ ⏳ Aguardando Guia      │ (pulsante)
└─────────────────────────┘
```

### **Consulta + Exame - Não Informado**
```
┌─────────────────────────┐
│ 🟠 Consulta + Exame     │
│ ❓ Guia: Não informado  │
└─────────────────────────┘
```

---

## 📊 Fluxo de Trabalho

### **Cenário 1: Exame Comum**
1. Recepção cadastra paciente
2. Tipo: **Exame**
3. Guia: **⏳ Aguardando Autorização**
4. Observação: "Enviado para convênio em 04/12/2024"
5. Status: **NÃO Agendado**
6. ⏰ Aguarda autorização do convênio...
7. Convênio autoriza
8. Recepção edita registro
9. Guia: **✅ Autorizada**
10. Data Autorização: `05/12/2024`
11. Observação: "Guia nº 987654"
12. ✅ **Agora pode agendar!**

### **Cenário 2: Cirurgia Urgente**
1. Médico solicita cirurgia urgente
2. Tipo: **Cirurgia**
3. Urgente: **✅ SIM**
4. Guia: **✅ Autorizada** (pré-autorizada)
5. Data Autorização: `04/12/2024`
6. Observação: "Autorização verbal - confirmar nº"
7. ✅ **Pode agendar imediatamente!**

### **Cenário 3: Retorno (não requer guia)**
1. Tipo: **Retorno**
2. 🎯 **Seção de guia NÃO aparece**
3. ✅ **Agenda direto**, sem validação de guia

---

## ⚙️ Validações Implementadas

### **Server-Side (PHP)**

```php
// 1. Verifica se o tipo requer guia
$requerGuia = in_array($tipoAtendimento, ['Exame', 'Cirurgia', 'Consulta + Exame']);

// 2. Se tentar agendar SEM informar status da guia
if ($requerGuia && $agendado && !isset($_POST['guia_autorizada'])) {
    $erros[] = 'Para agendar exames ou cirurgias, é necessário informar se a guia está autorizada';
}

// 3. Se tentar agendar com guia NÃO autorizada
if ($requerGuia && $agendado && $_POST['guia_autorizada'] == '0') {
    $erros[] = 'Não é possível agendar sem a guia autorizada. Aguarde a autorização da guia.';
}
```

### **Client-Side (JavaScript)**

```javascript
// Mostra/oculta seção de guia conforme tipo de atendimento
function toggleCamposGuia() {
    const tiposQueRequeremGuia = ['Exame', 'Cirurgia', 'Consulta + Exame'];
    if (tiposQueRequeremGuia.includes(select.value)) {
        divAutorizacaoGuia.style.display = 'block';
    } else {
        divAutorizacaoGuia.style.display = 'none';
    }
}
```

---

## 📝 Campos da Seção de Autorização

### **1. Guia Autorizada?** (obrigatório se for agendar)
- **Tipo**: Select
- **Opções**:
  - `""` = Selecione
  - `"1"` = ✅ Sim - Guia Autorizada
  - `"0"` = ⏳ Não - Aguardando Autorização
- **Visual**: Borda amarela, fundo branco

### **2. Data da Autorização** (condicional)
- **Tipo**: Text (máscara DD/MM/AAAA)
- **Quando aparece**: Só se `guia_autorizada = 1`
- **Opcional**: Não obrigatório

### **3. Observação da Guia** (opcional)
- **Tipo**: Textarea
- **Exemplos**:
  - "Guia nº 123456"
  - "Código autorização: ABC-789"
  - "Autorizado por telefone - Dr. Silva"
- **Limite**: 2 linhas (expansível)

---

## 🎯 Tipos que Requerem Guia

| Tipo Atendimento | Requer Guia? | Cor |
|------------------|--------------|-----|
| Consulta | ❌ Não | 🔵 Azul |
| **Exame** | ✅ **SIM** | 🟣 Roxo |
| **Consulta + Exame** | ✅ **SIM** | 🟠 Laranja |
| Retorno | ❌ Não | 🟢 Verde-água |
| **Cirurgia** | ✅ **SIM** | 🔴 Vermelho |
| Procedimento | ❌ Não | 🩷 Rosa |

---

## 🔧 Exemplos de Uso

### **Exemplo 1: Exame de Sangue - Guia Pendente**
```
Tipo: Exame
Guia Autorizada: Não
Observação: "Enviado para Unimed em 04/12/2024"
Status: Não Agendado (não pode agendar ainda)
```

### **Exemplo 2: Ressonância - Guia Autorizada**
```
Tipo: Exame
Guia Autorizada: Sim
Data Autorização: 05/12/2024
Observação: "Guia nº 987654321 - Válida por 30 dias"
Status: ✅ Pode agendar
```

### **Exemplo 3: Cirurgia Cardíaca - Urgente**
```
Tipo: Cirurgia
Urgente: SIM
Motivo Urgência: "Obstrução coronária - risco de infarto"
Guia Autorizada: Sim
Data Autorização: 04/12/2024 (mesmo dia)
Observação: "Autorização emergencial - ref: URG-2024-1234"
Status: ✅ Pode agendar imediatamente
```

---

## 🚨 Mensagens de Erro

### **Erro 1: Tentar agendar sem informar status da guia**
```
❌ Para agendar exames ou cirurgias, é necessário informar se a guia está autorizada
```

### **Erro 2: Tentar agendar com guia não autorizada**
```
❌ Não é possível agendar sem a guia autorizada. Aguarde a autorização da guia.
```

---

## 📈 Benefícios

✅ **Controle Rigoroso**: Impossível agendar sem autorização  
✅ **Rastreabilidade**: Data e observações da autorização registradas  
✅ **Visual Claro**: Status da guia visível na listagem  
✅ **Workflow Correto**: Força o processo correto de autorização  
✅ **Redução de Erros**: Menos agendamentos cancelados por falta de guia  
✅ **Auditoria**: Histórico completo de autorizações  

---

## 🔍 Casos de Uso Especiais

### **Caso 1: Convênio Particular (sem guia)**
Se o paciente é particular e não precisa de guia:
- Marque como **✅ Guia Autorizada**
- Observação: "Particular - sem guia"
- Pode agendar normalmente

### **Caso 2: Guia Autorizada Parcialmente**
Se a guia foi autorizada mas com ressalvas:
- Marque como **✅ Guia Autorizada**
- Observação: "Autorizado apenas exame A e B, excluído C"
- Adicione na observação geral do paciente

### **Caso 3: Guia Vencida**
Se a guia já foi autorizada mas venceu:
- Edite o registro
- Altere para **⏳ Aguardando Autorização**
- Observação: "Guia anterior vencida - solicitada renovação"

---

## 📞 Suporte e Dúvidas

### **Problema: Seção de guia não aparece**
- Verifique se o tipo de atendimento é: Exame, Cirurgia ou Consulta + Exame
- Limpe cache do navegador (Ctrl + F5)

### **Problema: Não consigo agendar**
- Verifique se a guia está marcada como "✅ Autorizada"
- Se estiver "⏳ Aguardando", altere após receber autorização

### **Problema: Campo de data não aparece**
- O campo de data só aparece se guia_autorizada = "Sim"
- Selecione "✅ Sim - Guia Autorizada" primeiro

---

## 🎓 Treinamento da Equipe

### **Para Recepcionistas:**
1. Ao cadastrar exame/cirurgia, sempre verificar se há guia
2. Se não tiver, marcar como "⏳ Aguardando"
3. Anotar na observação quando foi enviada
4. Não tentar agendar até ter autorização

### **Para Autorizadores:**
1. Quando receber autorização, editar o registro
2. Alterar para "✅ Autorizada"
3. Informar data da autorização
4. Anotar número da guia na observação

### **Para Gestores:**
1. Filtrar registros aguardando guia
2. Acompanhar tempo médio de autorização
3. Identificar gargalos por convênio

---

**Implementado em**: 04/12/2024  
**Versão**: 1.0  
**Status**: ✅ Completo e Funcional  
**Autor**: Sistema Lista de Espera Hospital
