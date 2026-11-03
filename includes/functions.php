<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';

function e(mixed $value): string
{
return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Monta a URL pública para links enviados por e-mail.
 * Em rede local, usa o mesmo host pelo qual a pessoa abriu o site
 * (por exemplo, 192.168.0.25), em vez de fixar localhost.
 */
function verification_base_url(): string
{
    $host = (string)($_SERVER['HTTP_HOST'] ?? '');

    // Aceita somente host/IP e porta, evitando caracteres inesperados no link.
    if ($host !== '' && preg_match('/^[a-zA-Z0-9.-]+(?::[0-9]{1,5})?$/', $host)) {
        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string)$_SERVER['HTTPS']) !== 'off';
        $scheme = $https ? 'https' : 'http';
        return $scheme . '://' . $host . BASE_URL;
    }

    // Fallback para instalações em que o servidor não fornece HTTP_HOST.
    return rtrim(APP_URL, '/');
}

function redirect(string $path): never
{
header('Location: ' . url($path));
exit;
}

function flash(string $type, string $message): void
{
$_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
return $flash;
}

function money(float|int|string $value): string
{
return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function status_label(string $status): string
{
return match ($status) {
'disponivel' => 'Disponível',
'reservada' => 'Reservada',
'ocupada' => 'Ocupada',
'pendente' => 'Pendente',
'confirmada' => 'Confirmada',
'cancelada' => 'Recusada',
'concluida' => 'Concluída',
'expirada' => 'Expirado',
default => ucfirst($status),
};
}

function status_class(string $status): string
{
return match ($status) {
'disponivel' => 'status-disponivel',
'reservada', 'confirmada' => 'status-reservada',
'ocupada' => 'status-ocupada',
'pendente' => 'status-pendente',
'cancelada' => 'status-cancelada',
'concluida' => 'status-concluida',
'expirada' => 'status-cancelada',
default => 'status-default',
};
}

function update_table_status_from_reservation(PDO $pdo, int $mesaId): void
{
$stmt = $pdo->prepare(
"SELECT status
FROM reservas
WHERE mesa_id = ?
AND status = 'confirmada'
AND data_reserva >= CURDATE()
ORDER BY data_reserva ASC, hora_reserva ASC
LIMIT 1"
);
$stmt->execute([$mesaId]);
$reservation = $stmt->fetch();

if ($reservation) {
$pdo->prepare("UPDATE mesas SET status = 'reservada' WHERE id = ? AND status <> 'ocupada'")->execute([$mesaId]);
} else {
$pdo->prepare("UPDATE mesas SET status = 'disponivel' WHERE id = ? AND status = 'reservada'")->execute([$mesaId]);
}
}

function verification_token(): string { return bin2hex(random_bytes(32)); }
function verification_hash(string $token): string { return hash('sha256', $token); }

function smtp_read($socket, array $expectedCodes): string
{
$response = '';
while (($line = fgets($socket, 515)) !== false) {
$response .= $line;
if (isset($line[3]) && $line[3] === ' ') break;
}
$code = (int)substr($response, 0, 3);
if (!in_array($code, $expectedCodes, true)) {
throw new RuntimeException('SMTP respondeu com erro: ' . trim($response));
}
return $response;
}

function smtp_command($socket, string $command, array $expectedCodes): string
{
fwrite($socket, $command . "\r\n");
return smtp_read($socket, $expectedCodes);
}

function send_smtp_mail(string $to, string $subject, string $body): bool
{
if (SMTP_USERNAME === 'SEU_EMAIL@gmail.com' || SMTP_PASSWORD === 'SUA_SENHA_DE_APP_AQUI') {
throw new RuntimeException('Configure o SMTP em config/mail.php antes de enviar e-mails.');
}

$transport = SMTP_SECURE === 'ssl' ? 'ssl://' : '';
$socket = @fsockopen($transport . SMTP_HOST, SMTP_PORT, $errno, $errstr, 15);
if (!$socket) throw new RuntimeException("Não foi possível conectar ao SMTP: {$errstr} ({$errno}).");
stream_set_timeout($socket, 15);

try {
smtp_read($socket, [220]);
smtp_command($socket, 'EHLO localhost', [250]);
smtp_command($socket, 'AUTH LOGIN', [334]);
smtp_command($socket, base64_encode(SMTP_USERNAME), [334]);
smtp_command($socket, base64_encode(SMTP_PASSWORD), [235]);
smtp_command($socket, 'MAIL FROM:<' . SMTP_FROM_EMAIL . '>', [250]);
smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
smtp_command($socket, 'DATA', [354]);

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$headers = [
'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>',
'To: <' . $to . '>',
'Subject: ' . $encodedSubject,
'MIME-Version: 1.0',
'Content-Type: text/plain; charset=UTF-8',
'Content-Transfer-Encoding: 8bit',
'Date: ' . date(DATE_RFC2822),
];
$safeBody = preg_replace('/(?m)^\./', '..', str_replace(["\r\n", "\r"], "\n", $body));
$data = implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $safeBody) . "\r\n.";
smtp_command($socket, $data, [250]);
smtp_command($socket, 'QUIT', [221]);
fclose($socket);
return true;
} catch (Throwable $e) {
fclose($socket);
throw $e;
}
}

function send_verification_email(string $nome, string $email, string $token): bool
{
$link = rtrim(verification_base_url(), '/') . '/verificar-email.php?token=' . urlencode($token);
$subject = 'Confirme seu e-mail — MesaReserva';
$message = "Olá, {$nome}!\n\nSua conta no MesaReserva foi criada com sucesso. Para ativá-la, confirme seu e-mail acessando:\n\n{$link}\n\nEste link é válido por 24 horas. Se você não criou esta conta, ignore esta mensagem.\n\nMesaReserva\nSua mesa, seu horário, sua reserva.";
return send_smtp_mail($email, $subject, $message);
}

function available_tables_count(PDO $pdo): int
{
    // Libera mesas cujo status ficou como "reservada" após o horário passar.
    // Uma reserva confirmada futura de hoje mantém a mesa reservada; mesas ocupadas
    // manualmente nunca são liberadas por esta rotina.
    $pdo->exec("UPDATE mesas m
        SET m.status = 'disponivel'
        WHERE m.status = 'reservada'
          AND NOT EXISTS (
              SELECT 1 FROM reservas r
              WHERE r.mesa_id = m.id
                AND r.status = 'confirmada'
                AND r.data_reserva = CURDATE()
                AND TIMESTAMP(r.data_reserva, r.hora_reserva) >= NOW()
          )");

    return (int)$pdo->query("SELECT COUNT(*) FROM mesas m
        WHERE m.status <> 'ocupada'
          AND NOT EXISTS (
              SELECT 1 FROM reservas r
              WHERE r.mesa_id = m.id
                AND r.status = 'confirmada'
                AND r.data_reserva = CURDATE()
                AND TIMESTAMP(r.data_reserva, r.hora_reserva) >= NOW()
          )")->fetchColumn();
}

function current_user_avatar(): ?string
{
    if (!is_logged_in()) return null;

    $sessionAvatar = $_SESSION['usuario_avatar'] ?? null;
    if ($sessionAvatar !== null && $sessionAvatar !== '') {
        return (string)$sessionAvatar;
    }

    global $pdo;

    try {
        $stmt = $pdo->prepare('SELECT avatar FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([current_user_id()]);
        $avatar = $stmt->fetchColumn();
        return $avatar ? (string)$avatar : null;
    } catch (PDOException $e) {
        // Compatibilidade com bancos antigos: a coluna pode ainda não existir.
        return null;
    }
}

