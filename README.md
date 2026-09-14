# Formulário de contato que avisa no Telegram

Projeto do minicurso. Um formulário de contato que, ao ser enviado, grava os dados no
banco e manda a mensagem para o seu Telegram — sem recarregar a página.

Tecnologias: **HTML · CSS · jQuery · PHP · MySQL · Telegram Bot API**

---

## Antes de começar

Você vai precisar de:

- **XAMPP** instalado (traz Apache, PHP e MySQL juntos) — apachefriends.org
- Um **editor de código** (VS Code serve bem)
- **Telegram** no celular, com sua conta

---

## Como colocar para rodar

### 1. Coloque a pasta no lugar certo

Copie a pasta do projeto para dentro do `htdocs` do XAMPP e chame de `contato`:

```
Windows:  C:\xampp\htdocs\contato\
Linux:    /opt/lampp/htdocs/contato/
macOS:    /Applications/XAMPP/htdocs/contato/
```

> **Por que isso importa:** PHP só executa quando o arquivo é servido pelo Apache.
> Se você abrir o `index.html` com dois cliques, o formulário até aparece, mas o envio
> nunca funciona.

### 2. Ligue o servidor

Abra o painel do XAMPP e clique em **Start** no `Apache` e no `MySQL`.

Teste: abra `http://localhost/contato/` no navegador. O formulário tem que aparecer.

### 3. Crie o banco de dados

1. Abra `http://localhost/phpmyadmin`
2. Clique na aba **SQL**
3. Cole todo o conteúdo do arquivo `schema.sql`
4. Clique em **Executar**

Deve aparecer o banco `minicurso` com a tabela `contatos` na lista da esquerda.

### 4. Crie o seu bot no Telegram

1. No Telegram, procure por **@BotFather** (confira o selo azul de verificado)
2. Envie `/newbot`
3. Escolha um **nome** para o bot — ex.: `Contato Turma 3B`
4. Escolha um **username**, que precisa terminar em `bot` e ser único — ex.: `contato_turma3b_bot`
5. O BotFather responde com o **token**, mais ou menos assim:

```
8123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw
```

Copie esse token. Ele é a senha do seu bot.

### 5. Descubra o seu chat_id

O token diz *quem envia*. O `chat_id` diz *para onde vai*. Precisa dos dois.

1. Abra a conversa com o **seu** bot e mande qualquer mensagem (um "oi" basta)
2. Abra esta URL no navegador, trocando `SEU_TOKEN` pelo token do passo anterior:

```
https://api.telegram.org/botSEU_TOKEN/getUpdates
```

3. Procure na resposta o campo `"chat": { "id": ... }`:

```json
{
  "ok": true,
  "result": [
    {
      "message": {
        "chat": { "id": 123456789, "first_name": "Ana", "type": "private" },
        "text": "oi"
      }
    }
  ]
}
```

Aqui o `chat_id` é `123456789`.

> **Veio `"result": []` vazio?** Você ainda não mandou mensagem para o bot.
> Mande e recarregue a URL.

### 6. Preencha o config.php

Copie o arquivo `config.exemplo.php` para um arquivo novo chamado `config.php` e
preencha com o seu token e o seu chat_id:

```php
<?php
return [
    "db_host"  => "localhost",
    "db_nome"  => "minicurso",
    "db_user"  => "root",
    "db_senha" => "",

    "bot_token" => "8123456789:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw",
    "chat_id"   => "123456789",
];
```

No XAMPP, o usuário do MySQL é `root` e a senha é vazia — por isso essas duas linhas
já vêm prontas.

### 7. Teste

Abra `http://localhost/contato/`, preencha e envie.

**Deu certo quando as três coisas acontecem ao mesmo tempo:**

- [ ] Mensagem verde aparece na tela
- [ ] Uma linha nova aparece na tabela `contatos` do phpMyAdmin
- [ ] A mensagem chega no seu Telegram

---

## O que cada arquivo faz

| Arquivo | Papel |
|---|---|
| `index.html` | O formulário: nome, e-mail, mensagem |
| `style.css` | A aparência, e as cores de sucesso e de erro |
| `script.js` | jQuery: valida, envia por AJAX e mostra a resposta |
| `api.php` | Recebe os dados, valida, grava no banco e chama o Telegram |
| `config.php` | Senha do banco e token do bot — **só na sua máquina** |
| `config.exemplo.php` | Modelo do arquivo acima, sem segredo nenhum |
| `schema.sql` | Cria o banco e a tabela |

O caminho de uma mensagem:

```
formulário  ->  jQuery ($.post)  ->  api.php  ->  MySQL
                                        |
                                        +------>  Telegram
```

---

## Quando não funcionar

Antes de tudo: aperte **F12**, vá na aba **Network**, envie o formulário, clique na
linha `api.php` e leia a aba **Response**. Quase sempre a resposta já diz o problema.

| O que acontece | Causa provável | O que fazer |
|---|---|---|
| A página recarrega ao enviar | O `preventDefault()` não rodou | Veja o erro vermelho no Console (F12) |
| Aparece "Não foi possível falar com o servidor" | O PHP deu erro e não devolveu JSON | Network → `api.php` → Response |
| `chat not found` | `chat_id` errado, ou você nunca falou com o bot | Mande "oi" para o bot e refaça o `getUpdates` |
| `401 Unauthorized` | Token errado ou incompleto | Peça de novo com `/token` no BotFather |
| Acentos viram `?` ou `Ã©` | Falta `utf8mb4` em algum ponto | Confira o `charset` no HTML, no PDO e na tabela |
| Tela em branco, sem erro nenhum | Erro fatal do PHP | Veja `xampp/apache/logs/error.log` |
| Salva no banco mas não chega no Telegram | Token ou chat_id errados | A própria tela avisa: "o aviso no Telegram falhou" |

---

## Cuidados importantes

**O token é a senha do seu bot.** Quem tiver ele manda mensagem como se fosse você.

- Nunca coloque o token no GitHub, em print, em slide ou em grupo de WhatsApp
- O arquivo `config.php` está no `.gitignore` justamente por isso — versione apenas o
  `config.exemplo.php`
- Se o token vazar, mande `/revoke` para o **@BotFather** e gere um novo

**Nunca confie no que vem do navegador.** Qualquer pessoa apaga o `required` pelo F12 e
manda o que quiser. Por isso o `api.php` valida tudo de novo, do zero, e usa *prepared
statements* (`:nome`, `:email`) em vez de juntar texto no SQL — é o que impede
SQL injection.

---

## Para ir além

Terminou e quer continuar? Tente:

1. **Botão de responder** — inclua o e-mail como link `mailto:` na mensagem do Telegram
2. **Painel de contatos** — um `lista.php` que faz `SELECT` e mostra os contatos numa tabela
3. **Anexo** — aceite um arquivo no formulário e envie com `sendDocument`
4. **Bot que responde** — estude `setWebhook`: o Telegram passa a chamar o *seu* PHP
   quando alguém escreve para o bot

Documentação oficial: core.telegram.org/bots/api — está em inglês e é enorme, mas a
estrutura é sempre a mesma que você já viu no `sendMessage`.
