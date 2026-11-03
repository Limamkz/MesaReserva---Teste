USE MesaReserva;
ALTER TABLE usuarios ADD COLUMN tipo ENUM('admin','cliente') NOT NULL DEFAULT 'cliente' AFTER senha;
ALTER TABLE usuarios ADD COLUMN cliente_id INT UNSIGNED NULL AFTER tipo;
ALTER TABLE usuarios ADD COLUMN email_verificado_em DATETIME NULL AFTER ativo;
ALTER TABLE usuarios ADD COLUMN token_verificacao CHAR(64) NULL AFTER email_verificado_em;
ALTER TABLE usuarios ADD COLUMN token_expira_em DATETIME NULL AFTER token_verificacao;
ALTER TABLE usuarios ADD INDEX idx_usuario_tipo (tipo);
ALTER TABLE usuarios ADD INDEX idx_token (token_verificacao);
ALTER TABLE usuarios ADD CONSTRAINT fk_usuario_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE SET NULL;
CREATE TABLE IF NOT EXISTS perguntas (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NOT NULL, assunto VARCHAR(150) NOT NULL, mensagem TEXT NOT NULL, resposta TEXT NULL, status ENUM('aberta','respondida') NOT NULL DEFAULT 'aberta', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, respondida_em DATETIME NULL, CONSTRAINT fk_perguntas_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS avaliacoes (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NOT NULL, nota TINYINT UNSIGNED NOT NULL, comentario VARCHAR(1000) NULL, aprovado TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_avaliacoes_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE) ENGINE=InnoDB;

    -- Preserva a conta antiga: a primeira conta vira administradora e contas administrativas ficam verificadas.
    UPDATE usuarios SET tipo='admin', email_verificado_em=COALESCE(email_verificado_em,NOW()) WHERE id=(SELECT id FROM (SELECT MIN(id) id FROM usuarios) x);
    UPDATE usuarios SET email_verificado_em=COALESCE(email_verificado_em,NOW()) WHERE tipo='admin';
