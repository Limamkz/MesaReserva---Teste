<?php
$pageTitle = $pageTitle ?? 'Área do cliente';
$layoutType = 'client';
$flash = get_flash();
$currentPath = $_SERVER['PHP_SELF'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            <?= e($pageTitle) ?> | <?= APP_NAME ?>
        </title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261008painel3') ?>">
    </head>
    <body>
        <div class="app-shell">
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-brand">
                    <a href="<?= url('cliente.php') ?>" class="brand-link">
                        <img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva" class="sidebar-logo">
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-label">MINHA CONTA</div>
                <a class="nav-item <?= str_contains($currentPath, '/cliente.php') ? 'active' : '' ?>" href="<?= url('cliente.php') ?>">
                    <span class="material-symbols-outlined">home</span>
                    <span>Início</span>
                </a>
                <a class="nav-item <?= str_contains($currentPath, '/reservar.php') ? 'active' : '' ?>" href="<?= url('reservar.php') ?>">
                    <span class="material-symbols-outlined">calendar_add_on</span>
                    <span>Reservar</span>
                </a>
                <a class="nav-item <?= str_contains($currentPath, '/minhas-reservas.php') ? 'active' : '' ?>" href="<?= url('minhas-reservas.php') ?>">
                    <span class="material-symbols-outlined">event_note</span>
                    <span>Minhas reservas</span>
                </a>
                <a class="nav-item <?= str_contains($currentPath, '/perfil.php') ? 'active' : '' ?>" href="<?= url('perfil.php') ?>">
                    <span class="material-symbols-outlined">person</span>
                    <span>Meu perfil</span>
                </a>
                <div class="nav-label nav-label-space">ATENDIMENTO</div>
                <a class="nav-item <?= str_contains($currentPath, '/perguntas.php') ? 'active' : '' ?>" href="<?= url('perguntas.php') ?>">
                    <span class="material-symbols-outlined">support_agent</span>
                    <span>Ajuda</span>
                </a>
                <a class="nav-item <?= str_contains($currentPath, '/avaliacoes.php') ? 'active' : '' ?>" href="<?= url('avaliacoes.php') ?>">
                    <span class="material-symbols-outlined">star</span>
                    <span>Avaliar</span>
                </a>
                <div class="nav-label nav-label-space">SISTEMA</div>
                <a class="nav-item" href="<?= url('index.php') ?>">
                    <span class="material-symbols-outlined">public</span>
                    <span>Ver site</span>
                </a>
                <a class="nav-item" href="<?= url('privacidade.php') ?>">
                    <span class="material-symbols-outlined">privacy_tip</span>
                    <span>Privacidade</span>
                </a>
                <a class="nav-item" href="<?= url('logout.php') ?>">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Sair</span>
                </a>
            </nav>
            <div class="sidebar-bottom">
                <a class="user-mini user-mini-link" href="<?= url('perfil.php') ?>" title="Editar meu perfil">
                    <?php $avatar = current_user_avatar(); ?>
                    <?php if ($avatar): ?>
                        <img class="user-avatar user-avatar-image" src="<?= url($avatar) ?>" alt="Foto de <?= e(current_user_name()) ?>">
                    <?php else: ?>
                        <div class="user-avatar">
                            <?= e(mb_strtoupper(mb_substr(current_user_name(), 0, 1))) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <strong><?= e(current_user_name()) ?></strong>
                        <span>Cliente · Editar perfil</span>
                    </div>
                </a>
            </div>
        </aside>
        <div class="main-area">
            <header class="topbar">
                <button class="icon-button mobile-menu" id="mobileMenu" aria-label="Abrir menu">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="topbar-title">
                    <span class="eyebrow">RESTAURANTE MESARESERVA</span>
                    <h1>
                        <?= e($pageTitle) ?>
                    </h1>
                </div>
                <div class="topbar-actions">
                    <div class="date-pill">
                        <span class="material-symbols-outlined">calendar_today</span>
                        <?= date('d/m/Y') ?>
                    </div>
                    <a class="profile-chip" href="<?= url('logout.php') ?>" title="Sair">
                        <span class="material-symbols-outlined">logout</span>
                    </a>
                </div>
            </header>
            <main class="content client-content">
                <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <span class="material-symbols-outlined">
                        <?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?>
                    </span>
                    <span>
                        <?= e($flash['message']) ?>
                    </span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
                <?php endif; ?>
