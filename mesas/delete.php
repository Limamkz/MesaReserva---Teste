<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$id=(int)($_GET['id']??0);
if($id){
try{$stmt=$pdo->prepare("DELETE FROM mesas WHERE id=?");$stmt->execute([$id]);flash('success','Mesa excluída com sucesso.');}
catch(PDOException $e){flash('error','Não é possível excluir esta mesa enquanto houver reservas ou consumos vinculados.');}
}
redirect('mesas/index.php');
