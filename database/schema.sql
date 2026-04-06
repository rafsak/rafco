-- ============================================
-- Radio Mehna V2 - Schéma de Base de Données
-- MySQL / MariaDB
-- ============================================

CREATE DATABASE IF NOT EXISTS radio_mehna
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE radio_mehna;

-- ============================================
-- Table: roles
-- ============================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: users
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    role_id INT NOT NULL DEFAULT 2,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: programs (émissions)
-- ============================================
CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    host_id INT DEFAULT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    broadcast_days VARCHAR(255) DEFAULT NULL COMMENT 'monday,tuesday,...',
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: podcasts
-- ============================================
CREATE TABLE IF NOT EXISTS podcasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    audio_file VARCHAR(500) NOT NULL,
    image VARCHAR(500) DEFAULT NULL,
    duration INT DEFAULT 0 COMMENT 'durée en secondes',
    category VARCHAR(100) DEFAULT NULL,
    program_id INT DEFAULT NULL,
    author_id INT DEFAULT NULL,
    play_count INT DEFAULT 0,
    is_published TINYINT(1) DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_category (category),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: posts (articles/actualités)
-- ============================================
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    author_id INT DEFAULT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: media
-- ============================================
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) DEFAULT NULL,
    type ENUM('image', 'audio', 'video', 'document') DEFAULT 'image',
    mime_type VARCHAR(100) DEFAULT NULL,
    size BIGINT DEFAULT 0,
    path VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: translations (traductions dynamiques)
-- ============================================
CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    lang VARCHAR(5) NOT NULL,
    value TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_translation (table_name, record_id, field_name, lang),
    INDEX idx_record (table_name, record_id, lang)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: chatbot_data (réponses IA)
-- ============================================
CREATE TABLE IF NOT EXISTS chatbot_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    keywords TEXT NOT NULL COMMENT 'mots-clés séparés par virgules',
    category VARCHAR(100) DEFAULT 'general',
    lang VARCHAR(5) NOT NULL DEFAULT 'fr',
    priority INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lang (lang),
    INDEX idx_category (category),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: analytics
-- ============================================
CREATE TABLE IF NOT EXISTS analytics (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    page_url VARCHAR(500) DEFAULT NULL,
    page_type VARCHAR(50) DEFAULT 'page',
    page_id INT DEFAULT 0,
    action VARCHAR(50) DEFAULT 'view',
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    language VARCHAR(5) DEFAULT 'fr',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page_type (page_type),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: logs
-- ============================================
CREATE TABLE IF NOT EXISTS logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: contacts (messages de contact)
-- ============================================
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: settings (paramètres du site)
-- ============================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type VARCHAR(50) DEFAULT 'text',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- Rôles
INSERT INTO roles (name, description) VALUES
('admin', 'Administrateur complet'),
('editor', 'Éditeur de contenu'),
('host', 'Animateur d\'émission');

-- Utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO users (username, email, password, full_name, role_id) VALUES
('admin', 'admin@radiomehna.tn', '$2y$12$LJ3m4ys3Kl5gHBpXF0eWe.6B4x2bX5HJ1YoKmKvL0fStMxDIyMtZm', 'Administrateur', 1);

-- Paramètres par défaut
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'Radio Mehna', 'text'),
('site_tagline', 'La voix de la Tunisie', 'text'),
('site_email', 'contact@radiomehna.tn', 'email'),
('site_phone', '+216 XX XXX XXX', 'text'),
('site_address', 'Tunisie', 'text'),
('stream_url', 'https://stream6.tanitweb.com/radiomehna', 'url'),
('facebook_url', 'https://facebook.com/radiomehna', 'url'),
('youtube_url', 'https://www.youtube.com/@RadioMehna-2024', 'url'),
('instagram_url', '', 'url'),
('twitter_url', '', 'url'),
('default_language', 'fr', 'text'),
('analytics_enabled', '1', 'boolean'),
('chatbot_enabled', '1', 'boolean');

