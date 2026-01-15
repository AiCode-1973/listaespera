# Sistema de Lista de Espera - Hospital

Sistema web completo para gerenciamento de fila de espera de pacientes para consultas e exames hospitalares.

## 📋 Descrição

Aplicação desenvolvida em PHP, MySQL, HTML e Tailwind CSS para controle eficiente de pacientes em lista de espera, com recursos de:

- ✅ Autenticação segura de usuários
- 👨‍⚕️ Cadastro de médicos e especialidades
- 🏥 Gerenciamento de convênios médicos
- 📊 Lista de espera com filtros avançados
- 📱 Interface responsiva e moderna
- 📄 Exportação de dados para CSV
- 🔒 Controle de permissões por perfil

## 🚀 Tecnologias

- **Backend:** PHP 7.4+
- **Banco de Dados:** MySQL 5.7+ / MariaDB
- **Frontend:** HTML5, Tailwind CSS 3.x, JavaScript
- **Servidor:** Apache (XAMPP/WAMP)

## 📦 Instalação

### Pré-requisitos

- XAMPP/WAMP instalado (ou Apache + PHP)
- PHP 7.4 ou superior
- Acesso ao MySQL (local ou remoto)
- Extensões PHP: PDO, pdo_mysql

### ⚙️ CONFIGURAÇÃO ATUAL: MySQL Remoto

**O sistema está configurado para conectar a um banco MySQL REMOTO:**

| Parâmetro | Valor |
|-----------|-------|
| Host | 186.209.113.107 |
| Banco | dema5738_lista_espera_hospital |
| Usuário | dema5738_lista_espera_hospital |

📘 **Veja instruções completas em:** `CONFIGURACAO_MYSQL_REMOTO.md`

### Passo a Passo

1. **Clone/Copie o projeto** para a pasta htdocs do XAMPP:
   ```
   C:\xampp\htdocs\listaespera\
   ```

2. **Importe o banco de dados no servidor REMOTO:**
   - Acesse o phpMyAdmin do seu servidor de hospedagem
   - Selecione o banco `dema5738_lista_espera_hospital`
   - Clique em "SQL" e execute o conteúdo de `sql/schema.sql`
   
   **OU se tiver acesso SSH:**
   ```bash
   mysql -h 186.209.113.107 -u dema5738_lista_espera_hospital -p < sql/schema.sql
   ```

3. **Teste a conexão:**
   - Acesse: `http://localhost/listaespera/testar_conexao.php`
   - Verifique se todos os testes passaram
   - ✅ Se OK, remova o arquivo `testar_conexao.php` por segurança

4. **Inicie o Apache** pelo painel do XAMPP
   - ⚠️ Não é necessário iniciar o MySQL local (usando banco remoto)

5. **Acesse o sistema:**
   ```
   http://localhost/listaespera
   ```

## 🔑 Credenciais de Acesso

O sistema vem com 3 usuários pré-cadastrados para teste:

| Perfil         | E-mail                  | Senha    |
|----------------|-------------------------|----------|
| Administrador  | admin@hospital.com      | admin123 |
| Recepção       | recepcao@hospital.com   | admin123 |
| Médico         | medico@hospital.com     | admin123 |

## 📖 Estrutura do Projeto

```
listaespera/
├── config/
│   └── database.php           # Configuração do banco de dados
├── controllers/
│   └── AuthController.php     # Autenticação e controle de acesso
├── models/
│   ├── Usuario.php            # Model de usuários
│   ├── Medico.php             # Model de médicos
│   ├── Especialidade.php      # Model de especialidades
│   ├── Convenio.php           # Model de convênios
│   └── FilaEspera.php         # Model da lista de espera
├── views/ (integrado nas páginas principais)
├── includes/
│   ├── header.php             # Cabeçalho comum
│   ├── footer.php             # Rodapé comum
│   └── functions.php          # Funções auxiliares
├── sql/
│   └── schema.sql             # Script de criação do banco
├── dashboard.php              # Página principal (lista de espera)
├── login.php                  # Página de login
├── logout.php                 # Logout
├── medicos.php                # Gerenciamento de médicos
├── especialidades.php         # Gerenciamento de especialidades
├── convenios.php              # Gerenciamento de convênios
├── fila_espera_form.php       # Formulário de cadastro/edição
├── exportar_csv.php           # Exportação de dados
└── index.php                  # Redirecionamento inicial
```

