<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('reservas/index.php');verify_csrf();
$id=(int)($_POST['id']??0);$cliente=(int)($_POST['cliente_id']??0);$mesa=(int)($_POST['mesa_id']??0);$pessoas=(int)($_POST['pessoas']??0);
$data=$_POST['data_reserva']??'';$hora=$_POST['hora_reserva']??'';$status=$_POST['status']??'pendente';$obs=trim($_POST['observacoes']??'');
if(!$cliente||!$mesa||$pessoas<1||!$data||!$hora||!in_array($status,['pendente','confirmada','cancelada','concluida'],true)){flash('error','Confira os dados da reserva.');redirect('reservas/form.php'.($id?'?id='.$id:''));}
$s=$pdo->prepare("SELECT capacidade FROM mesas WHERE id=?");$s->execute([$mesa]);$cap=(int)$s->fetchColumn();
if(!$cap||$pessoas>$cap){flash('error',"A mesa selecionada comporta no máximo {$cap} pessoas.");redirect('reservas/form.php'.($id?'?id='.$id:''));}
try{
$check=$pdo->prepare("SELECT COUNT(*) FROM reservas WHERE mesa_id=? AND data_reserva=? AND hora_reserva=? AND status IN ('pendente','confirmada') AND id<>?");
$check->execute([$mesa,$data,$hora,$id]); if((int)$check->fetchColumn()>0){flash('error','Já existe uma reserva para esta mesa nesse mesmo horário.');redirect('reservas/form.php'.($id?'?id='.$id:''));}
if($id){$s=$pdo->prepare("UPDATE reservas SET cliente_id=?,mesa_id=?,data_reserva=?,hora_reserva=?,pessoas=?,status=?,observacoes=? WHERE id=?");$s->execute([$cliente,$mesa,$data,$hora,$pessoas,$status,$obs,$id]);flash('success','Reserva atualizada com sucesso.');}
else{$s=$pdo->prepare("INSERT INTO reservas(cliente_id,mesa_id,data_reserva,hora_reserva,pessoas,status,observacoes) VALUES(?,?,?,?,?,?,?)");$s->execute([$cliente,$mesa,$data,$hora,$pessoas,$status,$obs]);flash('success','Reserva cadastrada com sucesso.');}
if($status==='confirmada'){$pdo->prepare("UPDATE mesas SET status='reservada' WHERE id=? AND status<>'ocupada'")->execute([$mesa]);}
elseif($status==='cancelada'||$status==='concluida'){update_table_status_from_reservation($pdo,$mesa);}
}catch(PDOException $e){flash('error','Não foi possível salvar a reserva.');}
redirect('reservas/index.php');
