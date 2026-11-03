USE MesaReserva;

-- Adiciona o caminho da foto de perfil de cada usuário.
ALTER TABLE usuarios
    ADD COLUMN avatar VARCHAR(255) NULL AFTER cliente_id;
