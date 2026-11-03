<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$host = 'localhost';
$db   = 'MesaReserva';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
$pdo = new PDO($dsn, $user, $pass, $options);

// Garante as colunas necessárias para a recuperação de senha em bancos existentes.
$recoveryColumns = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME IN ('token_redefinicao_senha','token_redefinicao_expira')")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('token_redefinicao_senha', $recoveryColumns, true)) {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN token_redefinicao_senha CHAR(64) NULL");
}
if (!in_array('token_redefinicao_expira', $recoveryColumns, true)) {
    $pdo->exec("ALTER TABLE usuarios ADD COLUMN token_redefinicao_expira DATETIME NULL");
}

/*
* Migração automática para instalações que já possuem o banco antigo.
* Assim, atualizar o projeto não quebra páginas que dependem das novas
* tabelas de avaliações, perguntas e verificação de e-mail.
*/
$columnExists = static function (PDO $pdo, string $table, string $column): bool {
$stmt = $pdo->prepare(
"SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
);
$stmt->execute([$table, $column]);
return (bool)$stmt->fetchColumn();
};

$tableExists = static function (PDO $pdo, string $table): bool {
$stmt = $pdo->prepare(
"SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
);
$stmt->execute([$table]);
return (bool)$stmt->fetchColumn();
};

// Garante as colunas novas da tabela usuarios.
if (!$columnExists($pdo, 'usuarios', 'tipo')) {
$pdo->exec("ALTER TABLE usuarios ADD COLUMN tipo ENUM('admin','cliente') NOT NULL DEFAULT 'cliente' AFTER senha");
}
if (!$columnExists($pdo, 'usuarios', 'cliente_id')) {
$pdo->exec("ALTER TABLE usuarios ADD COLUMN cliente_id INT UNSIGNED NULL AFTER tipo");
}
if (!$columnExists($pdo, 'usuarios', 'avatar')) {
$pdo->exec("ALTER TABLE usuarios ADD COLUMN avatar VARCHAR(255) NULL AFTER cliente_id");
}
if (!$columnExists($pdo, 'usuarios', 'email_verificado_em')) {
$pdo->exec("ALTER TABLE usuarios ADD COLUMN email_verificado_em DATETIME NULL AFTER ativo");
}
if (!$columnExists($pdo, 'usuarios', 'token_verificacao')) {
$pdo->exec("ALTER TABLE usuarios ADD COLUMN token_verificacao CHAR(64) NULL AFTER email_verificado_em");
}
if (!$columnExists($pdo, 'usuarios', 'token_expira_em')) {
$pdo->exec("ALTER TABLE usuarios ADD COLUMN token_expira_em DATETIME NULL AFTER token_verificacao");
}

// Tabelas novas. IF NOT EXISTS evita o erro caso já tenham sido criadas.
if (!$tableExists($pdo, 'perguntas')) {
$pdo->exec("CREATE TABLE perguntas (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
usuario_id INT UNSIGNED NOT NULL,
assunto VARCHAR(150) NOT NULL,
mensagem TEXT NOT NULL,
resposta TEXT NULL,
status ENUM('aberta','respondida') NOT NULL DEFAULT 'aberta',
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
respondida_em DATETIME NULL,
INDEX idx_perguntas_usuario(usuario_id),
CONSTRAINT fk_perguntas_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

if (!$tableExists($pdo, 'avaliacoes')) {
$pdo->exec("CREATE TABLE avaliacoes (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
usuario_id INT UNSIGNED NOT NULL,
nota TINYINT UNSIGNED NOT NULL,
comentario VARCHAR(1000) NULL,
aprovado TINYINT(1) NOT NULL DEFAULT 1,
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
INDEX idx_avaliacoes_usuario(usuario_id),
CONSTRAINT fk_avaliacoes_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id)
ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Cria o relacionamento usuario -> cliente somente se ele ainda não existir.
$fkStmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA = DATABASE()
AND TABLE_NAME = 'usuarios'
AND COLUMN_NAME = 'cliente_id'
AND REFERENCED_TABLE_NAME = 'clientes'");
if ((int)$fkStmt->fetchColumn() === 0 && $tableExists($pdo, 'clientes')) {
try {
$pdo->exec("ALTER TABLE usuarios ADD CONSTRAINT fk_usuario_cliente
FOREIGN KEY (cliente_id) REFERENCES clientes(id)
ON UPDATE CASCADE ON DELETE SET NULL");
} catch (PDOException $e) {
// Não interrompe o site se o banco já possuir uma restrição equivalente.
}
}

} catch (PDOException $e) {
http_response_code(500);
exit(
'<div style="font-family:Arial;padding:40px;max-width:760px;margin:auto">' .
'<h1>Erro de conexão com o banco</h1>' .
'<p>Não foi possível conectar ao banco <strong>MesaReserva</strong> ou concluir a atualização automática.</p>' .
'<p>Confira se o MySQL está ligado no XAMPP, se o banco <strong>MesaReserva</strong> existe e se o usuário <strong>root</strong> está sem senha.</p>' .
'<p>
<strong>Detalhe técnico:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>' .
    '</div>'
    );
    }
