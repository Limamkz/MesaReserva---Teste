<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$available=available_tables_count($pdo);
$reviews=$pdo->query("SELECT a.nota,a.comentario,u.nome FROM avaliacoes a INNER JOIN usuarios u ON u.id=a.usuario_id WHERE a.aprovado=1 ORDER BY a.created_at DESC LIMIT 3")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>MesaReserva | Seu momento começa pela mesa</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261008painel3') ?>">
    </head>
    <body class="public-body">
        <header class="public-nav">
            <a href="<?= url() ?>" class="public-brand">
                <img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva">
            </a>
        <nav>
            <a href="#sobre">Sobre</a>
            <a href="#experiencia">Experiência</a>
            <a href="#avaliacoes">Avaliações</a>
            <a href="<?= url('privacidade.php') ?>">Privacidade</a>
        </nav>
        <div class="public-actions">
            <a class="btn btn-light btn-sm" href="<?= url('login.php') ?>">Entrar</a>
            <a class="btn btn-primary btn-sm" href="<?= url('login.php') ?>">Reservar mesa</a>
        </div>
    </header>
    <main>
        <section class="hero-public">
            <div class="hero-overlay">
            </div>
            <div class="hero-content">
                <span class="eyebrow gold">RESTAURANTE MESARESERVA</span>
                <h1>Seu momento começa<br>pela <span>mesa certa.</span>
                </h1>
                <p>Uma experiência mais organizada para você reservar seu lugar, escolher o melhor horário e aproveitar o restaurante com tranquilidade.</p>
                <div class="hero-buttons">
                    <a class="btn btn-primary" href="<?= url('login.php') ?>">
                        <span class="material-symbols-outlined">calendar_month</span>Reservar uma mesa</a>
                            <a class="btn btn-ghost" href="#sobre">Conheça o MesaReserva</a>
                        </div>
                        <div class="availability-public">
                            <span class="pulse-dot">
                            </span>
                            <strong>
                                <?= $available ?>
                            </strong> mesas disponíveis no momento</div>
                        </div>
                    </section>
                    <section id="sobre" class="public-section intro-section">
                        <div class="section-kicker">SOBRE O MESARESERVA</div>
                        <div class="intro-grid">
                            <div>
                                <h2>Organização por trás de uma experiência memorável.</h2>
                            </div>
                            <div>
                                <p>O MesaReserva foi criado para tornar a experiência de reserva do Restaurante MesaReserva mais simples, segura e organizada. Em poucos passos, o cliente encontra disponibilidade e solicita sua mesa, enquanto a equipe mantém o controle do salão.</p>
                                <p>Do primeiro clique ao momento de sentar à mesa, cada detalhe foi pensado para unir praticidade, cuidado e eficiência.</p>
                            </div>
                        </div>
                    </section>
                    <section id="experiencia" class="public-section features-public">
                        <div class="section-kicker">UMA EXPERIÊNCIA PENSADA PARA VOCÊ</div>
                        <div class="feature-grid">
                            <article>
                                <span class="material-symbols-outlined">event_available</span>
                                <h3>Reserva simples</h3>
                                <p>Escolha data, horário, quantidade de pessoas e mesa disponível.</p>
                            </article>
                            <article>
                                <span class="material-symbols-outlined">table_restaurant</span>
                                <h3>Disponibilidade</h3>
                                <p>Consulte a quantidade de mesas disponíveis antes de fazer sua reserva.</p>
                            </article>
                            <article>
                                <span class="material-symbols-outlined">support_agent</span>
                                <h3>Atendimento</h3>
                                <p>Envie perguntas, problemas ou solicitações diretamente para a administração.</p>
                            </article>
                            <article>
                                <span class="material-symbols-outlined">verified_user</span>
                                <h3>Acesso seguro</h3>
                                <p>Conta protegida, senha com hash e verificação de e-mail.</p>
                            </article>
                        </div>
                    </section>
                    <section id="avaliacoes" class="public-section reviews-public">
                        <div class="section-kicker">QUEM JÁ VIVEU A EXPERIÊNCIA</div>
                        <div class="reviews-grid">
                            <?php if(!$reviews): ?>
                            <div class="review-empty">As primeiras avaliações aparecerão aqui.</div>
                            <?php else: foreach($reviews as $r): ?>
                            <article class="review-card">
                                <div class="stars">
                                    <?= str_repeat('★',(int)$r['nota']) ?>
                                    <span>
                                        <?= str_repeat('☆',5-(int)$r['nota']) ?>
                                    </span>
                                </div>
                                <p>“<?= e($r['comentario'] ?: 'Atendimento e experiência muito bons.') ?>”</p>
                                <strong>
                                    <?= e($r['nome']) ?>
                                </strong>
                            </article>
                            <?php endforeach; endif; ?>
                        </div>
                    </section>
                    <section class="cta-public">
                        <div>
                            <span class="section-kicker">MOMENTO CERTO, LUGAR CERTO</span>
                            <h2>Reserve sua mesa e deixe o resto com a gente.</h2>
                        </div>
                        <a class="btn btn-primary" href="<?= url('login.php') ?>">Começar minha reserva <span class="material-symbols-outlined">arrow_forward</span>
                        </a>
                    </section>
                </main>
                <?php require __DIR__.'/partials/footer.php'; ?>
