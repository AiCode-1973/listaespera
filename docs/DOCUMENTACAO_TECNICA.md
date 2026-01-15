# 📚 Documentação Técnica - Sistema de Lista de Espera

## Arquitetura do Sistema

### Padrão de Desenvolvimento
- **Arquitetura:** MVC Simplificado (Model-View-Controller)
- **Backend:** PHP Procedural/OOP Híbrido
- **Frontend:** HTML5 + Tailwind CSS + JavaScript Vanilla
- **Banco de Dados:** MySQL com PDO

### Segurança Implementada

| Camada | Proteção | Implementação |
|--------|----------|---------------|
| Autenticação | Hash de senhas | `password_hash()` bcrypt |
| SQL | Injection Prevention | PDO Prepared Statements |
| XSS | Output Sanitization | `htmlspecialchars()` |
| Sessão | Session Hijacking | `session_regenerate_id()` |
| CSRF | Token validation | Função `verificarTokenCSRF()` |

## 📂 Estrutura de Arquivos Detalhada

```
listaespera/
│
├── config/
│   └── database.php              # PDO connection class
│
├── controllers/
│   └── AuthController.php        # Login, logout, permissions
│
├── models/
│   ├── Usuario.php               # User authentication & management
│   ├── Medico.php                # Doctor CRUD + specialties N:N
│   ├── Especialidade.php         # Medical specialties CRUD
│   ├── Convenio.php              # Health insurance CRUD
│   └── FilaEspera.php            # Waiting list main logic
│
├── includes/
│   ├── header.php                # Common header with navbar
│   ├── footer.php                # Common footer with scripts
│   └── functions.php             # Helper functions (format, validate)
│
├── sql/
│   └── schema.sql                # Complete DDL + sample data
│
├── index.php                     # Entry point (redirects)
├── login.php                     # Login page
├── logout.php                    # Logout handler
├── dashboard.php                 # Main waiting list view
├── fila_espera_form.php          # Add/Edit patient form
├── fila_espera_view.php          # View patient details
├── medicos.php                   # Doctors management
├── especialidades.php            # Specialties management
├── convenios.php                 # Insurance management
├── exportar_csv.php              # CSV export handler
├── .htaccess                     # Apache security configs
├── README.md                     # User documentation
├── INSTALACAO.md                 # Installation guide
└── DOCUMENTACAO_TECNICA.md       # This file
```

## 🔌 Endpoints e Rotas

### Autenticação

| Arquivo | Método | Parâmetros | Descrição |
|---------|--------|------------|-----------|
| `login.php` | GET | - | Exibe formulário de login |
| `login.php` | POST | email, senha | Processa autenticação |
| `logout.php` | GET | - | Destrói sessão e redireciona |

### Lista de Espera (Principal)

| Arquivo | Método | Parâmetros | Descrição |
|---------|--------|------------|-----------|
| `dashboard.php` | GET | Filtros opcionais | Lista pacientes com paginação |
| `fila_espera_form.php` | GET | id (opcional) | Formulário add/edit |
| `fila_espera_form.php` | POST | Dados do paciente | Cria/atualiza registro |
| `fila_espera_view.php` | GET | id | Visualiza detalhes do paciente |
| `exportar_csv.php` | GET | Filtros (opcional) | Exporta lista para CSV |

**Filtros disponíveis no dashboard:**
- `medico_id` - Filtrar por médico
- `especialidade_id` - Filtrar por especialidade
- `convenio_id` - Filtrar por convênio
- `agendado` - Status (0=não, 1=sim)
- `nome_paciente` - Busca por nome
- `data_inicio` - Data solicitação início (DD/MM/AAAA)
- `data_fim` - Data solicitação fim (DD/MM/AAAA)
- `pagina` - Número da página (default: 1)

### Médicos

| Arquivo | Método | Parâmetros | Descrição |
|---------|--------|------------|-----------|
| `medicos.php` | GET | - | Lista médicos |
| `medicos.php` | GET | acao=editar, id | Preenche form para edição |
| `medicos.php` | GET | acao=inativar, id | Inativa médico |
| `medicos.php` | POST | Dados do médico | Cria/atualiza médico |

### Especialidades

| Arquivo | Método | Parâmetros | Descrição |
|---------|--------|------------|-----------|
| `especialidades.php` | GET | - | Lista especialidades |
| `especialidades.php` | GET | acao=editar, id | Preenche form para edição |
| `especialidades.php` | GET | acao=deletar, id | Deleta especialidade |
| `especialidades.php` | POST | nome, cor, id? | Cria/atualiza |

### Convênios

| Arquivo | Método | Parâmetros | Descrição |
|---------|--------|------------|-----------|
| `convenios.php` | GET | - | Lista convênios |
| `convenios.php` | GET | acao=editar, id | Preenche form para edição |
| `convenios.php` | GET | acao=deletar, id | Deleta convênio |
| `convenios.php` | POST | nome, codigo, cor, id? | Cria/atualiza |

## 🗄️ Schema do Banco de Dados

