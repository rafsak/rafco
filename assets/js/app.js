/**
 * Radio Mehna V2 - Application JavaScript
 * Player, Chatbot, Theme, Search, Interactions
 */

document.addEventListener('DOMContentLoaded', function () {
    RadioMehna.init();
});

const RadioMehna = {
    // Configuration
    config: {
        streamUrl: 'https://stream6.tanitweb.com/radiomehna',
        baseUrl: window.location.origin,
        lang: document.documentElement.lang || 'fr',
    },

    // Audio element
    audio: null,
    isPlaying: false,

    /**
     * Initialisation
     */
    init: function () {
        this.initTheme();
        this.initPlayer();
        this.initChatbot();
        this.initSearch();
        this.initLazyLoad();
        this.initMicroInteractions();
        this.initPWA();
    },

    // ==========================================
    // THEME (Dark/Light)
    // ==========================================
    initTheme: function () {
        const saved = localStorage.getItem('theme') || 'dark';
        this.setTheme(saved);

        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                this.setTheme(next);
            });
        }
    },

    setTheme: function (theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);

        const icon = document.querySelector('#themeToggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
    },

    // ==========================================
    // RADIO PLAYER
    // ==========================================
    initPlayer: function () {
        this.audio = new Audio();
        this.audio.preload = 'none';

        // Player controls
        const playBtns = document.querySelectorAll('[data-action="play-radio"]');
        playBtns.forEach(btn => {
            btn.addEventListener('click', () => this.toggleRadio());
        });

        // Volume
        const volumeSlider = document.getElementById('volumeSlider');
        if (volumeSlider) {
            volumeSlider.addEventListener('input', (e) => {
                this.audio.volume = e.target.value / 100;
                localStorage.setItem('volume', e.target.value);
            });

            // Restore volume
            const savedVol = localStorage.getItem('volume');
            if (savedVol) {
                volumeSlider.value = savedVol;
                this.audio.volume = savedVol / 100;
            }
        }

        // Volume icon toggle
        const volumeBtn = document.getElementById('volumeBtn');
        if (volumeBtn) {
            volumeBtn.addEventListener('click', () => {
                if (this.audio.muted) {
                    this.audio.muted = false;
                    volumeBtn.querySelector('i').className = 'bi bi-volume-up-fill';
                } else {
                    this.audio.muted = true;
                    volumeBtn.querySelector('i').className = 'bi bi-volume-mute-fill';
                }
            });
        }

        // Audio events
        this.audio.addEventListener('playing', () => this.onRadioPlay());
        this.audio.addEventListener('pause', () => this.onRadioPause());
        this.audio.addEventListener('error', () => this.onRadioError());
        this.audio.addEventListener('waiting', () => this.onRadioLoading());

        // Podcast player
        this.initPodcastPlayer();

        // Check current program
        this.checkCurrentProgram();
        setInterval(() => this.checkCurrentProgram(), 60000);
    },

    toggleRadio: function () {
        if (this.isPlaying) {
            this.audio.pause();
            this.audio.src = '';
        } else {
            this.audio.src = this.config.streamUrl;
            this.audio.load();
            this.audio.play().catch(() => {});
        }
    },

    onRadioPlay: function () {
        this.isPlaying = true;
        this.updatePlayerUI(true);
    },

    onRadioPause: function () {
        this.isPlaying = false;
        this.updatePlayerUI(false);
    },

    onRadioError: function () {
        this.isPlaying = false;
        this.updatePlayerUI(false);
        const status = document.getElementById('playerStatus');
        if (status) status.textContent = this.getTranslation('player.offline');
    },

    onRadioLoading: function () {
        const status = document.getElementById('playerStatus');
        if (status) status.textContent = this.getTranslation('player.loading');
    },

    updatePlayerUI: function (playing) {
        // Update all play buttons
        document.querySelectorAll('[data-action="play-radio"]').forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = playing ? 'bi bi-pause-fill' : 'bi bi-play-fill';
            }
        });

        // Waveforms
        document.querySelectorAll('.waveform').forEach(w => {
            w.classList.toggle('paused', !playing);
        });

        // Live badge
        const liveBadge = document.querySelector('.live-badge');
        if (liveBadge) {
            liveBadge.style.opacity = playing ? '1' : '0.5';
        }

        // Status text
        const status = document.getElementById('playerStatus');
        if (status) {
            status.textContent = playing
                ? this.getTranslation('player.now_playing')
                : this.getTranslation('player.listen');
        }
    },

    checkCurrentProgram: function () {
        fetch(this.config.baseUrl + '/ajax/current-program.php')
            .then(r => r.json())
            .then(data => {
                const titleEl = document.getElementById('currentProgramTitle');
                if (titleEl && data.title) {
                    titleEl.textContent = data.title;
                }
            })
            .catch(() => {});
    },

    // ==========================================
    // PODCAST PLAYER
    // ==========================================
    initPodcastPlayer: function () {
        const podcastAudio = document.getElementById('podcastAudio');
        if (!podcastAudio) return;

        const playBtn = document.getElementById('podcastPlayBtn');
        const progressBar = document.getElementById('podcastProgress');
        const progressFill = document.getElementById('podcastProgressFill');
        const currentTime = document.getElementById('podcastCurrentTime');
        const totalTime = document.getElementById('podcastTotalTime');

        if (playBtn) {
            playBtn.addEventListener('click', () => {
                if (podcastAudio.paused) {
                    // Pause radio if playing
                    if (this.isPlaying) {
                        this.audio.pause();
                        this.audio.src = '';
                    }
                    podcastAudio.play();
                    playBtn.querySelector('i').className = 'bi bi-pause-fill';
                } else {
                    podcastAudio.pause();
                    playBtn.querySelector('i').className = 'bi bi-play-fill';
                }
            });
        }

        podcastAudio.addEventListener('timeupdate', () => {
            if (podcastAudio.duration) {
                const pct = (podcastAudio.currentTime / podcastAudio.duration) * 100;
                if (progressFill) progressFill.style.width = pct + '%';
                if (currentTime) currentTime.textContent = this.formatTime(podcastAudio.currentTime);
            }
        });

        podcastAudio.addEventListener('loadedmetadata', () => {
            if (totalTime) totalTime.textContent = this.formatTime(podcastAudio.duration);
        });

        if (progressBar) {
            progressBar.addEventListener('click', (e) => {
                const rect = progressBar.getBoundingClientRect();
                const pct = (e.clientX - rect.left) / rect.width;
                podcastAudio.currentTime = pct * podcastAudio.duration;
            });
        }

        // Track play
        podcastAudio.addEventListener('play', () => {
            const podcastId = podcastAudio.dataset.podcastId;
            if (podcastId) {
                fetch(this.config.baseUrl + '/ajax/track-play.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'podcast_id=' + podcastId,
                }).catch(() => {});
            }
        });
    },

    formatTime: function (seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    },

    // ==========================================
    // CHATBOT IA
    // ==========================================
    initChatbot: function () {
        const toggle = document.getElementById('chatbotToggle');
        const window_ = document.getElementById('chatbotWindow');
        const closeBtn = document.getElementById('chatbotClose');
        const input = document.getElementById('chatbotInput');
        const sendBtn = document.getElementById('chatbotSend');

        if (!toggle || !window_) return;

        toggle.addEventListener('click', () => {
            window_.classList.toggle('active');
            if (window_.classList.contains('active')) {
                input?.focus();
                // Show welcome message
                const msgs = document.getElementById('chatbotMessages');
                if (msgs && msgs.children.length === 0) {
                    this.addChatMessage('bot', this.getTranslation('chatbot.welcome'));
                    this.showSuggestions();
                }
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                window_.classList.remove('active');
            });
        }

        if (sendBtn) {
            sendBtn.addEventListener('click', () => this.sendChatMessage());
        }

        if (input) {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.sendChatMessage();
            });
        }
    },

    sendChatMessage: function () {
        const input = document.getElementById('chatbotInput');
        const message = input?.value.trim();
        if (!message) return;

        this.addChatMessage('user', message);
        input.value = '';

        // Loading indicator
        this.addChatMessage('bot', '<em>' + this.getTranslation('chatbot.thinking') + '</em>', true);

        fetch(this.config.baseUrl + '/ajax/chatbot.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: 'message=' + encodeURIComponent(message) + '&lang=' + this.config.lang,
        })
            .then(r => r.json())
            .then(data => {
                // Remove loading
                const msgs = document.getElementById('chatbotMessages');
                const loading = msgs?.querySelector('.chat-loading');
                if (loading) loading.remove();

                // Display response
                this.addChatMessage('bot', data.message);

                // Display items if any
                if (data.data && data.data.length > 0) {
                    let html = '<div class="chat-items">';
                    data.data.forEach(item => {
                        html += '<div class="chat-item"><strong>' + this.escapeHtml(item.title) + '</strong>';
                        if (item.description) {
                            html += '<br><small>' + this.escapeHtml(item.description).substring(0, 80) + '...</small>';
                        }
                        html += '</div>';
                    });
                    html += '</div>';
                    this.addChatMessage('bot', html, false, true);
                }

                // Display suggestions
                if (data.suggestions && data.suggestions.length > 0) {
                    this.showSuggestionButtons(data.suggestions);
                }
            })
            .catch(() => {
                const msgs = document.getElementById('chatbotMessages');
                const loading = msgs?.querySelector('.chat-loading');
                if (loading) loading.remove();
                this.addChatMessage('bot', this.getTranslation('chatbot.error'));
            });
    },

    addChatMessage: function (type, content, isLoading, isHtml) {
        const msgs = document.getElementById('chatbotMessages');
        if (!msgs) return;

        const div = document.createElement('div');
        div.className = 'chat-message ' + type;
        if (isLoading) div.classList.add('chat-loading');

        if (isHtml) {
            div.innerHTML = content;
        } else {
            div.innerHTML = content;
        }

        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
    },

    showSuggestions: function () {
        const suggestions = this.getDefaultSuggestions();
        this.showSuggestionButtons(suggestions);
    },

    showSuggestionButtons: function (suggestions) {
        const msgs = document.getElementById('chatbotMessages');
        if (!msgs) return;

        const div = document.createElement('div');
        div.className = 'chat-message bot';

        let html = '<div class="chat-suggestions">';
        suggestions.forEach(s => {
            html += '<button class="chat-suggestion-btn" onclick="RadioMehna.handleSuggestion(this)">' + this.escapeHtml(s) + '</button>';
        });
        html += '</div>';
        div.innerHTML = html;
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
    },

    handleSuggestion: function (btn) {
        const input = document.getElementById('chatbotInput');
        if (input) {
            input.value = btn.textContent;
            this.sendChatMessage();
        }
    },

    getDefaultSuggestions: function () {
        const suggestions = {
            fr: ['Émissions du jour', 'Podcasts populaires', 'Comment écouter ?', 'Contact'],
            ar: ['برامج اليوم', 'بودكاست شائعة', 'كيف أستمع؟', 'اتصل بنا'],
            en: ['Today\'s shows', 'Popular podcasts', 'How to listen?', 'Contact'],
        };
        return suggestions[this.config.lang] || suggestions.fr;
    },

    // ==========================================
    // SEARCH
    // ==========================================
    initSearch: function () {
        const searchInput = document.getElementById('globalSearch');
        const resultsDropdown = document.getElementById('searchResults');

        if (!searchInput || !resultsDropdown) return;

        let searchTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const query = searchInput.value.trim();

            if (query.length < 2) {
                resultsDropdown.classList.remove('active');
                return;
            }

            searchTimer = setTimeout(() => {
                this.performSearch(query, resultsDropdown);
            }, 300);
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-box')) {
                resultsDropdown.classList.remove('active');
            }
        });
    },

    performSearch: function (query, dropdown) {
        fetch(this.config.baseUrl + '/ajax/search.php?q=' + encodeURIComponent(query) + '&lang=' + this.config.lang, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                if (data.results && data.results.length > 0) {
                    let html = '';
                    data.results.forEach(item => {
                        const typeLabels = { program: 'Émission', podcast: 'Podcast', post: 'Article' };
                        html += '<a href="' + this.config.baseUrl + '/' + this.config.lang + '/' + item.type + '/' + item.slug + '" class="search-result-item">';
                        html += '<div><span class="search-result-type">' + (typeLabels[item.type] || item.type) + '</span>';
                        html += '<div class="fw-semibold">' + this.escapeHtml(item.title) + '</div></div>';
                        html += '</a>';
                    });
                    dropdown.innerHTML = html;
                    dropdown.classList.add('active');
                } else {
                    dropdown.innerHTML = '<div class="p-3 text-center text-muted">' + this.getTranslation('search.no_results') + '</div>';
                    dropdown.classList.add('active');
                }
            })
            .catch(() => {
                dropdown.classList.remove('active');
            });
    },

    // ==========================================
    // LAZY LOAD
    // ==========================================
    initLazyLoad: function () {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            img.classList.add('loaded');
                        }
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => observer.observe(img));
        }
    },

    // ==========================================
    // MICRO-INTERACTIONS
    // ==========================================
    initMicroInteractions: function () {
        // Cards hover glow effect
        document.querySelectorAll('.card-mehna').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', x + 'px');
                card.style.setProperty('--mouse-y', y + 'px');
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    },

    // ==========================================
    // PWA
    // ==========================================
    initPWA: function () {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    },

    // ==========================================
    // LANGUAGE SWITCH (AJAX)
    // ==========================================
    switchLanguage: function (lang) {
        fetch(this.config.baseUrl + '/ajax/switch-lang.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: 'lang=' + lang,
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Redirect to the same page in new language
                    const path = window.location.pathname;
                    const segments = path.split('/').filter(Boolean);
                    const supportedLangs = ['fr', 'ar', 'en'];
                    if (supportedLangs.includes(segments[0])) {
                        segments[0] = lang;
                    } else {
                        segments.unshift(lang);
                    }
                    window.location.href = '/' + segments.join('/');
                }
            })
            .catch(() => {
                window.location.href = '/' + lang + '/';
            });
    },

    // ==========================================
    // HELPERS
    // ==========================================
    escapeHtml: function (text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    getTranslation: function (key) {
        const translations = window.RadioMehnaTranslations || {};
        const keys = key.split('.');
        let val = translations;
        for (const k of keys) {
            if (val && typeof val === 'object' && k in val) {
                val = val[k];
            } else {
                return key;
            }
        }
        return typeof val === 'string' ? val : key;
    },
};
