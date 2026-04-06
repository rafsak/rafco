-- ============================================
-- ATFP Chatbot - Schema & Données de test
-- Compatible MySQL 5.7+ / MariaDB 10.3+
-- ============================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE DATABASE IF NOT EXISTS atfp_chatbot
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE atfp_chatbot;

-- ------------------------------------------
-- Table : gouvernorats
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS gouvernorats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_fr VARCHAR(100) NOT NULL,
    nom_ar VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : centres ATFP
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS centres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_fr VARCHAR(200) NOT NULL,
    nom_ar VARCHAR(200) NOT NULL,
    adresse_fr VARCHAR(300) DEFAULT NULL,
    adresse_ar VARCHAR(300) DEFAULT NULL,
    telephone VARCHAR(30) DEFAULT NULL,
    gouvernorat_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gouvernorat_id) REFERENCES gouvernorats(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : secteurs
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS secteurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_fr VARCHAR(150) NOT NULL,
    nom_ar VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : spécialités
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS specialites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_fr VARCHAR(200) NOT NULL,
    nom_ar VARCHAR(200) NOT NULL,
    secteur_id INT NOT NULL,
    niveau VARCHAR(10) NOT NULL COMMENT 'CAP, BTP, BTS',
    duree_mois INT NOT NULL DEFAULT 24,
    description_fr TEXT DEFAULT NULL,
    description_ar TEXT DEFAULT NULL,
    conditions_fr TEXT DEFAULT NULL,
    conditions_ar TEXT DEFAULT NULL,
    debouches_fr TEXT DEFAULT NULL,
    debouches_ar TEXT DEFAULT NULL,
    FOREIGN KEY (secteur_id) REFERENCES secteurs(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : centre_specialite (pivot)
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS centre_specialite (
    centre_id INT NOT NULL,
    specialite_id INT NOT NULL,
    PRIMARY KEY (centre_id, specialite_id),
    FOREIGN KEY (centre_id) REFERENCES centres(id),
    FOREIGN KEY (specialite_id) REFERENCES specialites(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : conversations
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lang ENUM('fr','ar') DEFAULT 'fr',
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : messages
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    role ENUM('user','bot') NOT NULL,
    content TEXT NOT NULL,
    intent VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : profils utilisateurs (scoring)
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS profils (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    niveau_scolaire VARCHAR(50) DEFAULT NULL,
    interets JSON DEFAULT NULL,
    scores JSON DEFAULT NULL,
    recommandations JSON DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table : admin users
-- ------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES DE TEST
-- ============================================

-- Gouvernorats
INSERT INTO gouvernorats (nom_fr, nom_ar) VALUES
('Tunis', 'تونس'),
('Ariana', 'أريانة'),
('Ben Arous', 'بن عروس'),
('Sousse', 'سوسة'),
('Sfax', 'صفاقس'),
('Nabeul', 'نابل'),
('Bizerte', 'بنزرت'),
('Gabès', 'قابس'),
('Kairouan', 'القيروان'),
('Monastir', 'المنستير'),
('Médenine', 'مدنين'),
('Gafsa', 'قفصة'),
('Kasserine', 'القصرين'),
('Sidi Bouzid', 'سيدي بوزيد'),
('Jendouba', 'جندوبة'),
('Béja', 'باجة'),
('Le Kef', 'الكاف'),
('Siliana', 'سليانة'),
('Mahdia', 'المهدية'),
('Tozeur', 'توزر'),
('Kébili', 'قبلي'),
('Tataouine', 'تطاوين'),
('Manouba', 'منوبة'),
('Zaghouan', 'زغوان');

-- Secteurs
INSERT INTO secteurs (nom_fr, nom_ar) VALUES
('Informatique & Multimédia', 'الإعلامية والوسائط المتعددة'),
('Électricité & Électronique', 'الكهرباء والإلكترونيك'),
('Mécanique & Métallurgie', 'الميكانيك وتشكيل المعادن'),
('Bâtiment & Travaux Publics', 'البناء والأشغال العمومية'),
('Textile & Habillement', 'النسيج والملابس'),
('Tourisme & Hôtellerie', 'السياحة والفندقة'),
('Agriculture & Pêche', 'الفلاحة والصيد البحري'),
('Artisanat', 'الحرف اليدوية'),
('Coiffure & Esthétique', 'الحلاقة والتجميل'),
('Santé', 'الصحة'),
('Commerce & Gestion', 'التجارة والتصرف'),
('Industries Agroalimentaires', 'الصناعات الغذائية');

-- Centres ATFP
INSERT INTO centres (nom_fr, nom_ar, adresse_fr, telephone, gouvernorat_id) VALUES
('Centre Sectoriel de Formation en Informatique de Tunis', 'المركز القطاعي للتكوين في الإعلامية بتونس', 'Rue de Grèce, Tunis', '71 240 000', 1),
('Centre Sectoriel de Formation en Électronique de Ben Arous', 'المركز القطاعي للتكوين في الإلكترونيك ببن عروس', 'Zone Industrielle, Ben Arous', '71 380 000', 3),
('Centre de Formation et d''Apprentissage de Sousse', 'مركز التكوين والتدريب بسوسة', 'Avenue de la République, Sousse', '73 220 000', 4),
('Centre Sectoriel de Formation en Mécanique de Sfax', 'المركز القطاعي للتكوين في الميكانيك بصفاقس', 'Route de Tunis Km 5, Sfax', '74 400 000', 5),
('Centre de Formation et d''Apprentissage de Nabeul', 'مركز التكوين والتدريب بنابل', 'Avenue Habib Bourguiba, Nabeul', '72 280 000', 6),
('Centre Sectoriel de Formation en Tourisme de Hammamet', 'المركز القطاعي للتكوين في السياحة بالحمامات', 'Zone Touristique, Hammamet', '72 260 000', 6),
('Centre de Formation et d''Apprentissage de Bizerte', 'مركز التكوين والتدريب ببنزرت', 'Avenue Habib Bourguiba, Bizerte', '72 430 000', 7),
('Centre de Formation et d''Apprentissage de Gabès', 'مركز التكوين والتدريب بقابس', 'Avenue Farhat Hached, Gabès', '75 270 000', 8),
('Centre de Formation et d''Apprentissage de Kairouan', 'مركز التكوين والتدريب بالقيروان', 'Avenue de la République, Kairouan', '77 230 000', 9),
('Centre Sectoriel de Formation en BTP de l''Ariana', 'المركز القطاعي للتكوين في البناء بأريانة', 'Cité Ennasr, Ariana', '71 710 000', 2),
('Centre de Formation et d''Apprentissage de Gafsa', 'مركز التكوين والتدريب بقفصة', 'Avenue Habib Bourguiba, Gafsa', '76 220 000', 12),
('Centre de Formation et d''Apprentissage de Monastir', 'مركز التكوين والتدريب بالمنستير', 'Avenue de l''Environnement, Monastir', '73 460 000', 10);

-- Spécialités
INSERT INTO specialites (nom_fr, nom_ar, secteur_id, niveau, duree_mois, description_fr, description_ar, conditions_fr, debouches_fr) VALUES
-- Informatique
('Technicien en Développement Web', 'تقني في تطوير الويب', 1, 'BTP', 24, 'Formation complète en développement de sites et applications web (HTML, CSS, JavaScript, PHP, MySQL).', 'تكوين شامل في تطوير المواقع والتطبيقات (HTML، CSS، JavaScript، PHP، MySQL).', 'Niveau 9ème année de base + test d''admission', 'Développeur web, Intégrateur web, Webmaster'),
('Technicien en Réseaux Informatiques', 'تقني في الشبكات المعلوماتية', 1, 'BTP', 24, 'Installation, configuration et maintenance des réseaux informatiques.', 'تركيب وإعداد وصيانة الشبكات المعلوماتية.', 'Niveau 9ème année de base + test d''admission', 'Administrateur réseau, Technicien support'),
('Technicien Supérieur en Développement Informatique', 'تقني سامي في التطوير المعلوماتي', 1, 'BTS', 30, 'Conception et développement d''applications informatiques avancées.', 'تصميم وتطوير التطبيقات المعلوماتية المتقدمة.', 'Baccalauréat + test d''admission', 'Développeur logiciel, Analyste programmeur'),
('Infographiste', 'مصمم جرافيكي', 1, 'BTP', 24, 'Création graphique, PAO, retouche d''images et design multimédia.', 'التصميم الجرافيكي والنشر المكتبي ومعالجة الصور.', 'Niveau 9ème année de base + test d''admission', 'Infographiste, Designer graphique, Maquettiste'),

-- Électricité
('Électricien Bâtiment', 'كهربائي بناء', 2, 'CAP', 18, 'Installation et maintenance des systèmes électriques dans les bâtiments.', 'تركيب وصيانة الأنظمة الكهربائية في المباني.', 'Niveau 7ème année + test d''admission', 'Électricien, Technicien de maintenance'),
('Technicien en Électronique Industrielle', 'تقني في الإلكترونيك الصناعية', 2, 'BTP', 24, 'Maintenance et réparation des équipements électroniques industriels.', 'صيانة وإصلاح المعدات الإلكترونية الصناعية.', 'Niveau 9ème année de base + test d''admission', 'Technicien électronique, Agent de maintenance'),
('Technicien en Froid et Climatisation', 'تقني في التبريد والتكييف', 2, 'BTP', 24, 'Installation et maintenance des systèmes de froid et climatisation.', 'تركيب وصيانة أنظمة التبريد والتكييف.', 'Niveau 9ème année de base + test d''admission', 'Frigoriste, Climaticien'),

-- Mécanique
('Mécanicien Automobile', 'ميكانيكي سيارات', 3, 'CAP', 18, 'Réparation et entretien des véhicules automobiles.', 'إصلاح وصيانة السيارات.', 'Niveau 7ème année + test d''admission', 'Mécanicien auto, Chef d''atelier'),
('Technicien en Soudure', 'تقني في اللحام', 3, 'BTP', 24, 'Techniques de soudure avancées pour l''industrie.', 'تقنيات اللحام المتقدمة للصناعة.', 'Niveau 9ème année de base + test d''admission', 'Soudeur qualifié, Chaudronnier'),
('Tourneur Fraiseur', 'خراط', 3, 'CAP', 18, 'Usinage de pièces mécaniques sur tours et fraiseuses.', 'تشغيل القطع الميكانيكية على المخارط وآلات التفريز.', 'Niveau 7ème année + test d''admission', 'Tourneur, Fraiseur, Usineur'),

-- BTP
('Maçon', 'بنّاء', 4, 'CAP', 12, 'Construction et rénovation de bâtiments.', 'بناء وتجديد المباني.', 'Niveau 6ème année', 'Maçon, Chef de chantier'),
('Plombier Sanitaire', 'سبّاك صحي', 4, 'CAP', 18, 'Installation et maintenance des réseaux sanitaires.', 'تركيب وصيانة الشبكات الصحية.', 'Niveau 7ème année + test d''admission', 'Plombier, Installateur sanitaire'),
('Technicien en Dessin Bâtiment', 'تقني في رسم البناء', 4, 'BTP', 24, 'Réalisation de plans et dessins techniques pour le bâtiment.', 'إنجاز المخططات والرسوم التقنية للبناء.', 'Niveau 9ème année de base + test d''admission', 'Dessinateur, Métreur'),

-- Tourisme & Hôtellerie
('Agent de Réception Hôtelière', 'عون استقبال فندقي', 6, 'BTP', 24, 'Accueil et gestion de la clientèle hôtelière.', 'استقبال وإدارة زبائن الفندق.', 'Niveau 9ème année + test d''admission + entretien', 'Réceptionniste, Agent d''accueil'),
('Cuisinier', 'طبّاخ', 6, 'CAP', 18, 'Arts culinaires et techniques de cuisine professionnelle.', 'فنون الطبخ وتقنيات المطبخ المهني.', 'Niveau 7ème année + test d''admission', 'Cuisinier, Chef de partie'),
('Pâtissier', 'حلواني', 6, 'CAP', 18, 'Fabrication de pâtisseries, viennoiseries et confiseries.', 'صناعة الحلويات والمعجنات.', 'Niveau 7ème année + test d''admission', 'Pâtissier, Chef pâtissier'),

-- Coiffure & Esthétique
('Coiffeur', 'حلاق', 9, 'CAP', 18, 'Techniques de coiffure masculine et féminine.', 'تقنيات الحلاقة للرجال والنساء.', 'Niveau 7ème année', 'Coiffeur, Gérant de salon'),
('Esthéticienne', 'أخصائية تجميل', 9, 'BTP', 24, 'Soins esthétiques du visage et du corps.', 'العناية التجميلية بالوجه والجسم.', 'Niveau 9ème année + test d''admission', 'Esthéticienne, Conseillère beauté'),

-- Commerce
('Agent Commercial', 'عون تجاري', 11, 'BTP', 24, 'Techniques de vente, marketing et gestion commerciale.', 'تقنيات البيع والتسويق والتصرف التجاري.', 'Niveau 9ème année de base + test d''admission', 'Commercial, Vendeur conseil'),

-- Agroalimentaire
('Technicien en Industries Agroalimentaires', 'تقني في الصناعات الغذائية', 12, 'BTP', 24, 'Transformation et conservation des produits alimentaires.', 'تحويل وحفظ المنتجات الغذائية.', 'Niveau 9ème année de base + test d''admission', 'Technicien agroalimentaire, Agent de qualité');

-- Associations centre <-> spécialité
INSERT INTO centre_specialite (centre_id, specialite_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4),
(2, 5), (2, 6), (2, 7),
(3, 1), (3, 5), (3, 8), (3, 19),
(4, 8), (4, 9), (4, 10),
(5, 5), (5, 11), (5, 12), (5, 17),
(6, 14), (6, 15), (6, 16),
(7, 1), (7, 5), (7, 8), (7, 11),
(8, 5), (8, 8), (8, 11), (8, 12),
(9, 5), (9, 8), (9, 17),
(10, 11), (10, 12), (10, 13),
(11, 5), (11, 8), (11, 10),
(12, 5), (12, 17), (12, 18);

-- Admin par défaut (mot de passe : admin123)
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
