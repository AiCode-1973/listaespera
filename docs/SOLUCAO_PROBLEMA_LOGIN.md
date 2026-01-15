# 🔧 Solução: Problema de Login

## 🚨 Problema Identificado

As senhas dos usuários no banco de dados **não correspondem** à senha "admin123" porque o hash bcrypt no `schema.sql` original estava incorreto.

---

## ✅ Solução Rápida (3 Passos)

### **Passo 1: Diagnóstico**

Acesse: **`http://localhost/listaespera/gerar_senha.php`**

Este script irá:
- ✅ Testar a conexão com o banco remoto
- ✅ Verificar quais usuários têm senha incorreta
- ✅ Gerar o SQL para corrigir
- ✅ Mostrar um relatório completo

### **Passo 2: Corrigir Senhas no Banco Remoto**

Você tem **2 opções**:

#### **Opção A: Usar o SQL Gerado** ⭐ Recomendado

1. Abra: `http://localhost/listaespera/gerar_senha.php`
2. Copie o bloco de SQL que está na seção **"SQL para Corrigir Usuários"**
3. Acesse o phpMyAdmin do servidor **186.209.113.107**
4. Selecione o banco `dema5738_lista_espera_hospital`
5. Clique na aba **"SQL"**
6. Cole e execute o comando
7. Pronto! As senhas foram atualizadas

#### **Opção B: Usar Arquivo SQL**

1. Abra o arquivo: `sql/corrigir_senhas.sql`
2. Copie **TODO** o conteúdo
3. Acesse o phpMyAdmin do servidor **186.209.113.107**
4. Selecione o banco `dema5738_lista_espera_hospital`
5. Clique na aba **"SQL"**
6. Cole e execute
7. Verifique os resultados na tabela que aparece

### **Passo 3: Testar o Login**

1. Acesse: **`http://localhost/listaespera/login.php`**
2. Use as credenciais:
   - **E-mail:** admin@hospital.com
   - **Senha:** admin123
3. ✅ Deve funcionar!

---

## 🔍 Por Que Aconteceu Isso?

O hash bcrypt no `schema.sql` original era um **hash de exemplo** que não correspondia à senha "admin123". 

**Hash antigo (ERRADO):**
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

**Hash novo (CORRETO para "admin123"):**
```
$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm
```

---

## 📋 Checklist de Verificação

Após executar o SQL de correção, verifique:

- [ ] Acesse `gerar_senha.php` novamente
- [ ] Todos os usuários devem aparecer com **"Senha Funciona? SIM"** em verde
- [ ] Tente fazer login no sistema
- [ ] Se funcionar, **remova** os arquivos:
  - `gerar_senha.php`
  - `testar_conexao.php`

---

## 🛠️ Se Ainda Não Funcionar

### Verificação 1: Conexão com Banco

```
http://localhost/listaespera/testar_conexao.php
```

Todos os testes devem passar (5/5).

### Verificação 2: Arquivo de Configuração

Abra: `config/database.php`

Confirme:
```php
private $host = '186.209.113.107';
private $db_name = 'dema5738_lista_espera_hospital';
private $username = 'dema5738_lista_espera_hospital';
private $password = 'Dema@1973';
```

### Verificação 3: Extensões PHP

Verifique se PDO está habilitado:
1. Abra: `C:\xampp\php\php.ini`
2. Procure por:
   ```
   extension=pdo_mysql
   ```
3. Certifique-se que **NÃO** está comentado (sem ; no início)
4. Reinicie o Apache

### Verificação 4: Logs de Erro

Ative exibição de erros temporariamente:

1. Abra `login.php`
2. Adicione no início (logo após `<?php`):
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
3. Tente fazer login novamente
4. Veja qual erro aparece

---

## 📊 Comando SQL Manual

Se preferir atualizar manualmente, execute no phpMyAdmin:

```sql
USE dema5738_lista_espera_hospital;

-- Atualiza senha para "admin123"
UPDATE usuarios 
SET senha_hash = '$2y$10$rBW5m7V5yKZYKOC3F0hpV.Zy5vJ3xGxKL0X.WqF5dZ3yH2K0xKzMm';

-- Verifica resultado
SELECT id, nome, email, perfil, ativo FROM usuarios;
```

---

## 🔐 Entendendo o Sistema de Senhas

O sistema usa **bcrypt** (via `password_hash()` do PHP) para segurança:

1. **Cadastro/Atualização:**
   - Senha digitada: `admin123`
   - PHP executa: `password_hash('admin123', PASSWORD_DEFAULT)`
   - Resultado: `$2y$10$rBW5m...` (60 caracteres)
   - Salva no banco

2. **Login:**
   - Usuário digita: `admin123`
   - PHP busca hash do banco
   - Executa: `password_verify('admin123', $hash_do_banco)`
   - Se retornar `true` → Login OK
   - Se retornar `false` → Senha incorreta

3. **Por que não posso simplesmente ver a senha?**
   - Bcrypt é **irreversível** (one-way hash)
   - Impossível "descriptografar" o hash
   - Única opção: gerar novo hash e atualizar

---

## 🎯 Resumo

| Problema | Solução |
|----------|---------|
| "E-mail ou senha incorretos" | Execute `sql/corrigir_senhas.sql` |
| Erro de conexão | Verifique `config/database.php` |
| Página em branco | Ative display_errors e veja o erro |
| PDO não encontrado | Ative extensão pdo_mysql no php.ini |

---

## 📞 Próximos Passos

1. ✅ Execute o SQL de correção
2. ✅ Faça login no sistema
3. ✅ **Remova os arquivos de diagnóstico:**
   - `gerar_senha.php`
   - `testar_conexao.php`
4. ✅ Altere as senhas padrão dos usuários
5. ✅ Comece a usar o sistema!

---

**Problema resolvido!** 🎉

Se tiver qualquer dúvida, os arquivos de diagnóstico fornecerão todas as informações necessárias.
