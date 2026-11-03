<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('mesas/index.php');
verify_csrf();
$id=(int)($_POST['id']??0); $numero=(int)($_POST['numero']??0); $capacidade=(int)($_POST['capacidade']??0);
$status=$_POST['status']??'disponivel'; $obs=trim($_POST['observacoes']??'');
if($numero<1||$capacidade<1||!in_array($status,['disponivel','reservada','ocupada'],true)){flash('error','Confira os dados da mesa.');redirect('mesas/form.php'.($id?'?id='.$id:''));}
try{
if($id){$stmt=$pdo->prepare("UPDATE mesas SET numero=?, capacidade=?, status=?, observacoes=? WHERE id=?");$stmt->execute([$numero,$capacidade,$status,$obs,$id]);flash('success','Mesa atualizada com sucesso.');}
else{$stmt=$pdo->prepare("INSERT INTO mesas(numero,capacidade,status,observacoes) VALUES(?,?,?,?)");$stmt->execute([$numero,$capacidade,$status,$obs]);flash('success','Mesa cadastrada com sucesso.');}
}catch(PDOException $e){flash('error',$e->getCode()==='23000'?'O número da mesa já está cadastrado.':'Não foi possível salvar a mesa.');}
redirect('mesas/index.php');
