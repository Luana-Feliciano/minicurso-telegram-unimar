<?php
header("Content-Type: application/json; charset=utf-8");

$config = require __DIR__ . "/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "mensagem" => "Método não permitido."]);
    exit;
}

// ---------- 1. ler os campos ----------
$nome     = trim($_POST["nome"] ?? "");
$email    = trim($_POST["email"] ?? "");
$mensagem = trim($_POST["mensagem"] ?? "");

// ---------- 2. honeypot: humano não vê esse campo, robô preenche ----------
if (trim($_POST["site"] ?? "") !== "") {
    echo json_encode(["ok" => true, "mensagem" => "Mensagem enviada!"]);
    exit; // finge que deu certo e descarta em silêncio
}

// ---------- 3. validar ----------
if ($nome === "" || $email === "" || $mensagem === "") {
    echo json_encode(["ok" => false, "mensagem" => "Preencha todos os campos."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["ok" => false, "mensagem" => "E-mail inválido."]);
    exit;
}

if (mb_strlen($mensagem) < 10) {
    echo json_encode(["ok" => false, "mensagem" => "Mensagem muito curta."]);
    exit;
}

// ---------- 4. gravar no banco ----------
try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_nome']};charset=utf8mb4",
        $config["db_user"],
        $config["db_senha"],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare(
        "INSERT INTO contatos (nome, email, mensagem, ip)
         VALUES (:nome, :email, :mensagem, :ip)"
    );

    $stmt->execute([
        ":nome"     => $nome,
        ":email"    => $email,
        ":mensagem" => $mensagem,
        ":ip"       => $_SERVER["REMOTE_ADDR"] ?? null,
    ]);

} catch (PDOException $e) {
    // O detalhe do erro é para o log, nunca para a tela do usuário.
    error_log("Erro ao gravar contato: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["ok" => false, "mensagem" => "Erro ao salvar. Tente mais tarde."]);
    exit;
}

// ---------- 5. avisar no Telegram ----------
$texto = "📬 <b>Novo contato pelo site</b>\n\n"
       . "<b>Nome:</b> " . htmlspecialchars($nome) . "\n"
       . "<b>E-mail:</b> " . htmlspecialchars($email) . "\n\n"
       . htmlspecialchars($mensagem);

$ch = curl_init("https://api.telegram.org/bot" . $config["bot_token"] . "/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_POSTFIELDS     => [
        "chat_id"    => $config["chat_id"],
        "text"       => $texto,
        "parse_mode" => "HTML",
    ],
]);

$resposta = curl_exec($ch);
$erroCurl = curl_error($ch);
curl_close($ch);

$telegram = json_decode($resposta, true);
$enviou = ($erroCurl === "" && isset($telegram["ok"]) && $telegram["ok"] === true);

if (!$enviou) {
    error_log("Telegram falhou: " . ($erroCurl !== "" ? $erroCurl : $resposta));
}

// ---------- 6. responder o jQuery ----------
echo json_encode([
    "ok" => true,
    "mensagem" => $enviou
        ? "Mensagem enviada! Já chegou no Telegram."
        : "Recebemos sua mensagem, mas o aviso no Telegram falhou.",
]);