-- Données chatbot par défaut (FR)
INSERT INTO chatbot_data (question, answer, keywords, category, lang, priority) VALUES
('Comment écouter la radio ?', 'Vous pouvez écouter Radio Mehna en direct en cliquant sur le bouton "Écouter en direct" sur notre page d\'accueil, ou en utilisant le player en bas de page.', 'écouter,radio,direct,live,stream', 'general', 'fr', 10),
('Quels sont les horaires ?', 'Radio Mehna diffuse 24h/24 et 7j/7. Consultez notre grille des programmes pour connaître les émissions du jour.', 'horaires,heures,programme,grille,diffusion', 'schedule', 'fr', 9),
('Comment contacter Radio Mehna ?', 'Vous pouvez nous contacter via notre page de contact, par email à contact@radiomehna.tn ou via nos réseaux sociaux.', 'contact,contacter,email,téléphone,adresse', 'contact', 'fr', 8),
('Où écouter les podcasts ?', 'Retrouvez tous nos podcasts dans la section "Podcasts" de notre site. Vous pouvez les écouter en ligne ou les télécharger.', 'podcast,podcasts,écouter,télécharger', 'podcasts', 'fr', 7),
('Quelles sont les émissions ?', 'Découvrez toutes nos émissions dans la section "Émissions". Chaque émission a sa page dédiée avec les détails et les horaires.', 'émission,émissions,programme,programmes', 'programs', 'fr', 7);

-- Données chatbot (AR)
INSERT INTO chatbot_data (question, answer, keywords, category, lang, priority) VALUES
('كيف أستمع للراديو؟', 'يمكنك الاستماع إلى راديو مهنة مباشرة عبر الضغط على زر "استمع مباشرة" في الصفحة الرئيسية أو عبر المشغل في أسفل الصفحة.', 'استمع,راديو,مباشر,بث', 'general', 'ar', 10),
('ما هي مواعيد البث؟', 'راديو مهنة يبث على مدار الساعة طوال أيام الأسبوع. راجع جدول البرامج لمعرفة برامج اليوم.', 'مواعيد,بث,جدول,برامج', 'schedule', 'ar', 9),
('كيف أتواصل مع راديو مهنة؟', 'يمكنك التواصل معنا عبر صفحة الاتصال أو عبر البريد الإلكتروني contact@radiomehna.tn أو عبر شبكات التواصل الاجتماعي.', 'تواصل,اتصال,بريد,هاتف', 'contact', 'ar', 8);

-- Données chatbot (EN)
INSERT INTO chatbot_data (question, answer, keywords, category, lang, priority) VALUES
('How to listen to the radio?', 'You can listen to Radio Mehna live by clicking the "Listen Live" button on our homepage, or by using the player at the bottom of the page.', 'listen,radio,live,stream,play', 'general', 'en', 10),
('What are the schedules?', 'Radio Mehna broadcasts 24/7. Check our program schedule to see today\'s shows.', 'schedule,hours,program,time,broadcast', 'schedule', 'en', 9),
('How to contact Radio Mehna?', 'You can contact us through our contact page, by email at contact@radiomehna.tn or via our social media.', 'contact,email,phone,address,reach', 'contact', 'en', 8);

-- Émissions de démonstration
INSERT INTO programs (title, slug, description, host_id, start_time, end_time, broadcast_days, is_active, is_featured, sort_order) VALUES
('Sabah El Mehna', 'sabah-el-mehna', 'Émission matinale pour bien commencer la journée avec de la musique et des discussions enrichissantes.', 1, '07:00:00', '09:00:00', 'monday,tuesday,wednesday,thursday,friday', 1, 1, 1),
('Musique Sans Frontières', 'musique-sans-frontieres', 'Un voyage musical à travers les genres et les cultures du monde entier.', 1, '10:00:00', '12:00:00', 'monday,wednesday,friday', 1, 1, 2),
('Culture & Patrimoine', 'culture-patrimoine', 'Découverte du patrimoine culturel tunisien et des traditions locales.', 1, '14:00:00', '15:30:00', 'tuesday,thursday', 1, 1, 3),
('Soirée Tarab', 'soiree-tarab', 'Les plus belles voix de la musique arabe classique et moderne.', 1, '20:00:00', '22:00:00', 'monday,tuesday,wednesday,thursday,friday,saturday,sunday', 1, 1, 4),
('Weekend Vibes', 'weekend-vibes', 'Ambiance détente et musique variée pour le weekend.', 1, '10:00:00', '14:00:00', 'saturday,sunday', 1, 0, 5),
('Jeunesse & Avenir', 'jeunesse-avenir', 'Émission dédiée aux jeunes : orientation, emploi, formation professionnelle.', 1, '16:00:00', '17:30:00', 'wednesday,saturday', 1, 0, 6);

