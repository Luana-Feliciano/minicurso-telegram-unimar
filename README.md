# Minicurso — Formulário de contato integrado ao Telegram

Projeto de referência do minicurso. Stack: HTML, CSS, jQuery, PHP, MySQL e Telegram Bot API.

## Arquivos

| Arquivo | Papel |
|---|---|
| `index.html` | O formulário (nome, e-mail, mensagem + honeypot) |
| `style.css` | Aparência e as classes `.sucesso` / `.erro` |
| `script.js` | jQuery: valida, envia por AJAX, trata a resposta |
| `api.php` | Recebe, valida, grava no MySQL, avisa no Telegram |
| `config.exemplo.php` | Modelo do arquivo de segredos |
| `schema.sql` | Banco e tabela `contatos` |

## Como rodar

1. Copie a pasta para dentro do `htdocs` do XAMPP (`C:\xampp\htdocs\contato` ou `/opt/lampp/htdocs/contato`).
2. Ligue **Apache** e **MySQL** no painel do XAMPP.
3. Abra `http://localhost/phpmyadmin`, aba **SQL**, cole o conteúdo de `schema.sql` e execute.
4. Copie `config.exemplo.php` para `config.php`.
5. Crie o bot no `@BotFather` (`/newbot`) e copie o token para o `config.php`.
6. Mande uma mensagem qualquer para o seu bot e abra
   `https://api.telegram.org/bot<SEU_TOKEN>/getUpdates`.
   Copie o `chat.id` da resposta para o `config.php`.
7. Abra `http://localhost/contato/` e envie o formulário.

Deu certo quando as três coisas acontecem: texto verde na tela, linha nova na tabela
`contatos` e mensagem no Telegram.

## Testado até aqui

Os caminhos de validação foram verificados com o servidor embutido do PHP
(`php -S 127.0.0.1:8777`), todos respondendo o JSON esperado:

- `GET` na API → `405 Método não permitido`
- campos vazios → `Preencha todos os campos.`
- e-mail inválido → `E-mail inválido.`
- mensagem com menos de 10 caracteres → `Mensagem muito curta.`
- honeypot preenchido → responde `ok: true` e descarta em silêncio

O caminho completo (INSERT + Telegram) **não** foi testado nesta máquina: não há MySQL
instalado aqui nem token de bot real. Faça esse teste no XAMPP antes da aula.

## Segurança

- `config.php` está no `.gitignore`. Nunca versione token nem senha.
- Se o token vazar, mande `/revoke` para o `@BotFather` e gere outro.
- Todo SQL usa prepared statement; todo dado do usuário passa por `htmlspecialchars`
  antes de virar mensagem com `parse_mode: HTML`.
