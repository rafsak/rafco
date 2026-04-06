<?php
/**
 * Radio Mehna V2 - Footer Template
 */
$lang = currentLang();
?>
</main>

<!-- Footer -->
<footer class="footer-mehna">
    <div class="container">
        <div class="row g-4">
            <!-- About -->
            <div class="col-lg-4 col-md-6">
                <h5><i class="bi bi-broadcast-pin text-accent"></i> <?= __('site.name') ?></h5>
                <p><?= __('footer.about_text') ?></p>
                <div class="footer-social mt-3">
                    <a href="https://facebook.com/radiomehna" target="_blank" rel="noopener" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.youtube.com/@RadioMehna-2024" target="_blank" rel="noopener" title="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="#" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5><?= __('footer.links') ?></h5>
                <ul>
                    <li><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                    <li><a href="<?= langUrl('programs') ?>"><?= __('nav.programs') ?></a></li>
                    <li><a href="<?= langUrl('podcasts') ?>"><?= __('nav.podcasts') ?></a></li>
                    <li><a href="<?= langUrl('news') ?>"><?= __('nav.news') ?></a></li>
                    <li><a href="<?= langUrl('contact') ?>"><?= __('nav.contact') ?></a></li>
                </ul>
            </div>

            <!-- Programs -->
            <div class="col-lg-3 col-md-6">
                <h5><?= __('nav.programs') ?></h5>
                <ul>
                    <li><a href="<?= langUrl('programs') ?>"><?= __('programs.all') ?></a></li>
                    <li><a href="<?= langUrl('programs/schedule') ?>"><?= __('nav.schedule') ?></a></li>
                    <li><a href="<?= langUrl('podcasts') ?>"><?= __('podcasts.all') ?></a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-lg-3 col-md-6">
                <h5><?= __('footer.newsletter') ?></h5>
                <p class="mb-3" style="font-size:0.85rem;"><?= __('footer.about_text') ?></p>
                <form class="d-flex gap-2" onsubmit="return false;">
                    <input type="email" class="form-control form-control-sm"
                           placeholder="<?= __('footer.email_placeholder') ?>"
                           style="background:var(--bg-input);border-color:var(--border-color);color:var(--text-primary);">
                    <button class="btn btn-accent btn-sm" type="button"><?= __('footer.subscribe') ?></button>
                </form>
            </div>
        </div>

        <!-- Bottom -->
        <div class="footer-bottom">
            <p><?= str_replace(':year', date('Y'), __('footer.copyright')) ?></p>
        </div>
    </div>
</footer>

<!-- Sticky Player Bar -->
<div class="player-bar" id="playerBar">
    <div class="player-info">
        <div class="waveform paused" id="playerWaveform">
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            <span class="bar"></span><span class="bar"></span><span class="bar"></span>
            <span class="bar"></span><span class="bar"></span>
        </div>
        <div class="player-details">
            <div class="player-title" id="currentProgramTitle"><?= __('site.name') ?></div>
            <div class="player-subtitle" id="playerStatus"><?= __('player.listen') ?></div>
        </div>
    </div>

    <div class="player-controls">
        <button class="player-btn-main" data-action="play-radio" title="<?= __('player.listen') ?>">
            <i class="bi bi-play-fill"></i>
        </button>
    </div>

    <div class="player-volume">
        <button class="player-btn" id="volumeBtn" title="<?= __('player.volume') ?>">
            <i class="bi bi-volume-up-fill"></i>
        </button>
        <input type="range" class="volume-slider" id="volumeSlider" min="0" max="100" value="80">
    </div>
</div>

<!-- Chatbot -->
<?php if (CHATBOT_ENABLED): ?>
<button class="chatbot-toggle" id="chatbotToggle" title="<?= __('chatbot.title') ?>">
    <i class="bi bi-chat-dots-fill"></i>
</button>

<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <h5><i class="bi bi-robot"></i> <?= __('chatbot.title') ?></h5>
        <button class="chatbot-close" id="chatbotClose"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages"></div>
    <div class="chatbot-input">
        <input type="text" id="chatbotInput" placeholder="<?= __('chatbot.placeholder') ?>" autocomplete="off">
        <button id="chatbotSend" title="<?= __('chatbot.send') ?>"><i class="bi bi-send-fill"></i></button>
    </div>
</div>
<?php endif; ?>

<!-- JS Translations -->
<script>
window.RadioMehnaTranslations = <?= json_encode(loadTranslations($lang), JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?= ASSETS_URL ?>/js/app.js"></script>

</body>
</html>