-- Podcasts de démonstration
INSERT INTO podcasts (title, slug, description, audio_file, duration, category, program_id, author_id, play_count, is_published, published_at) VALUES
('Sabah El Mehna - Épisode 1', 'sabah-el-mehna-ep1', 'Premier épisode de Sabah El Mehna - Interview exclusive avec un artiste local.', 'demo-podcast-1.mp3', 3600, 'Matinale', 1, 1, 150, 1, '2024-12-01 08:00:00'),
('Musique Sans Frontières - Jazz Session', 'msf-jazz-session', 'Une session jazz mémorable avec des musiciens internationaux.', 'demo-podcast-2.mp3', 5400, 'Musique', 2, 1, 230, 1, '2024-12-05 11:00:00'),
('Culture & Patrimoine - Médina de Tunis', 'culture-medina-tunis', 'Visite guidée de la Médina de Tunis et ses trésors cachés.', 'demo-podcast-3.mp3', 2700, 'Culture', 3, 1, 180, 1, '2024-12-10 15:00:00'),
('Soirée Tarab - Best of Oum Kalthoum', 'tarab-oum-kalthoum', 'Les plus belles chansons d\'Oum Kalthoum revisitées.', 'demo-podcast-4.mp3', 7200, 'Tarab', 4, 1, 450, 1, '2024-12-15 21:00:00');

-- Articles de démonstration
INSERT INTO posts (title, slug, excerpt, content, author_id, status, views, published_at) VALUES
('Radio Mehna lance sa nouvelle plateforme digitale', 'radio-mehna-nouvelle-plateforme', 'Radio Mehna dévoile sa plateforme digitale V2, une expérience radio réinventée pour l\'ère numérique.', '<p>Radio Mehna est fière d\'annoncer le lancement de sa nouvelle plateforme digitale V2. Cette refonte complète offre une expérience utilisateur moderne et immersive.</p><p>Parmi les nouvelles fonctionnalités :</p><ul><li>Un player radio repensé avec animations audio</li><li>Une section podcasts enrichie</li><li>Un assistant IA intelligent</li><li>Un support multilingue complet (FR, AR, EN)</li></ul><p>Nous sommes impatients de vous faire découvrir cette nouvelle version !</p>', 1, 'published', 320, '2024-12-01 10:00:00'),
('Festival de musique arabe : les temps forts', 'festival-musique-arabe', 'Retour sur les moments marquants du Festival de musique arabe de cette année.', '<p>Le Festival de musique arabe a une fois de plus tenu toutes ses promesses cette année. Radio Mehna était présente pour couvrir l\'événement.</p><p>Les artistes ont offert des prestations exceptionnelles, mêlant tradition et modernité. Notre équipe vous propose un retour en images et en sons sur les meilleurs moments.</p>', 1, 'published', 180, '2024-12-08 14:00:00'),
('Interview exclusive : artiste tunisien émergent', 'interview-artiste-tunisien', 'Rencontre avec un jeune talent de la scène musicale tunisienne qui fait parler de lui.', '<p>Cette semaine, Radio Mehna a eu le plaisir de recevoir un jeune artiste tunisien qui fait sensation sur la scène musicale nationale.</p><p>Dans cette interview exclusive, il nous parle de son parcours, de ses influences et de ses projets pour l\'avenir. Une conversation inspirante à ne pas manquer !</p>', 1, 'published', 95, '2024-12-12 16:00:00');
