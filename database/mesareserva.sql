CREATE DATABASE IF NOT EXISTS MesaReserva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE MesaReserva;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS avaliacoes;
DROP TABLE IF EXISTS perguntas;
DROP TABLE IF EXISTS consumos;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS mesas;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin','cliente') NOT NULL DEFAULT 'cliente',
    cliente_id INT UNSIGNED NULL,
    avatar VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    email_verificado_em DATETIME NULL,
    token_verificacao CHAR(64) NULL,
    token_expira_em DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_tipo(tipo), INDEX idx_token(token_verificacao)
) ENGINE=InnoDB;

CREATE TABLE clientes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(30) NULL,
    email VARCHAR(160) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_clientes_nome(nome)
) ENGINE=InnoDB;

ALTER TABLE usuarios ADD CONSTRAINT fk_usuario_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE SET NULL;

CREATE TABLE mesas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero INT UNSIGNED NOT NULL UNIQUE,
    capacidade TINYINT UNSIGNED NOT NULL,
    status ENUM('disponivel','reservada','ocupada') NOT NULL DEFAULT 'disponivel',
    observacoes VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mesas_status(status)
) ENGINE=InnoDB;

CREATE TABLE reservas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNSIGNED NOT NULL,
    mesa_id INT UNSIGNED NOT NULL,
    data_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    pessoas TINYINT UNSIGNED NOT NULL,
    status ENUM('pendente','confirmada','cancelada','concluida','expirada') NOT NULL DEFAULT 'pendente',
    observacoes VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reservas_data(data_reserva), INDEX idx_reservas_mesa_hora(mesa_id,data_reserva,hora_reserva),
    CONSTRAINT fk_reservas_cliente FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_reservas_mesa FOREIGN KEY(mesa_id) REFERENCES mesas(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE consumos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT UNSIGNED NOT NULL,
    descricao VARCHAR(180) NOT NULL,
    quantidade INT UNSIGNED NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_consumos_mesa(mesa_id),
    CONSTRAINT fk_consumos_mesa FOREIGN KEY(mesa_id) REFERENCES mesas(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE perguntas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    assunto VARCHAR(150) NOT NULL,
    mensagem TEXT NOT NULL,
    resposta TEXT NULL,
    status ENUM('aberta','respondida') NOT NULL DEFAULT 'aberta',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    respondida_em DATETIME NULL,
    CONSTRAINT fk_perguntas_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nota TINYINT UNSIGNED NOT NULL,
    comentario VARCHAR(1000) NULL,
    aprovado TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_avaliacoes_usuario FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
