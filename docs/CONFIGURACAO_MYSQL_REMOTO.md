# 🌐 Configuração MySQL Remoto - Sistema de Lista de Espera

## ✅ Configuração Aplicada

O sistema foi configurado para conectar ao banco de dados MySQL **remoto** com as seguintes credenciais:

### Informações de Conexão

| Parâmetro | Valor |
|-----------|-------|
| **Host** | 186.209.113.107 |
| **Porta** | 3306 (padrão MySQL) |
| **Banco de Dados** | dema5738_lista_espera_hospital |
| **Usuário** | dema5738_lista_espera_hospital |
| **Senha** | Dema@1973 |

### Arquivo Configurado

O arquivo `config/database.php` foi atualizado com estas credenciais.

---

## 📋 Passos para Finalizar a Instalação

### 1. Criar as Tabelas no Banco Remoto

Você tem **3 opções** para criar as tabelas:

#### **Opção A: Via phpMyAdmin do Servidor Remoto** ✅ Recomendado

1. Acesse o phpMyAdmin do seu servidor de hospedagem
2. Faça login com suas credenciais
3. Selecione o banco `dema5738_lista_espera_hospital`
4. Clique na aba **"SQL"**
5. Abra o arquivo `sql/schema.sql` no bloco de notas
6. **Copie TODO o conteúdo** (começando de `CREATE DATABASE...`)
7. **Cole** na área de texto do phpMyAdmin
8. Clique em **"Executar"**
9. Aguarde a confirmação de sucesso

#### **Opção B: Via MySQL Workbench**

1. Abra o MySQL Workbench
2. Crie nova conexão:
   - **Hostname:** 186.209.113.107
   - **Port:** 3306
   - **Username:** dema5738_lista_espera_hospital
   - **Password:** Dema@1973
3. Conecte ao servidor
4. Abra o arquivo `sql/schema.sql`
5. Execute o script completo

#### **Opção C: Via Linha de Comando**

```bash
mysql -h 186.209.113.107 -u dema5738_lista_espera_hospital -p dema5738_lista_espera_hospital < sql/schema.sql
```
Quando solicitar a senha, digite: `Dema@1973`

---

### 2. Verificar a Conexão

Após criar as tabelas, teste a conexão:

1. Acesse: `http://localhost/listaespera`
2. Você deve ver a página de login
3. Se aparecer erro de conexão, verifique:
   - ✅ Firewall do servidor permite conexões na porta 3306
   - ✅ Usuário tem permissão de acesso remoto
   - ✅ Credenciais estão corretas

---

## 🔧 Configurações Importantes

### Permissões do Usuário MySQL

Certifique-se de que o usuário tem as seguintes permissões no banco:

```sql
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER 
ON dema5738_lista_espera_hospital.* 
TO 'dema5738_lista_espera_hospital'@'%';

FLUSH PRIVILEGES;
```

### Firewall

O servidor **186.209.113.107** deve permitir conexões TCP na porta **3306** do seu IP.

Se estiver tendo problemas de conexão, entre em contato com o administrador do servidor.

---

## 🔐 Segurança

### Recomendações:

1. ✅ **Conexão já configurada** com credenciais seguras
2. ⚠️ **Não compartilhe** as credenciais publicamente
3. ✅ **Backup regular** - Configure backups automáticos do banco
4. 🔒 **SSL/TLS** - Se possível, configure conexão SSL (opcional)

### Habilitar SSL (Opcional)

Se o servidor MySQL suportar SSL, você pode adicionar ao `database.php`:

```php
$this->conn = new PDO(
    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
    $this->username,
    $this->password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca-cert.pem', // Adicionar
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false   // Adicionar
    ]
);
```

---

## 🚨 Solução de Problemas

### Erro: "SQLSTATE[HY000] [2002] Connection timed out"

**Causa:** Firewall bloqueando a porta 3306 ou IP não autorizado.

**Solução:**
1. Verifique se o servidor permite acesso remoto
2. Adicione seu IP à lista de IPs autorizados no painel de controle
3. Contate o suporte da hospedagem

### Erro: "SQLSTATE[HY000] [1045] Access denied"

**Causa:** Credenciais incorretas ou usuário sem permissão de acesso remoto.

**Solução:**
1. Verifique as credenciais em `config/database.php`
2. Confirme que o usuário pode acessar de hosts remotos
3. Execute: `SELECT host FROM mysql.user WHERE user = 'dema5738_lista_espera_hospital';`

### Erro: "SQLSTATE[HY000] [2002] No such file or directory"

**Causa:** Tentando conectar via socket local em vez de TCP/IP.

**Solução:**
Altere a string de conexão para forçar TCP/IP:

```php
"mysql:host=" . $this->host . ";port=3306;dbname=" . $this->db_name . ";charset=utf8mb4"
```

---

## 📊 Verificar Tabelas Criadas

Após executar o `schema.sql`, verifique se as tabelas foram criadas:

```sql
SHOW TABLES FROM dema5738_lista_espera_hospital;
```

**Tabelas esperadas:**
- usuarios
- medicos
- especialidades
- convenios
- medico_especialidade
- fila_espera

**Total:** 6 tabelas

---

## 💾 Backup do Banco Remoto

### Via phpMyAdmin:
1. Acesse phpMyAdmin remoto
2. Selecione `dema5738_lista_espera_hospital`
3. Clique em **Exportar**
4. Formato: **SQL**
5. Execute

### Via Linha de Comando:
```bash
mysqldump -h 186.209.113.107 -u dema5738_lista_espera_hospital -p dema5738_lista_espera_hospital > backup_remoto.sql
```

---

## 🎯 Próximos Passos

1. ✅ **Execute o script SQL** no banco remoto
2. ✅ **Teste a conexão** acessando o sistema
3. ✅ **Faça login** com: admin@hospital.com / admin123
4. ✅ **Altere as senhas padrão** dos usuários
5. ✅ **Configure backup automático** (recomendado)

---

## 📞 Suporte

Se tiver problemas:

1. Verifique os logs de erro do Apache: `C:\xampp\apache\logs\error.log`
2. Ative display_errors no PHP para ver erros detalhados
3. Teste a conexão diretamente via MySQL Workbench ou Heidi SQL

---

**Sistema configurado para MySQL Remoto em 186.209.113.107**

Data da Configuração: 04/12/2025
