<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
$host='localhost'; $db='MesaReserva'; $user='root'; $pass=''; $charset='utf8mb4';
try {
 $pdo=new PDO("mysql:host={$host};dbname={$db};charset={$charset}",$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
 $exists=static function(string $table) use($pdo):bool {$q=$pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$q->execute([$table]);return (bool)$q->fetchColumn();};
 $col=static function(string $table,string $column) use($pdo):bool {$q=$pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?');$q->execute([$table,$column]);return (bool)$q->fetchColumn();};
 $pdo->exec("CREATE TABLE IF NOT EXISTS administradores (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,nome VARCHAR(120) NOT NULL,email VARCHAR(160) NOT NULL UNIQUE,senha VARCHAR(255) NOT NULL,avatar VARCHAR(255) NULL,ativo TINYINT(1) NOT NULL DEFAULT 1,email_verificado_em DATETIME NULL,token_verificacao CHAR(64) NULL,token_expira_em DATETIME NULL,token_redefinicao_senha CHAR(64) NULL,token_redefinicao_expira DATETIME NULL,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX idx_admin_token(token_verificacao),INDEX idx_admin_reset(token_redefinicao_senha)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,nome VARCHAR(150) NOT NULL,telefone VARCHAR(30) NULL,email VARCHAR(160) NULL,senha VARCHAR(255) NULL,avatar VARCHAR(255) NULL,ativo TINYINT(1) NOT NULL DEFAULT 1,email_verificado_em DATETIME NULL,token_verificacao CHAR(64) NULL,token_expira_em DATETIME NULL,token_redefinicao_senha CHAR(64) NULL,token_redefinicao_expira DATETIME NULL,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,UNIQUE KEY uq_cliente_email(email),INDEX idx_clientes_nome(nome),INDEX idx_cliente_token(token_verificacao),INDEX idx_cliente_reset(token_redefinicao_senha)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 // Garante primeiro as colunas de autenticação em instalações antigas antes de copiar as contas.
 foreach(['senha'=>'VARCHAR(255) NULL','avatar'=>'VARCHAR(255) NULL','ativo'=>'TINYINT(1) NOT NULL DEFAULT 1','email_verificado_em'=>'DATETIME NULL','token_verificacao'=>'CHAR(64) NULL','token_expira_em'=>'DATETIME NULL','token_redefinicao_senha'=>'CHAR(64) NULL','token_redefinicao_expira'=>'DATETIME NULL'] as $c=>$def){if(!$col('clientes',$c))$pdo->exec("ALTER TABLE clientes ADD COLUMN {$c} {$def}");}
 // Migração de contas antigas: clientes ficam na tabela clientes e administradores em administradores.
 if ($exists('usuarios')) {
   $pdo->beginTransaction();
   $legacy=$pdo->query('SELECT * FROM usuarios')->fetchAll();
   foreach($legacy as $u){
     $tipo=$u['tipo']??'cliente';
     if($tipo==='admin'){
       $q=$pdo->prepare('SELECT id FROM administradores WHERE email=?');$q->execute([$u['email']]);
       if(!$q->fetch()){$i=$pdo->prepare('INSERT INTO administradores(nome,email,senha,avatar,ativo,email_verificado_em,token_verificacao,token_expira_em,token_redefinicao_senha,token_redefinicao_expira,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)');$i->execute([$u['nome'],$u['email'],$u['senha'],$u['avatar']??null,$u['ativo']??1,$u['email_verificado_em']??null,$u['token_verificacao']??null,$u['token_expira_em']??null,$u['token_redefinicao_senha']??null,$u['token_redefinicao_expira']??null,$u['created_at']??date('Y-m-d H:i:s')]);}
     } else {
       $cid=(int)($u['cliente_id']??0);
       if($cid>0){$q=$pdo->prepare('UPDATE clientes SET nome=?,email=?,senha=?,avatar=?,ativo=?,email_verificado_em=?,token_verificacao=?,token_expira_em=?,token_redefinicao_senha=?,token_redefinicao_expira=? WHERE id=?');$q->execute([$u['nome'],$u['email'],$u['senha'],$u['avatar']??null,$u['ativo']??1,$u['email_verificado_em']??null,$u['token_verificacao']??null,$u['token_expira_em']??null,$u['token_redefinicao_senha']??null,$u['token_redefinicao_expira']??null,$cid]);}
       else {$q=$pdo->prepare('SELECT id FROM clientes WHERE email=?');$q->execute([$u['email']]);if(!$q->fetch()){$i=$pdo->prepare('INSERT INTO clientes(nome,email,senha,avatar,ativo,email_verificado_em,token_verificacao,token_expira_em,token_redefinicao_senha,token_redefinicao_expira,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)');$i->execute([$u['nome'],$u['email'],$u['senha'],$u['avatar']??null,$u['ativo']??1,$u['email_verificado_em']??null,$u['token_verificacao']??null,$u['token_expira_em']??null,$u['token_redefinicao_senha']??null,$u['token_redefinicao_expira']??null,$u['created_at']??date('Y-m-d H:i:s')]);}}
     }
   }
   $pdo->commit();
   // Relacionamentos legados deixam de apontar para usuarios; passam a apontar para clientes.
   foreach(['perguntas','avaliacoes'] as $t){if($exists($t)){
     $fk=$pdo->prepare('SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=\'usuario_id\' AND REFERENCED_TABLE_NAME=\'usuarios\'');$fk->execute([$t]);foreach($fk->fetchAll(PDO::FETCH_COLUMN) as $name){$pdo->exec('ALTER TABLE `'.$t.'` DROP FOREIGN KEY `'.$name.'`');}
     // Converte o ID do login legado para o ID real do cadastro em clientes.
     if($col($t,'usuario_id')){$pdo->exec("DELETE x FROM `{$t}` x INNER JOIN usuarios u ON u.id=x.usuario_id WHERE u.tipo='admin'");$pdo->exec("UPDATE `{$t}` x INNER JOIN usuarios u ON u.id=x.usuario_id INNER JOIN clientes c ON c.email=u.email SET x.usuario_id=c.id WHERE u.tipo='cliente'");}
     if(!$col($t,'cliente_id')){$pdo->exec("ALTER TABLE `{$t}` CHANGE usuario_id cliente_id INT UNSIGNED NOT NULL");}
     if($exists('clientes')){try{$pdo->exec("ALTER TABLE `{$t}` ADD CONSTRAINT fk_{$t}_cliente FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE CASCADE");}catch(Throwable $e){}}
   }}
   // Só remove a tabela antiga depois de copiar contas e remover referências.
   $pdo->exec('DROP TABLE usuarios');
 }
 // Garante que um mesmo cadastro não permaneça simultaneamente em clientes e administradores.
 // Contas administrativas são mantidas somente em administradores; os registros clientes
 // duplicados (criados por versões antigas da promoção) e seus vínculos são removidos.
 if ($exists('administradores') && $exists('clientes')) {
   $duplicates = $pdo->query("SELECT c.id FROM clientes c INNER JOIN administradores a ON a.email = c.email WHERE c.email IS NOT NULL AND c.email <> ''")->fetchAll(PDO::FETCH_COLUMN);
   if ($duplicates) {
     $pdo->beginTransaction();
     try {
       foreach ($duplicates as $duplicateId) {
         $duplicateId = (int)$duplicateId;
         foreach (['reservas' => 'cliente_id', 'perguntas' => 'cliente_id', 'avaliacoes' => 'cliente_id'] as $dependentTable => $foreignKey) {
           if ($exists($dependentTable) && $col($dependentTable, $foreignKey)) {
             $delete = $pdo->prepare("DELETE FROM `{$dependentTable}` WHERE `{$foreignKey}` = ?");
             $delete->execute([$duplicateId]);
           }
         }
         $deleteClient = $pdo->prepare('DELETE FROM clientes WHERE id = ?');
         $deleteClient->execute([$duplicateId]);
       }
       $pdo->commit();
     } catch (Throwable $migrationError) {
       if ($pdo->inTransaction()) $pdo->rollBack();
       throw $migrationError;
     }
   }
 }
 // Ajustes em bancos que já tinham as tabelas sem colunas de autenticação.
 foreach(['perguntas','avaliacoes'] as $t){if(!$exists($t)){
   if($t==='perguntas')$pdo->exec("CREATE TABLE perguntas(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,cliente_id INT UNSIGNED NOT NULL,assunto VARCHAR(150) NOT NULL,mensagem TEXT NOT NULL,resposta TEXT NULL,status ENUM('aberta','respondida') NOT NULL DEFAULT 'aberta',created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,respondida_em DATETIME NULL,INDEX(cliente_id),CONSTRAINT fk_perguntas_cliente FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
   else $pdo->exec("CREATE TABLE avaliacoes(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,cliente_id INT UNSIGNED NOT NULL,nota TINYINT UNSIGNED NOT NULL,comentario VARCHAR(1000) NULL,aprovado TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(cliente_id),CONSTRAINT fk_avaliacoes_cliente FOREIGN KEY(cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 }}
} catch(PDOException $e){http_response_code(500);exit('<div style="font-family:Arial;padding:40px;max-width:760px;margin:auto"><h1>Erro de conexão com o banco</h1><p>Confira se o MySQL está ligado, se o banco <strong>MesaReserva</strong> existe e se o usuário root está sem senha.</p><p><strong>Detalhe técnico:</strong> '.htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8').'</p></div>');}