### Tabela: `usuarios`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT PK AI | ID do usuário |
| nome | VARCHAR(100) | Nome completo |
| email | VARCHAR(100) UNIQUE | E-mail (login) |
| senha_hash | VARCHAR(255) | Senha hasheada |
| perfil | ENUM | administrador, recepcao, medico |
| ativo | BOOLEAN | Status |
| created_at | TIMESTAMP | Data criação |
| updated_at | TIMESTAMP | Data atualização |

### Tabela: `medicos`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT PK AI | ID do médico |
| nome | VARCHAR(100) | Nome completo |
| crm_cpf | VARCHAR(20) UNIQUE | CRM ou CPF |
| telefone | VARCHAR(20) | Telefone |
| email | VARCHAR(100) | E-mail |
| ativo | BOOLEAN | Status |
| created_at | TIMESTAMP | Data criação |
| updated_at | TIMESTAMP | Data atualização |

### Tabela: `especialidades`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT PK AI | ID da especialidade |
| nome | VARCHAR(100) UNIQUE | Nome |
| cor | VARCHAR(50) | Classe Tailwind (ex: bg-blue-200) |
| created_at | TIMESTAMP | Data criação |
| updated_at | TIMESTAMP | Data atualização |

### Tabela: `convenios`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT PK AI | ID do convênio |
| nome | VARCHAR(100) UNIQUE | Nome |
| codigo | VARCHAR(50) | Código interno |
| cor | VARCHAR(50) | Classe Tailwind |
| created_at | TIMESTAMP | Data criação |
| updated_at | TIMESTAMP | Data atualização |

### Tabela: `medico_especialidade` (N:N)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT PK AI | ID do relacionamento |
| medico_id | INT FK | Referência médicos(id) |
| especialidade_id | INT FK | Referência especialidades(id) |
| created_at | TIMESTAMP | Data criação |

**Constraint:** UNIQUE(medico_id, especialidade_id)

### Tabela: `fila_espera` (Principal)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT PK AI | ID do registro |
| medico_id | INT FK | Referência médicos(id) |
| especialidade_id | INT FK | Referência especialidades(id) |
| convenio_id | INT FK NULL | Referência convenios(id) |
| nome_paciente | VARCHAR(150) | Nome do paciente |
| cpf | VARCHAR(14) | CPF (com formatação) |
| data_nascimento | DATE | Data nascimento |
| data_solicitacao | DATE | Data solicitação |
| informacao | VARCHAR(100) | Tipo (consulta, exame, etc) |
| observacao | TEXT | Observações |
| agendado | BOOLEAN | Se foi agendado |
| data_agendamento | DATE NULL | Data do agendamento |
| telefone1 | VARCHAR(20) | Telefone principal |
| telefone2 | VARCHAR(20) NULL | Telefone secundário |
| agendado_por | VARCHAR(100) NULL | Nome do usuário que agendou |
| created_at | TIMESTAMP | Data criação |
| updated_at | TIMESTAMP | Data atualização |

## 🔧 Classes e Métodos

### Database (`config/database.php`)

```php
class Database {
    public function getConnection(): PDO
}
```

### AuthController (`controllers/AuthController.php`)

```php
class AuthController {
    public function login($email, $senha): array
    public function logout(): void
    public function verificarAutenticacao(): void
    public function verificarPermissao($perfis): void
    public function getUsuarioLogado(): array
    public function isAdmin(): bool
    public function isRecepcao(): bool
    public function isMedico(): bool
}
```

### Usuario (`models/Usuario.php`)

```php
class Usuario {
    public function autenticar($email, $senha): array|false
    public function buscarPorId($id): array|false
    public function listar($filtros = []): array
    public function criar($dados): int|false
    public function atualizar($id, $dados): bool
    public function emailExiste($email, $excluirId = null): bool
}
```

### Medico (`models/Medico.php`)

```php
class Medico {
    public function listar($filtros = []): array
    public function buscarPorId($id): array|false
    public function criar($dados): int|false
    public function atualizar($id, $dados): bool
    public function buscarEspecialidades($medicoId): array
    public function crmCpfExiste($crmCpf, $excluirId = null): bool
    public function inativar($id): bool
}
```

### Especialidade (`models/Especialidade.php`)

```php
class Especialidade {
    public function listar($busca = ''): array
    public function buscarPorId($id): array|false
    public function criar($dados): int|false
    public function atualizar($id, $dados): bool
    public function deletar($id): bool
    public function nomeExiste($nome, $excluirId = null): bool
    public function buscarPorMedico($medicoId): array
}
```

### Convenio (`models/Convenio.php`)

```php
class Convenio {
    public function listar($busca = ''): array
    public function buscarPorId($id): array|false
    public function criar($dados): int|false
    public function atualizar($id, $dados): bool
    public function deletar($id): bool
    public function nomeExiste($nome, $excluirId = null): bool
}
```

### FilaEspera (`models/FilaEspera.php`)

```php
class FilaEspera {
    public function listar($filtros = [], $limit = 20, $offset = 0): array
    public function contar($filtros = []): int
    public function buscarPorId($id): array|false
    public function criar($dados): int|false
    public function atualizar($id, $dados): bool
    public function marcarAgendado($id, $dataAgendamento, $agendadoPor): bool
    public function deletar($id): bool
    public function verificarDuplicidade($cpf, $medicoId, $dataSolicitacao, $excluirId = null): bool
    public function exportar($filtros = []): array
}
```

