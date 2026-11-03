USE MesaReserva;

ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS tipo ENUM('admin','cliente') NOT NULL DEFAULT 'cliente' AFTER senha;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS cliente_id INT UNSIGNED NULL AFTER tipo;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL AFTER cliente_id;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS email_verificado_em DATETIME NULL AFTER ativo;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS token_verificacao CHAR(64) NULL AFTER email_verificado_em;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS token_expira_em DATETIME NULL AFTER token_verificacao;

CREATE TABLE IF NOT EXISTS perguntas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    assunto VARCHAR(150) NOT NULL,
    mensagem TEXT NOT NULL,
    resposta TEXT NULL,
    status ENUM('aberta','respondida') NOT NULL DEFAULT 'aberta',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    respondida_em DATETIME NULL,
    INDEX idx_perguntas_usuario(usuario_id),
    CONSTRAINT fk_perguntas_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nota TINYINT UNSIGNED NOT NULL,
    comentario VARCHAR(1000) NULL,
    aprovado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_avaliacoes_usuario(usuario_id),
    CONSTRAINT fk_avaliacoes_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
