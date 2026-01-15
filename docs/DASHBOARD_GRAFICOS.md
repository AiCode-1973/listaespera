# 📊 Dashboard de Gráficos e Estatísticas

## 📋 Visão Geral

Dashboard interativo com gráficos dinâmicos usando **Chart.js** para visualização de dados da fila de espera.

**Acesso:** Exclusivo para **Administradores**

---

## 🎯 Funcionalidades

### 📈 Estatísticas Resumidas (Cards)

**4 Cards Principais:**

1. **Total de Registros**
   - Contador total de registros na fila
   - Ícone: Lista
   - Cor: Azul

2. **Agendados**
   - Total de agendamentos confirmados
   - Percentual do total
   - Ícone: Check
   - Cor: Verde

3. **Pendentes**
   - Total aguardando agendamento
   - Percentual do total
   - Ícone: Relógio
   - Cor: Amarelo

4. **Urgentes**
   - Total marcados como urgentes
   - Percentual do total
   - Ícone: Alerta
   - Cor: Vermelho

### ⏱️ Tempo Médio de Espera

**Card Especial:**
- Calcula média de dias entre solicitação e agendamento
- Apenas para registros com agendamento confirmado
- Destaque visual em gradiente roxo

---

## 📊 Gráficos Implementados

### 1. **Status Geral** (Pizza)
- **Tipo:** Gráfico de Pizza
- **Dados:** Agendados, Pendentes, Urgentes
- **Cores:** Verde, Amarelo, Vermelho
- **Tooltip:** Mostra quantidade e percentual

### 2. **Evolução Mensal** (Linha)
- **Tipo:** Gráfico de Linha
- **Período:** Últimos 6 meses
- **Séries:**
  - Agendados (Verde)
  - Pendentes (Amarelo)
- **Características:** Preenchimento suave, tensão 0.4

### 3. **Top 10 Especialidades** (Barra Horizontal Empilhada)
- **Tipo:** Barra Horizontal
- **Ranking:** 10 especialidades com mais registros
- **Séries:**
  - Agendados (Verde)
  - Pendentes (Amarelo)
- **Empilhado:** Sim

### 4. **Tipo de Atendimento** (Rosca)
- **Tipo:** Gráfico de Rosca (Doughnut)
- **Dados:** Consulta, Exame, Cirurgia, etc.
- **Cores:** Paleta multicolorida
- **Visual:** Moderno com espaço central

### 5. **Top 10 Médicos** (Barra Vertical)
- **Tipo:** Barra Vertical
- **Ranking:** 10 médicos com mais atendimentos
- **Dados:** Total de atendimentos por médico
- **Cor:** Azul

### 6. **Top 10 Convênios** (Barra Horizontal)
- **Tipo:** Barra Horizontal
- **Ranking:** 10 convênios mais utilizados
- **Dados:** Total por convênio
- **Cor:** Roxo

---

## 🎨 Design e Interface

### Layout Responsivo
```
┌─────────────────────────────────────────┐
│  📊 Dashboard de Análises               │
│  (Gradiente Azul → Roxo)                │
├─────────────────────────────────────────┤
│  [Card 1] [Card 2] [Card 3] [Card 4]   │
├─────────────────────────────────────────┤
│  [Tempo Médio de Espera: X dias]       │
├─────────────────────────────────────────┤
│  [Gráfico 1]  │  [Gráfico 2]           │
│  [Gráfico 3]  │  [Gráfico 4]           │
│  [Gráfico 5]  │  [Gráfico 6]           │
└─────────────────────────────────────────┘
```

### Paleta de Cores
```javascript
const cores = {
    agendado: '#10b981',   // Verde
    pendente: '#f59e0b',   // Amarelo
    urgente: '#ef4444',    // Vermelho
    azul: '#3b82f6',       // Azul
    roxo: '#8b5cf6',       // Roxo
    rosa: '#ec4899'        // Rosa
};
```

### Elementos Visuais
- Cards com borda lateral colorida (border-left-4)
- Ícones FontAwesome
- Sombras suaves
- Animações nas interações
- Tooltips informativos

---

## 🔧 Tecnologias Utilizadas

### Frontend
- **Chart.js v4.4.0** - Biblioteca de gráficos
- **Tailwind CSS** - Framework CSS
- **FontAwesome** - Ícones

### Backend
- **PHP 7.4+** - Processamento server-side
- **MySQL** - Banco de dados
- **PDO** - Conexão com banco

---

## 📊 Queries SQL

### 1. Estatísticas Gerais
```sql
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as total_agendados,
    SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as total_pendentes,
    SUM(CASE WHEN urgente = 1 THEN 1 ELSE 0 END) as total_urgentes
FROM fila_espera
```

### 2. Evolução Mensal
```sql
SELECT 
    DATE_FORMAT(data_solicitacao, '%Y-%m') as mes,
    SUM(CASE WHEN agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera
WHERE data_solicitacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(data_solicitacao, '%Y-%m')
ORDER BY mes ASC
```

### 3. Por Especialidade (Top 10)
```sql
SELECT 
    e.nome as especialidade,
    COUNT(*) as total,
    SUM(CASE WHEN f.agendado = 1 THEN 1 ELSE 0 END) as agendados,
    SUM(CASE WHEN f.agendado = 0 THEN 1 ELSE 0 END) as pendentes
FROM fila_espera f
LEFT JOIN especialidades e ON f.especialidade_id = e.id
GROUP BY f.especialidade_id, e.nome
ORDER BY total DESC
LIMIT 10
```

