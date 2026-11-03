<?php
declare(strict_types=1);

// Configure as variáveis de ambiente no servidor quando possível.
// O Gmail exibe a senha de app em grupos separados por espaços; removemos
// esses espaços antes da autenticação SMTP.
define('SMTP_HOST', getenv('MESARESERVA_SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('MESARESERVA_SMTP_PORT') ?: 465));
define('SMTP_SECURE', getenv('MESARESERVA_SMTP_SECURE') ?: 'ssl');
define('SMTP_USERNAME', getenv('MESARESERVA_SMTP_USERNAME') ?: 'murilo.lima.mkz@gmail.com');
$smtpPassword = getenv('MESARESERVA_SMTP_PASSWORD');
if ($smtpPassword === false || $smtpPassword === '') {
    // Compatibilidade com a configuração SMTP já usada nesta instalação.
    $smtpPassword = 'vbsr yhbk wcdr yirj';
}
define('SMTP_PASSWORD', preg_replace('/\\s+/', '', trim($smtpPassword)));
define('SMTP_FROM_EMAIL', SMTP_USERNAME);
define('SMTP_FROM_NAME', 'MesaReserva');