## 🎯 Funcionalidades Principais

### Dashboard (Lista de Espera)

- Visualização completa da fila de espera
- Filtros por: médico, especialidade, convênio, status, nome do paciente, período
- Paginação de registros (20 por página)
- Chips coloridos para identificação visual
- Exportação para CSV
- Ações: editar, visualizar, adicionar

### Cadastro de Pacientes

- Informações completas do paciente
- Validação de CPF
- Formatação automática de telefones e datas
- Marcação de agendamento
- Observações e informações adicionais

### Gestão de Médicos

- Cadastro completo com CRM/CPF
- Associação com múltiplas especialidades
- Status ativo/inativo
- Não permite exclusão física (apenas inativação)

### Especialidades e Convênios

- CRUD completo
- Configuração de cores para chips
- Preview das cores na listagem

## 🔐 Níveis de Acesso

### Administrador
- Acesso total ao sistema
- Pode criar, editar e excluir todos os registros
- Gerencia usuários (futuro)
- Acesso a relatórios

### Recepção/Agendador
- Gerencia lista de espera (CRUD)
- Visualiza cadastros auxiliares
- Não pode excluir médicos/especialidades/convênios

### Médico
- Visualiza apenas pacientes associados a ele
- Consulta informações da lista de espera

## 🛡️ Segurança

- **Senhas:** Hash com `password_hash()` (bcrypt)
- **SQL Injection:** Prevenção via Prepared Statements (PDO)
- **XSS:** Sanitização de outputs com `htmlspecialchars()`
- **Sessões:** Regeneração de ID após login
- **Validações:** Client-side (JavaScript) e Server-side (PHP)

## 📊 Banco de Dados

### Tabelas Principais

- `usuarios` - Usuários do sistema
- `medicos` - Médicos cadastrados
- `especialidades` - Especialidades médicas
- `convenios` - Convênios de saúde
- `medico_especialidade` - Relacionamento N:N
- `fila_espera` - Lista de espera (tabela principal)

### Dados de Exemplo

O sistema vem com:
- 3 usuários
- 5 médicos
- 7 especialidades
- 5 convênios
- 8 pacientes na fila de espera

## 🎨 Interface

- **Design:** Moderno e limpo com Tailwind CSS
- **Cores:** Azul (principal), chips coloridos para categorias
- **Responsividade:** Desktop-first, adaptável a tablets
- **Ícones:** Font Awesome 6.4.0
- **UX:** Hover effects, máscaras de input, validações visuais

## 📝 Customização

### Alterar Cores dos Chips

Edite as especialidades/convênios e escolha entre as cores disponíveis no Tailwind:
- bg-blue-200, bg-purple-200, bg-red-200, bg-green-200, etc.

### Adicionar Novos Campos

1. Adicione a coluna no banco (ALTER TABLE)
2. Atualize o Model correspondente
3. Adicione o campo no formulário
4. Implemente validações

### Modificar Registros por Página

No `dashboard.php`, altere:
```php
$registrosPorPagina = 20; // Altere para o valor desejado
```

## 🐛 Solução de Problemas

### Erro de Conexão com Banco

- Verifique se MySQL está rodando
- Confira credenciais em `config/database.php`
- Certifique-se que o banco foi criado

### Página em Branco

- Ative display_errors no php.ini
- Verifique logs do Apache
- Confirme que todas as extensões PHP estão ativas

### Erro de Permissão

- Verifique permissões da pasta (755 para pastas, 644 para arquivos)
- No Linux/Mac: `chmod -R 755 /caminho/para/listaespera`

## 📧 Suporte

Para dúvidas ou problemas:
- Verifique a documentação inline no código
- Revise os comentários nos arquivos PHP
- Consulte o arquivo `schema.sql` para estrutura do banco

## 📄 Licença

Este projeto é de código aberto para fins educacionais e de demonstração.

---

**Desenvolvido com ❤️ para gerenciamento hospitalar eficiente**
