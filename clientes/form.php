<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
flash('error', 'Os dados pessoais do cliente são apenas para consulta nesta área.');
redirect('clientes/index.php');