## 🛠️ Funções Auxiliares (`includes/functions.php`)

| Função | Parâmetros | Retorno | Descrição |
|--------|------------|---------|-----------|
| `formatarCPF()` | string $cpf | string | Formata CPF XXX.XXX.XXX-XX |
| `limparCPF()` | string $cpf | string | Remove formatação |
| `validarCPF()` | string $cpf | bool | Valida dígitos verificadores |
| `formatarData()` | string $data | string | YYYY-MM-DD → DD/MM/YYYY |
| `converterDataBanco()` | string $data | string | DD/MM/YYYY → YYYY-MM-DD |
| `formatarTelefone()` | string $tel | string | Formata telefone |
| `sanitizar()` | string $str | string | Previne XSS |
| `gerarClasseChip()` | string $cor | string | Gera classes Tailwind |
| `exibirAlerta()` | string $tipo, string $msg | string | HTML de alerta |
| `redirecionar()` | string $url | void | Redireciona e exit() |
| `verificarLogin()` | - | void | Verifica se está logado |
| `verificarPermissao()` | array $perfis | void | Verifica perfil |
| `getUsuarioLogado()` | - | array | Retorna dados do usuário |
| `paginar()` | int $total, int $perPage, int $page | array | Calcula paginação |
| `gerarTokenCSRF()` | - | string | Gera token CSRF |
| `verificarTokenCSRF()` | string $token | bool | Valida token |

## 🎨 Classes CSS Customizadas

### Chips (Badges)
```css
.chip {
    @apply px-3 py-1 rounded-full text-xs font-semibold inline-block;
}
```

**Uso:**
```html
<span class="chip bg-blue-200 text-blue-800">Texto</span>
```

### Tabela com Hover
```css
.table-hover tbody tr:hover {
    @apply bg-gray-100 transition-colors duration-150;
}
```

## 🔐 Níveis de Permissão

| Perfil | Dashboard | Add/Edit Fila | Médicos | Especialidades | Convênios | Deletar |
|--------|-----------|---------------|---------|----------------|-----------|---------|
| **Administrador** | ✅ Todos | ✅ Sim | ✅ Todos | ✅ CRUD completo | ✅ CRUD completo | ✅ Sim |
| **Recepção** | ✅ Todos | ✅ Sim | ✅ Visualizar | ✅ Visualizar | ✅ Visualizar | ❌ Não |
| **Médico** | ✅ Seus pacientes | ❌ Não | ❌ Não | ❌ Não | ❌ Não | ❌ Não |

## 📊 Fluxo de Dados

### Login
```
1. Usuário acessa login.php
2. Submete email + senha
3. AuthController->login()
4. Usuario->autenticar()
5. password_verify()
6. Cria sessão com dados do usuário
7. Redireciona para dashboard.php
```

### Adicionar Paciente
```
1. Usuário acessa fila_espera_form.php
2. Preenche formulário
3. Validações client-side (JS)
4. POST para fila_espera_form.php
5. Validações server-side
6. FilaEspera->criar($dados)
7. Prepared statement INSERT
8. Redireciona com mensagem de sucesso
```

### Filtrar Lista
```
1. Usuário define filtros no dashboard
2. GET com parâmetros de filtro
3. FilaEspera->listar($filtros, $limit, $offset)
4. SQL com WHERE dinâmico
5. Retorna array de registros
6. Loop no PHP para renderizar tabela
```

## 🧪 Testes Sugeridos

### Funcionais
- [ ] Login com credenciais corretas
- [ ] Login com credenciais incorretas
- [ ] Adicionar paciente com todos os campos
- [ ] Adicionar paciente com campos obrigatórios mínimos
- [ ] CPF inválido deve ser rejeitado
- [ ] Filtros devem reduzir lista
- [ ] Paginação deve funcionar
- [ ] Exportar CSV deve baixar arquivo
- [ ] Editar paciente deve manter dados
- [ ] Marcar como agendado deve exigir data

### Segurança
- [ ] Acesso sem login redireciona para login.php
- [ ] SQL injection é bloqueado (testar com: ' OR '1'='1)
- [ ] XSS é bloqueado (testar com: <script>alert('xss')</script>)
- [ ] Recepção não pode deletar especialidades
- [ ] Médico vê apenas seus pacientes

### Performance
- [ ] Dashboard com 100+ registros carrega em < 2s
- [ ] Filtros respondem rapidamente
- [ ] Exportação CSV não trava

## 🔄 Extensões Futuras

### Sugeridas
1. **Relatórios PDF** - Usar TCPDF ou mPDF
2. **Dashboard com gráficos** - Chart.js
3. **Notificações por e-mail** - PHPMailer
4. **API REST** - Para app mobile
5. **Histórico de alterações** - Audit log
6. **Busca avançada** - Elasticsearch
7. **Agenda visual** - FullCalendar.js

---

**Documentação gerada para Sistema de Lista de Espera v1.0**
