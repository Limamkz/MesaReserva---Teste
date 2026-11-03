<?php
$layoutType = $layoutType ?? 'public';
?>

<?php if ($layoutType === 'admin' || $layoutType === 'client'): ?>
</main>
<?php endif; ?>

<footer class="site-footer">
    <div class="site-footer-inner">
        <div class="site-footer-grid">
            <div class="site-footer-brand">
                <a class="site-footer-logo" href="<?= url('index.php') ?>">
                    <img src="<?= url('assets/img/logo.png') ?>" alt="MesaReserva">
                </a>
                <span>Sua mesa, seu horário, sua reserva.</span>
            </div>

            <div class="site-footer-column">
                <h4>Institucional</h4>
                <a href="<?= url('institucional.php') ?>">Missão, visão e valores</a>
                <a href="<?= url('privacidade.php') ?>">Política de privacidade</a>
                <a href="<?= url('index.php') ?>#sobre">Sobre o MesaReserva</a>
            </div>

            <div class="site-footer-column">
                <h4>Atendimento</h4>
                <a href="<?= url('perguntas.php') ?>">Central de ajuda</a>
                <a href="<?= url('login.php') ?>">Entrar na plataforma</a>
                <?php if ($layoutType === 'admin' || $layoutType === 'client'): ?>
                    <a href="<?= url('logout.php') ?>">Sair da conta</a>
                <?php endif; ?>
            </div>

            <div class="site-footer-column site-footer-contact">
                <h4>Fale com a gente</h4>
                <p>Restaurante MesaReserva</p>
                <div class="site-footer-socials" aria-label="Redes sociais e contato">
                    <a class="site-social" href="https://www.instagram.com/mesa.reserva/" target="_blank" rel="noopener" aria-label="Instagram MesaReserva" title="Instagram">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="5"></rect>
                            <circle cx="12" cy="12" r="4"></circle>
                            <circle cx="17.4" cy="6.6" r="1" class="social-dot"></circle>
                        </svg>
                        <span>Instagram</span>
                    </a>
                    <a class="site-social" href="https://wa.me/5511986926518" target="_blank" rel="noopener" aria-label="WhatsApp MesaReserva" title="WhatsApp">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.5 3.5A11.7 11.7 0 0 0 12.1 0C5.6 0 .3 5.2.3 11.7c0 2.1.6 4.2 1.7 6L.2 24l6.5-1.7a11.8 11.8 0 0 0 5.4 1.4h.1c6.5 0 11.8-5.2 11.8-11.7 0-3.2-1.2-6.2-3.5-8.5Zm-8.4 18.2h-.1a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.8 1 1-3.7-.2-.4a9.7 9.7 0 0 1-1.5-5.2C2.1 6.4 6.6 2 12.1 2c2.7 0 5.2 1 7.1 2.9a9.8 9.8 0 0 1 2.9 7c0 5.4-4.5 9.8-10 9.8Zm5.5-7.3c-.3-.1-1.8-.9-2.1-1-.3-.1-.5-.1-.7.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1-1.8-.9-3-1.6-4.2-3.6-.3-.5.3-.5.9-1.6.1-.2.1-.4 0-.6l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.2 2.4 3.7 5.9 5.2.8.4 1.5.6 2 .7.8.3 1.6.2 2.2.1.7-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.6-.3Z"></path>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <span>© <?= date('Y') ?> Restaurante MesaReserva. Todos os direitos reservados.</span>
            <span>MesaReserva · v<?= APP_VERSION ?></span>
        </div>
    </div>
</footer>

<?php if ($layoutType === 'admin' || $layoutType === 'client'): ?>
</div>
</div>
<?php endif; ?>

<script src="<?= url('assets/js/app.js?v=20261006f') ?>"></script>
