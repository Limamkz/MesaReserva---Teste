<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function is_logged_in(): bool { return isset($_SESSION['usuario_id']); }
function is_admin(): bool { return ($_SESSION['usuario_tipo'] ?? '') === 'admin'; }
function is_cliente(): bool { return ($_SESSION['usuario_tipo'] ?? '') === 'cliente'; }
function require_login(): void { if (!is_logged_in()) { header('Location: ' . BASE_URL . '/login.php'); exit; } }
function require_admin(): void { require_login(); if (!is_admin()) { header('Location: ' . BASE_URL . '/cliente.php'); exit; } }
function current_user_name(): string { return $_SESSION['usuario_nome'] ?? 'Usuário'; }
function current_user_id(): int { return (int)($_SESSION['usuario_id'] ?? 0); }
function current_client_id(): int { return (int)($_SESSION['cliente_id'] ?? 0); }
function login_user(array $user): void {
session_regenerate_id(true);
$_SESSION['usuario_id']=(int)$user['id'];
$_SESSION['usuario_nome']=$user['nome'];
$_SESSION['usuario_email']=$user['email'];
$_SESSION['usuario_tipo']=$user['tipo'] ?? 'cliente';
$_SESSION['cliente_id']=(int)($user['cliente_id'] ?? 0);
$_SESSION['usuario_avatar']=$user['avatar'] ?? null;
}
function logout_user(): void { $_SESSION=[]; if(ini_get('session.use_cookies')){ $p=session_get_cookie_params(); setcookie(session_name(),' ',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']); } session_destroy(); }
function csrf_token(): string { if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function verify_csrf(): void { $token=$_POST['csrf_token']??''; if(!$token||!hash_equals($_SESSION['csrf_token']??'',$token)){ http_response_code(419); exit('Token de segurança inválido. Volte e tente novamente.'); } }