### 4. Tempo Médio de Espera
```sql
SELECT 
    AVG(DATEDIFF(data_agendamento, data_solicitacao)) as media_dias
FROM fila_espera
WHERE agendado = 1 
AND data_agendamento IS NOT NULL 
AND data_solicitacao IS NOT NULL
```

---

## 🔐 Segurança

### Controle de Acesso
```php
// Verifica se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /listaespera/login.php');
    exit;
}

// Verifica se é administrador
if ($_SESSION['perfil'] !== 'administrador') {
    $_SESSION['mensagem_erro'] = 'Acesso negado.';
    header('Location: /listaespera/dashboard.php');
    exit;
}
```

### Menu
- Link visível **APENAS** para administradores
- Posicionado entre "Agenda" e "Pacientes"

---

## 📱 Responsividade

### Desktop (≥ 1024px)
- Grid 2 colunas para gráficos
- Grid 4 colunas para cards
- Todos os gráficos visíveis

### Tablet (768px - 1023px)
- Grid 2 colunas para cards
- Grid 1 coluna para gráficos
- Scroll vertical

### Mobile (< 768px)
- Grid 1 coluna para tudo
- Cards empilhados
- Gráficos responsivos

---

## 🎯 Casos de Uso

### 1. Análise de Performance
- Ver quantos agendamentos foram realizados
- Identificar gargalos (muitos pendentes)
- Monitorar urgências

### 2. Gestão de Recursos
- Médicos com mais demanda
- Especialidades mais solicitadas
- Convênios mais utilizados

### 3. Tendências
- Crescimento ou queda de solicitações
- Sazonalidade nos agendamentos
- Tempo médio de espera

### 4. Tomada de Decisão
- Contratar mais médicos de especialidades demandadas
- Negociar com convênios mais ativos
- Priorizar redução do tempo de espera

---

## 🚀 Como Usar

### Acesso
1. Faça login como **administrador**
2. No menu superior, clique em **"Gráficos"**
3. Dashboard carrega automaticamente

### Interação com Gráficos
- **Hover:** Passe o mouse sobre pontos/barras para ver detalhes
- **Legenda:** Clique nos itens para mostrar/ocultar séries
- **Zoom:** Alguns gráficos permitem zoom (clique + arraste)

### Atualização de Dados
- Dados são carregados em tempo real ao acessar
- Para atualizar: Recarregue a página (F5)
- Timestamp de atualização no cabeçalho

---

## 📈 Métricas Importantes

### KPIs Principais
1. **Taxa de Agendamento:** (Agendados / Total) × 100
2. **Taxa de Pendência:** (Pendentes / Total) × 100
3. **Taxa de Urgência:** (Urgentes / Total) × 100
4. **Tempo Médio de Espera:** Dias entre solicitação e agendamento

### Metas Sugeridas
- Taxa de Agendamento: > 80%
- Taxa de Pendência: < 20%
- Tempo Médio de Espera: < 7 dias
- Taxa de Urgência: < 10%

---

## 🔄 Atualizações Futuras Sugeridas

### Funcionalidades
- [ ] Filtro por período personalizado
- [ ] Exportar gráficos como imagem (PNG/PDF)
- [ ] Comparação ano a ano
- [ ] Alertas automáticos (muitos pendentes)
- [ ] Refresh automático a cada X minutos
- [ ] Dashboard personalizado por usuário

### Novos Gráficos
- [ ] Mapa de calor por dia da semana
- [ ] Funil de conversão (solicitação → agendamento)
- [ ] Tempo de espera por especialidade
- [ ] Taxa de cancelamento
- [ ] Satisfação do paciente (se houver dados)

### Otimizações
- [ ] Cache de dados (Redis/Memcached)
- [ ] Lazy loading de gráficos
- [ ] Paginação para gráficos com muitos dados
- [ ] Compressão de dados JSON

---

## 🐛 Troubleshooting

### Problema: Gráficos não aparecem
**Solução:**
1. Verifique console do navegador (F12)
2. Confirme que Chart.js está carregando
3. Verifique queries SQL no backend

### Problema: Dados incorretos
**Solução:**
1. Verifique filtros SQL
2. Confirme formato de datas
3. Teste queries diretamente no MySQL

### Problema: Lentidão ao carregar
**Solução:**
1. Adicione índices nas colunas usadas em GROUP BY
2. Limite resultados com LIMIT
3. Considere cache de queries

---

## 📚 Referências

### Documentação
- [Chart.js Docs](https://www.chartjs.org/docs/)
- [Chart.js Examples](https://www.chartjs.org/samples/)
- [Tailwind CSS](https://tailwindcss.com/docs)

### Exemplos de Gráficos
- Bar Chart: https://www.chartjs.org/docs/latest/charts/bar.html
- Line Chart: https://www.chartjs.org/docs/latest/charts/line.html
- Pie/Doughnut: https://www.chartjs.org/docs/latest/charts/doughnut.html

---

## ✅ Checklist de Implementação

- [x] Criar arquivo `dashboard_graficos.php`
- [x] Adicionar link no menu (apenas admin)
- [x] Implementar queries SQL
- [x] Criar 6 gráficos diferentes
- [x] Adicionar cards de estatísticas
- [x] Implementar tempo médio de espera
- [x] Design responsivo
- [x] Controle de acesso
- [x] Documentação completa

---

**Dashboard de Gráficos v1.0 - Sistema de Lista de Espera**  
_Desenvolvido para análise visual de dados e tomada de decisões_

