<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('consumos/index.php');verify_csrf();
$id=(int)($_POST['id']??0);$mesa=(int)($_POST['mesa_id']??0);$descricao=trim($_POST['descricao']??'');$qtd=(int)($_POST['quantidade']??0);$valor=(float)($_POST['valor_unitario']??0);
if(!$mesa||!$descricao||$qtd<1||$valor<0){flash('error','Confira os dados do consumo.');redirect('consumos/form.php'.($id?'?id='.$id:''));}
try{
if($id){$s=$pdo->prepare("UPDATE consumos SET mesa_id=?,descricao=?,quantidade=?,valor_unitario=? WHERE id=?");$s->execute([$mesa,$descricao,$qtd,$valor,$id]);flash('success','Consumo atualizado.');}
else{$s=$pdo->prepare("INSERT INTO consumos(mesa_id,descricao,quantidade,valor_unitario) VALUES(?,?,?,?)");$s->execute([$mesa,$descricao,$qtd,$valor]);flash('success','Consumo registrado.');}
}catch(PDOException $e){flash('error','Não foi possível salvar o consumo.');}
redirect('consumos/index.php');
