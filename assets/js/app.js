/**
 * ATFP Chatbot — Front-end vanilla JS
 */
(function () {
    'use strict';

    // -- Config --
    const API_URL = 'chat.php';
    let lang = 'fr';
    let sessionId = localStorage.getItem('atfp_session') || generateId();
    localStorage.setItem('atfp_session', sessionId);

    // -- DOM --
    const $messages   = document.getElementById('chatMessages');
    const $input      = document.getElementById('chatInput');
    const $sendBtn    = document.getElementById('sendBtn');
    const $typing     = document.getElementById('typingIndicator');
    const $langToggle = document.getElementById('langToggle');
    const $welcome    = document.getElementById('welcomeOverlay');
    const $startBtn   = document.getElementById('startBtn');
    const $quickArea  = document.getElementById('quickReplies');

    // -- Textes i18n --
    const i18n = {
        fr: {
            placeholder: 'Écrivez votre message...',
            title: 'ATFP Chatbot',
            status: 'En ligne',
            welcome_title: 'Bienvenue !',
            welcome_text: 'Je suis votre assistant d\'orientation ATFP. Je vous aide à trouver la formation idéale.',
            start: 'Commencer',
            toggle: 'عربي',
        },
        ar: {
            placeholder: 'اكتب رسالتك...',
            title: 'روبوت ATFP',
            status: 'متصل',
            welcome_title: '!مرحبا بك',
            welcome_text: 'أنا مساعدك في التوجيه المهني ATFP. سأساعدك في إيجاد التكوين المثالي.',
            start: 'ابدأ',
            toggle: 'Français',
        }
    };

    // -- Init --
    function init() {
        updateUI();
        bindEvents();
    }

    function bindEvents() {
        $sendBtn.addEventListener('click', sendMessage);
        $input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        $langToggle.addEventListener('click', toggleLang);
        $startBtn.addEventListener('click', startChat);
    }

    // -- Langue --
    function toggleLang() {
        lang = lang === 'fr' ? 'ar' : 'fr';
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
        updateUI();
    }

    function updateUI() {
        const t = i18n[lang];
        $input.placeholder = t.placeholder;
        document.getElementById('headerTitle').textContent = t.title;
        document.getElementById('headerStatus').textContent = t.status;
        document.getElementById('welcomeTitle').textContent = t.welcome_title;
        document.getElementById('welcomeText').textContent = t.welcome_text;
        $startBtn.textContent = t.start;
        $langToggle.textContent = t.toggle;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    }

    // -- Démarrer le chat --
    function startChat() {
        $welcome.classList.add('hidden');
        // Message de bienvenue auto
        sendToAPI(lang === 'ar' ? 'مرحبا' : 'bonjour');
    }

    // -- Envoyer un message --
    function sendMessage() {
        const text = $input.value.trim();
        if (!text) return;
        $input.value = '';
        appendMessage('user', text);
        clearQuickReplies();
        sendToAPI(text);
    }

    // -- Appel API --
    async function sendToAPI(message) {
        showTyping(true);

        try {
            const res = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    session_id: sessionId,
                    lang: lang,
                }),
            });

            const data = await res.json();

            showTyping(false);

            if (data.error) {
                appendMessage('bot', '⚠️ ' + data.error);
                return;
            }

            if (data.session_id) {
                sessionId = data.session_id;
                localStorage.setItem('atfp_session', sessionId);
            }

            // Render reply with simple markdown
            const rendered = renderMarkdown(data.reply || '');
            appendMessage('bot', rendered, true);

            // Quick replies based on intent
            showQuickReplies(data.intent, data.data);

        } catch (err) {
            showTyping(false);
            const errMsg = lang === 'ar'
                ? '⚠️ خطأ في الاتصال. حاول مرة أخرى.'
                : '⚠️ Erreur de connexion. Réessayez.';
            appendMessage('bot', errMsg);
        }
    }

    // -- Ajouter un message au DOM --
    function appendMessage(role, content, isHtml) {
        const wrapper = document.createElement('div');
        wrapper.className = 'message ' + role;

        const avatar = document.createElement('div');
        avatar.className = 'msg-avatar';
        avatar.textContent = role === 'bot' ? '🤖' : '👤';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        if (isHtml) {
            bubble.innerHTML = content;
        } else {
            bubble.textContent = content;
        }

        wrapper.appendChild(avatar);
        wrapper.appendChild(bubble);
        $messages.appendChild(wrapper);

        scrollToBottom();
    }

    // -- Quick replies --
    // options: [{label, value}] — label affiché avec emoji, value envoyé à l'API (texte brut)
    function showQuickReplies(intent, data) {
        clearQuickReplies();

        let options = [];

        if (intent === 'salutation' || intent === 'aide') {
            if (lang === 'ar') {
                options = [
                    {label: '🎓 الاختصاصات', value: 'اختصاصات'},
                    {label: '🏢 المراكز', value: 'مراكز'},
                    {label: '🧭 توجيهني', value: 'توجيه'},
                    {label: '📋 التسجيل', value: 'تسجيل'},
                    {label: 'ℹ️ ما هي ATFP', value: 'ما هي atfp'}
                ];
            } else {
                options = [
                    {label: '🎓 Spécialités', value: 'spécialités'},
                    {label: '🏢 Centres', value: 'centres'},
                    {label: '🧭 Orientez-moi', value: 'orientation'},
                    {label: '📋 Inscription', value: 'inscription'},
                    {label: 'ℹ️ C\'est quoi ATFP', value: "c'est quoi atfp"}
                ];
            }
        } else if (intent === 'specialites' && data && data.items) {
            options = data.items.map(function (s) { return {label: s.nom, value: s.nom}; });
        } else if (intent === 'orientation') {
            if (data && data.step === 'interets') {
                if (lang === 'ar') {
                    options = [
                        {label: '💻 إعلامية', value: 'إعلامية'},
                        {label: '⚡ كهرباء', value: 'كهرباء'},
                        {label: '🔧 ميكانيك', value: 'ميكانيك'},
                        {label: '🏗️ بناء', value: 'بناء'},
                        {label: '🏨 سياحة', value: 'سياحة'},
                        {label: '✂️ تجميل', value: 'تجميل'},
                        {label: '📊 تجارة', value: 'تجارة'}
                    ];
                } else {
                    options = [
                        {label: '💻 Informatique', value: 'informatique'},
                        {label: '⚡ Électricité', value: 'électricité'},
                        {label: '🔧 Mécanique', value: 'mécanique'},
                        {label: '🏗️ BTP', value: 'btp'},
                        {label: '🏨 Tourisme', value: 'tourisme'},
                        {label: '✂️ Esthétique', value: 'esthétique'},
                        {label: '📊 Commerce', value: 'commerce'}
                    ];
                }
            }
        }

        if (options.length === 0) return;

        options.forEach(function (opt) {
            const btn = document.createElement('button');
            btn.className = 'quick-reply-btn';
            btn.textContent = opt.label;
            btn.addEventListener('click', function () {
                appendMessage('user', opt.label);
                clearQuickReplies();
                sendToAPI(opt.value);
            });
            $quickArea.appendChild(btn);
        });
    }

    function clearQuickReplies() {
        $quickArea.innerHTML = '';
    }

    // -- Typing indicator --
    function showTyping(show) {
        $typing.classList.toggle('active', show);
        if (show) scrollToBottom();
    }

    // -- Scroll --
    function scrollToBottom() {
        requestAnimationFrame(function () {
            $messages.scrollTop = $messages.scrollHeight;
        });
    }

    // -- Simple markdown --
    function renderMarkdown(text) {
        // Escape HTML first
        let safe = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold: **text**
        safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Line breaks
        safe = safe.replace(/\n/g, '<br>');

        return safe;
    }

    // -- Helpers --
    function generateId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // -- Go --
    document.addEventListener('DOMContentLoaded', init);
})();
