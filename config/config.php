<?php
declare(strict_types=1);

// Detecta automaticamente a pasta pública do projeto. Isso permite instalar
// em /MesaReserva, /mesareserva ou na raiz do servidor sem quebrar os links.
$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$pathParts = array_values(array_filter(explode('/', trim($scriptName, '/')), 'strlen'));
$basePath = count($pathParts) > 1 ? '/' . $pathParts[0] : '';
define('BASE_URL', $basePath);
define('APP_NAME', 'MesaReserva');
define('APP_VERSION', '1.1.0');

// URL completa usada nos links enviados por e-mail.
// Em produção, troque pelo domínio HTTPS do sistema.
define('APP_URL', 'http://localhost' . BASE_URL);

date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
session_start();
}
