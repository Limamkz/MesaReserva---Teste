<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id=(int)($_GET['id']??0);
if($id){$s=$pdo->prepare("DELETE FROM consumos WHERE id=?");$s->execute([$id]);flash('success','Consumo excluído.');}
redirect('consumos/index.php');
