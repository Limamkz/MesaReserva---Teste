<?php declare(strict_types=1); require_once __DIR__.'/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>Missão, Visão e Valores | MesaReserva</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261008painel3') ?>">
    </head>
    <body class="institutional-body">
        <header class="institutional-nav">
            <a href="<?= url() ?>">
                <img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva">
            </a>
        <a class="btn btn-light btn-sm" href="<?= url('index.php') ?>">Voltar ao início</a>
    </header>
    <section class="institutional-hero">
        <div class="institutional-hero-inner">
            <span class="section-kicker">ESSÊNCIA MESARESERVA</span>
            <h1>Mais do que uma reserva.<br>
                <span>Uma experiência bem cuidada.</span>
            </h1>
            <p>Conheça os princípios que orientam o Restaurante MesaReserva: uma forma de trabalhar baseada em organização, cuidado com as pessoas, transparência e busca constante por uma experiência melhor.</p>
        </div>
    </section>
    <main class="institutional-content">
        <div class="purpose-grid">
            <article class="purpose-card">
                <span class="purpose-number">01</span>
                <span class="material-symbols-outlined">flag</span>
                <h2>Missão</h2>
                <p>Facilitar a reserva e a organização do salão do Restaurante MesaReserva, oferecendo ao cliente uma experiência simples, segura e acolhedora desde o primeiro contato até o momento à mesa.</p>
            </article>
            <article class="purpose-card">
                <span class="purpose-number">02</span>
                <span class="material-symbols-outlined">visibility</span>
                <h2>Visão</h2>
                <p>Ser reconhecido pela excelência no atendimento, pela organização da operação e pela capacidade de transformar tecnologia em uma experiência de restaurante mais tranquila, eficiente e humana.</p>
            </article>
            <article class="purpose-card">
                <span class="purpose-number">03</span>
                <span class="material-symbols-outlined">workspace_premium</span>
                <h2>Valores</h2>
                <p>Colocar o cliente no centro das decisões, agir com responsabilidade e transparência e buscar qualidade em cada detalhe, mantendo uma cultura de respeito, inovação e melhoria contínua.</p>
            </article>
        </div>
        <section class="values-section">
            <span class="section-kicker">O QUE GUIA CADA DECISÃO</span>
            <h2>Nossos valores na prática</h2>
            <div class="values-list">
                <div class="value-item">
                    <span class="material-symbols-outlined">favorite</span>
                    <div>
                        <strong>Cuidado com o cliente</strong>
                        <span>Ouvir, acolher e buscar uma experiência positiva em cada contato.</span>
                    </div>
                </div>
                <div class="value-item">
                    <span class="material-symbols-outlined">task_alt</span>
                    <div>
                        <strong>Organização e eficiência</strong>
                        <span>Manter informações claras para que reservas e horários sejam administrados com segurança.</span>
                    </div>
                </div>
                <div class="value-item">
                    <span class="material-symbols-outlined">lightbulb</span>
                    <div>
                        <strong>Inovação</strong>
                        <span>Usar tecnologia para resolver problemas reais e simplificar processos.</span>
                    </div>
                </div>
                <div class="value-item">
                    <span class="material-symbols-outlined">visibility</span>
                    <div>
                        <strong>Transparência</strong>
                        <span>Comunicar informações de forma clara, responsável e acessível.</span>
                    </div>
                </div>
                <div class="value-item">
                    <span class="material-symbols-outlined">shield</span>
                    <div>
                        <strong>Segurança e responsabilidade</strong>
                        <span>Proteger informações e tratar cada operação com seriedade.</span>
                    </div>
                </div>
                <div class="value-item">
                    <span class="material-symbols-outlined">star</span>
                    <div>
                        <strong>Qualidade</strong>
                        <span>Buscar melhoria contínua nos serviços, processos e atendimento.</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php require __DIR__.'/partials/footer.php'; ?>
</body>
</html>
