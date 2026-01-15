# 🚀 Guia de Instalação - Sistema de Lista de Espera

## Pré-requisitos

✅ XAMPP instalado (versão 7.4+ ou 8.x)  
✅ Navegador web moderno (Chrome, Firefox, Edge)  
✅ Editor de texto (opcional, para configurações)

## Passo 1: Verificar Arquivos

Certifique-se de que todos os arquivos estão na pasta:
```
C:\xampp\htdocs\listaespera\
```

A estrutura deve conter:
- `config/` - Configurações
- `models/` - Models do sistema
- `controllers/` - Controllers
- `includes/` - Arquivos comuns
- `sql/` - Script do banco de dados
- Arquivos PHP principais (dashboard.php, login.php, etc.)

## Passo 2: Iniciar Serviços

1. Abra o **XAMPP Control Panel**
2. Clique em **Start** ao lado de **Apache**
3. Clique em **Start** ao lado de **MySQL**
4. Aguarde até que ambos fiquem com fundo verde

## Passo 3: Criar Banco de Dados

### Opção A: Via phpMyAdmin (Recomendado)

1. Abra o navegador e acesse: `http://localhost/phpmyadmin`
2. Clique na aba **"SQL"** no topo
3. Abra o arquivo `C:\xampp\htdocs\listaespera\sql\schema.sql` em um editor de texto
4. Copie **TODO** o conteúdo do arquivo
5. Cole na área de texto do phpMyAdmin
6. Clique no botão **"Executar"** (canto inferior direito)
7. Aguarde a mensagem de sucesso

### Opção B: Via Linha de Comando

1. Abra o **Prompt de Comando** (cmd)
2. Navegue até a pasta bin do MySQL:
   ```
   cd C:\xampp\mysql\bin
   ```
3. Execute o comando:
   ```
   mysql -u root -p < C:\xampp\htdocs\listaespera\sql\schema.sql
   ```
4. Pressione Enter quando pedir a senha (deixe em branco se não configurou senha)

## Passo 4: Verificar Banco de Dados

1. Volte ao phpMyAdmin (`http://localhost/phpmyadmin`)
2. No menu lateral esquerdo, procure o banco **`lista_espera_hospital`**
3. Clique nele
4. Verifique se as seguintes tabelas foram criadas:
   - ✅ usuarios
   - ✅ medicos
   - ✅ especialidades
   - ✅ convenios
   - ✅ medico_especialidade
   - ✅ fila_espera

## Passo 5: Acessar o Sistema

1. Abra o navegador
2. Digite na barra de endereço:
   ```
   http://localhost/listaespera
   ```
3. Você será redirecionado para a página de login

## Passo 6: Fazer Login

Use uma das credenciais de teste:

### Administrador (Acesso Total)
- **E-mail:** admin@hospital.com
- **Senha:** admin123

### Recepção (Gerenciar Fila)
- **E-mail:** recepcao@hospital.com
- **Senha:** admin123

### Médico (Visualizar)
- **E-mail:** medico@hospital.com
- **Senha:** admin123

## 🎉 Pronto!

O sistema está instalado e funcionando. Você pode:

1. ✅ Visualizar a lista de espera no **Dashboard**
2. ✅ Adicionar novos pacientes
3. ✅ Gerenciar médicos, especialidades e convênios
4. ✅ Filtrar e exportar dados
5. ✅ Marcar pacientes como agendados

## ⚙️ Configurações Opcionais

### Alterar Senha do Banco de Dados

Se você configurou uma senha para o MySQL, edite o arquivo:
```
C:\xampp\htdocs\listaespera\config\database.php
```

Localize a linha:
```php
private $password = '';
```

Altere para:
```php
private $password = 'sua_senha_aqui';
```

### Alterar Porta do Apache

Se a porta 80 estiver ocupada:

1. No XAMPP, clique em **Config** ao lado de Apache
2. Escolha **httpd.conf**
3. Procure por `Listen 80`
4. Altere para `Listen 8080` (ou outra porta)
5. Salve e reinicie o Apache
6. Acesse: `http://localhost:8080/listaespera`

## 🐛 Solução de Problemas

### "Erro na conexão com o banco de dados"

**Solução:**
- Verifique se o MySQL está rodando no XAMPP
- Confirme que o banco `lista_espera_hospital` foi criado
- Revise as credenciais em `config/database.php`

### "Página não encontrada" ou "404"

**Solução:**
- Verifique se o Apache está rodando
- Confirme que os arquivos estão em `C:\xampp\htdocs\listaespera\`
- Tente acessar: `http://localhost/listaespera/index.php`

### "Forbidden - You don't have permission"

**Solução:**
- Verifique se o Apache tem permissões na pasta htdocs
- No XAMPP Control Panel, clique em **Config > Apache httpd.conf**
- Procure por `Require all denied` e altere para `Require all granted`
- Salve e reinicie o Apache

### Página em branco (sem mensagem de erro)

**Solução:**
1. Ative exibição de erros editando `C:\xampp\php\php.ini`:
   ```
   display_errors = On
   error_reporting = E_ALL
   ```
2. Reinicie o Apache
3. Recarregue a página para ver o erro específico

### Caracteres estranhos (ã, ç, ê aparecem como �)

**Solução:**
- O banco foi criado com UTF-8
- Verifique se importou o `schema.sql` completo
- Certifique-se de que o navegador está usando codificação UTF-8

## 📞 Próximos Passos

1. **Altere as senhas padrão** dos usuários de teste
2. **Adicione seus dados reais** (médicos, especialidades, convênios)
3. **Teste todas as funcionalidades** antes de usar em produção
4. **Faça backup regular** do banco de dados

## 💾 Como Fazer Backup

### Via phpMyAdmin:
1. Acesse phpMyAdmin
2. Selecione o banco `lista_espera_hospital`
3. Clique em **Exportar**
4. Escolha formato **SQL**
5. Clique em **Executar**

### Via Linha de Comando:
```bash
cd C:\xampp\mysql\bin
mysqldump -u root -p lista_espera_hospital > backup.sql
```

---

**Sistema pronto para uso! Em caso de dúvidas, consulte o README.md**
