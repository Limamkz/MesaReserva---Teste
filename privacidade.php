<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Política de Privacidade';
$loggedArea = is_logged_in();
$areaType = is_admin() ? 'admin' : 'cliente';

if ($loggedArea) {
    if (is_admin()) {
        require __DIR__ . '/partials/header.php';
    } else {
        require __DIR__ . '/partials/client-header.php';
    }
    ?>
    <div class="policy-page-inner">
        <div class="policy-wrap">
            
        <section class="policy-hero">
            <div class="policy-hero-icon">
                <span class="material-symbols-outlined">shield_lock</span>
            </div>
            <span class="section-kicker">TRANSPARÊNCIA E SEGURANÇA</span>
            <h1>Política de Privacidade</h1>
            <p>Entenda de forma simples como o MesaReserva utiliza e protege suas informações.</p>
            <div class="policy-meta">
                <span class="material-symbols-outlined">update</span> Última atualização: <?= date('d/m/Y') ?>
                </div>
            </section>
            <article class="policy-content">
                <div class="policy-intro">
                    <span class="material-symbols-outlined">verified_user</span>
                    <p>O Restaurante MesaReserva valoriza a privacidade, a segurança e a transparência no tratamento das informações de seus clientes, usuários e colaboradores. Esta Política de Privacidade explica, de forma clara, quais dados podem ser coletados, para quais finalidades são utilizados, como são protegidos e quais são os direitos dos titulares.</p>
                </div>
                <h2>
                    <span>01</span> Quem somos</h2>
                        <p>O MesaReserva é o sistema digital utilizado pelo Restaurante MesaReserva para organizar reservas de mesas, disponibilidades, atendimento e comunicação com seus clientes. Para fins desta política, “MesaReserva”, “nós” e “nosso” referem-se ao Restaurante MesaReserva e ao sistema por ele utilizado.</p>
                        <h2>
                            <span>02</span> Dados que podemos coletar</h2>
                                <p>Durante o cadastro e a utilização do sistema, podemos coletar nome, endereço de e-mail, telefone, informações relacionadas à reserva, quantidade de pessoas, data e horário escolhidos, mensagens enviadas ao atendimento e avaliações realizadas. Também podem ser registrados dados técnicos necessários ao funcionamento e à segurança do sistema, como informações de sessão e registros básicos de acesso.</p>
                                <h2>
                                    <span>03</span> Finalidades do tratamento</h2>
                                        <p>Os dados são utilizados para criar e administrar contas, confirmar a identidade por meio do e-mail, processar e organizar reservas, permitir contato entre cliente e restaurante, responder perguntas e solicitações, receber avaliações, melhorar a qualidade do atendimento, prevenir usos indevidos e cumprir obrigações legais aplicáveis.</p>
                                        <h2>
                                            <span>04</span> Base e princípios</h2>
                                                <p>O tratamento busca observar os princípios de finalidade, adequação, necessidade, livre acesso, qualidade dos dados, transparência, segurança, prevenção e não discriminação. Quando aplicável, o tratamento poderá ocorrer com base no consentimento, na execução de procedimentos solicitados pelo titular, no cumprimento de obrigação legal ou regulatória, no exercício regular de direitos e em outras bases previstas na legislação.</p>
                                                <h2>
                                                    <span>05</span> Compartilhamento</h2>
                                                        <p>Não comercializamos dados pessoais. Informações poderão ser compartilhadas apenas quando necessário para a prestação do serviço, para operação técnica autorizada, para cumprimento de obrigação legal, para proteção de direitos ou mediante autorização do titular, sempre buscando limitar o compartilhamento ao mínimo necessário.</p>
                                                        <h2>
                                                            <span>06</span> Segurança</h2>
                                                                <p>Adotamos medidas técnicas e organizacionais compatíveis com a finalidade do sistema, incluindo controle de acesso, sessões autenticadas, consultas parametrizadas e armazenamento de senhas utilizando funções de hash. Nenhum sistema conectado à internet pode garantir risco zero, mas trabalhamos para reduzir possibilidades de acesso indevido, alteração, perda ou divulgação não autorizada.</p>
                                                                <h2>
                                                                    <span>07</span> Senhas e autenticação</h2>
                                                                        <p>As senhas não devem ser armazenadas ou compartilhadas em texto puro. O sistema utiliza mecanismos de hash para armazenamento e verificação. A confirmação do e-mail é utilizada como camada adicional para validar o endereço informado no cadastro.</p>
                                                                        <h2>
                                                                            <span>08</span> Retenção</h2>
                                                                                <p>Os dados são mantidos pelo período necessário para cumprir as finalidades descritas nesta política, atender obrigações legais e proteger direitos. Quando não houver mais necessidade legítima de retenção, os dados poderão ser eliminados ou anonimizados, observadas as exigências aplicáveis.</p>
                                                                                <h2>
                                                                                    <span>09</span> Direitos do titular</h2>
                                                                                        <p>Nos termos da legislação aplicável, o titular poderá solicitar confirmação da existência de tratamento, acesso, correção, atualização, informações sobre compartilhamento, anonimização, bloqueio ou eliminação quando cabível, além de outras medidas previstas em lei. Solicitações podem ser encaminhadas diretamente ao restaurante por meio do canal de atendimento disponível no sistema.</p>
                                                                                        <h2>
                                                                                            <span>10</span> Cookies e sessão</h2>
                                                                                                <p>O sistema pode utilizar cookies e mecanismos de sessão estritamente necessários para manter o usuário autenticado, proteger formulários e garantir o funcionamento das páginas. Esses recursos não têm como finalidade vender informações pessoais a terceiros.</p>
                                                                                                <h2>
                                                                                                    <span>11</span> Alterações desta política</h2>
                                                                                                        <p>Esta política poderá ser atualizada para refletir mudanças no sistema, nos processos internos ou na legislação. A versão mais recente ficará disponível nesta página, acompanhada da respectiva data de atualização.</p>
                                                                                                        <h2>
                                                                                                            <span>12</span> Contato</h2>
                                                                                                                <p>Em caso de dúvidas, solicitações ou questões relacionadas à privacidade, o cliente poderá utilizar o canal de perguntas dentro do MesaReserva ou entrar em contato com a administração do Restaurante MesaReserva.</p>
            </article>
                                                                                                        
        </div>
    </div>
    <?php
    if (is_admin()) {
        require __DIR__ . '/partials/footer.php';
    } else {
        require __DIR__ . '/partials/footer.php';
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Política de Privacidade | MesaReserva</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,300..600,0..1,0..200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/style.css?v=20261008painel3') ?>">
</head>
<body class="policy-body">
    <header class="public-nav">
        <a href="<?= url() ?>" class="public-brand">
            <img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva">
        </a>
        <nav><a href="<?= url('index.php#sobre') ?>">Sobre</a><a href="<?= url('institucional.php') ?>">Institucional</a><a href="<?= url('privacidade.php') ?>">Privacidade</a></nav>
        <div class="public-actions"><a class="btn btn-light btn-sm" href="<?= url('login.php') ?>">Entrar</a><a class="btn btn-primary btn-sm" href="<?= url('login.php') ?>">Reservar mesa</a></div>
        <button class="policy-back-btn" type="button" onclick="if(history.length > 1){history.back()}else{window.location.href='<?= url('index.php') ?>'}">
            <span class="material-symbols-outlined">arrow_back</span>
            <span>Voltar</span>
        </button>
    </header>
    <main>
        <div class="policy-wrap">
            
        <section class="policy-hero">
            <div class="policy-hero-icon">
                <span class="material-symbols-outlined">shield_lock</span>
            </div>
            <span class="section-kicker">TRANSPARÊNCIA E SEGURANÇA</span>
            <h1>Política de Privacidade</h1>
            <p>Entenda de forma simples como o MesaReserva utiliza e protege suas informações.</p>
            <div class="policy-meta">
                <span class="material-symbols-outlined">update</span> Última atualização: <?= date('d/m/Y') ?>
                </div>
            </section>
            <article class="policy-content">
                <div class="policy-intro">
                    <span class="material-symbols-outlined">verified_user</span>
                    <p>O Restaurante MesaReserva valoriza a privacidade, a segurança e a transparência no tratamento das informações de seus clientes, usuários e colaboradores. Esta Política de Privacidade explica, de forma clara, quais dados podem ser coletados, para quais finalidades são utilizados, como são protegidos e quais são os direitos dos titulares.</p>
                </div>
                <h2>
                    <span>01</span> Quem somos</h2>
                        <p>O MesaReserva é o sistema digital utilizado pelo Restaurante MesaReserva para organizar reservas de mesas, disponibilidades, atendimento e comunicação com seus clientes. Para fins desta política, “MesaReserva”, “nós” e “nosso” referem-se ao Restaurante MesaReserva e ao sistema por ele utilizado.</p>
                        <h2>
                            <span>02</span> Dados que podemos coletar</h2>
                                <p>Durante o cadastro e a utilização do sistema, podemos coletar nome, endereço de e-mail, telefone, informações relacionadas à reserva, quantidade de pessoas, data e horário escolhidos, mensagens enviadas ao atendimento e avaliações realizadas. Também podem ser registrados dados técnicos necessários ao funcionamento e à segurança do sistema, como informações de sessão e registros básicos de acesso.</p>
                                <h2>
                                    <span>03</span> Finalidades do tratamento</h2>
                                        <p>Os dados são utilizados para criar e administrar contas, confirmar a identidade por meio do e-mail, processar e organizar reservas, permitir contato entre cliente e restaurante, responder perguntas e solicitações, receber avaliações, melhorar a qualidade do atendimento, prevenir usos indevidos e cumprir obrigações legais aplicáveis.</p>
                                        <h2>
                                            <span>04</span> Base e princípios</h2>
                                                <p>O tratamento busca observar os princípios de finalidade, adequação, necessidade, livre acesso, qualidade dos dados, transparência, segurança, prevenção e não discriminação. Quando aplicável, o tratamento poderá ocorrer com base no consentimento, na execução de procedimentos solicitados pelo titular, no cumprimento de obrigação legal ou regulatória, no exercício regular de direitos e em outras bases previstas na legislação.</p>
                                                <h2>
                                                    <span>05</span> Compartilhamento</h2>
                                                        <p>Não comercializamos dados pessoais. Informações poderão ser compartilhadas apenas quando necessário para a prestação do serviço, para operação técnica autorizada, para cumprimento de obrigação legal, para proteção de direitos ou mediante autorização do titular, sempre buscando limitar o compartilhamento ao mínimo necessário.</p>
                                                        <h2>
                                                            <span>06</span> Segurança</h2>
                                                                <p>Adotamos medidas técnicas e organizacionais compatíveis com a finalidade do sistema, incluindo controle de acesso, sessões autenticadas, consultas parametrizadas e armazenamento de senhas utilizando funções de hash. Nenhum sistema conectado à internet pode garantir risco zero, mas trabalhamos para reduzir possibilidades de acesso indevido, alteração, perda ou divulgação não autorizada.</p>
                                                                <h2>
                                                                    <span>07</span> Senhas e autenticação</h2>
                                                                        <p>As senhas não devem ser armazenadas ou compartilhadas em texto puro. O sistema utiliza mecanismos de hash para armazenamento e verificação. A confirmação do e-mail é utilizada como camada adicional para validar o endereço informado no cadastro.</p>
                                                                        <h2>
                                                                            <span>08</span> Retenção</h2>
                                                                                <p>Os dados são mantidos pelo período necessário para cumprir as finalidades descritas nesta política, atender obrigações legais e proteger direitos. Quando não houver mais necessidade legítima de retenção, os dados poderão ser eliminados ou anonimizados, observadas as exigências aplicáveis.</p>
                                                                                <h2>
                                                                                    <span>09</span> Direitos do titular</h2>
                                                                                        <p>Nos termos da legislação aplicável, o titular poderá solicitar confirmação da existência de tratamento, acesso, correção, atualização, informações sobre compartilhamento, anonimização, bloqueio ou eliminação quando cabível, além de outras medidas previstas em lei. Solicitações podem ser encaminhadas diretamente ao restaurante por meio do canal de atendimento disponível no sistema.</p>
                                                                                        <h2>
                                                                                            <span>10</span> Cookies e sessão</h2>
                                                                                                <p>O sistema pode utilizar cookies e mecanismos de sessão estritamente necessários para manter o usuário autenticado, proteger formulários e garantir o funcionamento das páginas. Esses recursos não têm como finalidade vender informações pessoais a terceiros.</p>
                                                                                                <h2>
                                                                                                    <span>11</span> Alterações desta política</h2>
                                                                                                        <p>Esta política poderá ser atualizada para refletir mudanças no sistema, nos processos internos ou na legislação. A versão mais recente ficará disponível nesta página, acompanhada da respectiva data de atualização.</p>
                                                                                                        <h2>
                                                                                                            <span>12</span> Contato</h2>
                                                                                                                <p>Em caso de dúvidas, solicitações ou questões relacionadas à privacidade, o cliente poderá utilizar o canal de perguntas dentro do MesaReserva ou entrar em contato com a administração do Restaurante MesaReserva.</p>
            </article>
                                                                                                        
        </div>
    </main>
    
    <script src="<?= url('assets/js/app.js') ?>"></script>
<?php require __DIR__ . '/partials/footer-public.php'; ?>
</body>
</html>
