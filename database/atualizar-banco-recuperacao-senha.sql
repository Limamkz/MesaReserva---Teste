USE MesaReserva;
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS token_redefinicao_senha CHAR(64) NULL,
    ADD COLUMN IF NOT EXISTS token_redefinicao_expira DATETIME NULL;
