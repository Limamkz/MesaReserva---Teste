-- Execute este arquivo uma única vez no phpMyAdmin, com o banco MesaReserva selecionado.
USE MesaReserva;

ALTER TABLE reservas
    MODIFY COLUMN status ENUM('pendente','confirmada','cancelada','concluida','expirada')
    NOT NULL DEFAULT 'pendente';

-- Marca como expirada qualquer reserva não encerrada cujo horário já passou.
UPDATE reservas
SET status = 'expirada'
WHERE status IN ('pendente', 'confirmada')
  AND TIMESTAMP(data_reserva, hora_reserva) < NOW();
