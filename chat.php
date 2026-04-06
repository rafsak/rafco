<?php
/**
 * ATFP Chatbot - API Chat
 * Point d'entrée unique pour le chatbot
 * Méthode : POST
 * Body JSON : { "message": "...", "session_id": "...", "lang": "fr"|"ar" }
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée'], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/config.php';

// -- Lire le body JSON --
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['message'])) {
    jsonResponse(['error' => 'Message requis'], 400);
}

$userMessage = trim($input['message']);
$sessionId   = $input['session_id'] ?? bin2hex(random_bytes(16));
$lang        = in_array($input['lang'] ?? '', ['fr', 'ar']) ? $input['lang'] : DEFAULT_LANG;

try {
    $db = getDB();

    // -- Récupérer ou créer la conversation --
    $conversationId = getOrCreateConversation($db, $sessionId, $lang);

    // -- Sauvegarder le message utilisateur --
    saveMessage($db, $conversationId, 'user', $userMessage);

    // -- Analyser le message et générer la réponse --
    $result = processMessage($db, $userMessage, $lang, $conversationId);

    // -- Sauvegarder la réponse bot --
    saveMessage($db, $conversationId, 'bot', $result['reply'], $result['intent'] ?? null);

    jsonResponse([
        'reply'      => $result['reply'],
        'intent'     => $result['intent'] ?? null,
        'data'       => $result['data'] ?? null,
        'session_id' => $sessionId,
        'lang'       => $lang,
    ]);

} catch (Exception $ex) {
    error_log('ATFP Chatbot Error: ' . $ex->getMessage());
    jsonResponse(['error' => 'Erreur interne du serveur'], 500);
}

// =============================================
// FONCTIONS
// =============================================

/**
 * Récupérer ou créer une conversation
 */
function getOrCreateConversation(PDO $db, string $sessionId, string $lang): int
{
    $stmt = $db->prepare('SELECT id FROM conversations WHERE session_id = :sid ORDER BY id DESC LIMIT 1');
    $stmt->execute([':sid' => $sessionId]);
    $row = $stmt->fetch();

    if ($row) {
        return (int)$row['id'];
    }

    $stmt = $db->prepare('INSERT INTO conversations (session_id, lang) VALUES (:sid, :lang)');
    $stmt->execute([':sid' => $sessionId, ':lang' => $lang]);
    return (int)$db->lastInsertId();
}

/**
 * Sauvegarder un message
 */
function saveMessage(PDO $db, int $conversationId, string $role, string $content, ?string $intent = null): void
{
    $stmt = $db->prepare(
        'INSERT INTO messages (conversation_id, role, content, intent) VALUES (:cid, :role, :content, :intent)'
    );
    $stmt->execute([
        ':cid'     => $conversationId,
        ':role'    => $role,
        ':content' => $content,
        ':intent'  => $intent,
    ]);
}

/**
 * Traiter le message et générer une réponse
 */
function processMessage(PDO $db, string $message, string $lang, int $conversationId): array
{
    $intents = loadIntents();
    $messageLower = mb_strtolower($message, 'UTF-8');

    // -- Vérifier le contexte de conversation (orientation en cours) --
    $context = getConversationContext($db, $conversationId);
    if ($context) {
        return handleOrientationFlow($db, $message, $lang, $conversationId, $context);
    }

    // -- Scoring par intent --
    $bestIntent = null;
    $bestScore  = 0;

    foreach ($intents['intents'] ?? [] as $intent) {
        if ($intent['tag'] === 'default') {
            continue;
        }

        $patterns = $intent['patterns'][$lang] ?? [];
        $score = calculateScore($messageLower, $patterns);

        if ($score > $bestScore) {
            $bestScore  = $score;
            $bestIntent = $intent;
        }
    }

    // -- Seuil minimum de confiance --
    if ($bestScore < 0.3 || !$bestIntent) {
        return getDefaultResponse($intents, $lang);
    }

    // -- Exécuter l'action associée --
    $reply = getRandomResponse($bestIntent['responses'][$lang] ?? []);
    $data  = null;

    if (!empty($bestIntent['action'])) {
        $actionResult = executeAction($db, $bestIntent, $message, $lang, $conversationId);
        if (!empty($actionResult['extra_reply'])) {
            $reply .= "\n\n" . $actionResult['extra_reply'];
        }
        $data = $actionResult['data'] ?? null;
    }

    return [
        'reply'  => $reply,
        'intent' => $bestIntent['tag'],
        'data'   => $data,
    ];
}

/**
 * Calculer le score de correspondance
 */
function calculateScore(string $message, array $patterns): float
{
    if (empty($patterns)) {
        return 0;
    }

    $maxScore = 0;
    $words = preg_split('/\s+/', $message);

    foreach ($patterns as $pattern) {
        $patternLower = mb_strtolower($pattern, 'UTF-8');
        $score = 0;

        // Correspondance exacte
        if ($message === $patternLower) {
            return 1.0;
        }

        // Le message contient le pattern
        if (mb_strpos($message, $patternLower) !== false) {
            $score = 0.8;
            // Bonus si le pattern est long (plus spécifique)
            $score += min(0.15, mb_strlen($patternLower) * 0.01);
        }

        // Correspondance par mots
        $patternWords = preg_split('/\s+/', $patternLower);
        $matchedWords = 0;
        foreach ($patternWords as $pw) {
            foreach ($words as $w) {
                if ($w === $pw || mb_strpos($w, $pw) !== false || mb_strpos($pw, $w) !== false) {
                    $matchedWords++;
                    break;
                }
            }
        }

        if (count($patternWords) > 0) {
            $wordScore = ($matchedWords / count($patternWords)) * 0.7;
            $score = max($score, $wordScore);
        }

        $maxScore = max($maxScore, $score);
    }

    return $maxScore;
}

/**
 * Exécuter une action
 */
function executeAction(PDO $db, array $intent, string $message, string $lang, int $conversationId): array
{
    $action = $intent['action'];

    switch ($action) {
        case 'list_secteurs':
            return actionListSecteurs($db, $lang);

        case 'list_centres':
            return actionListCentres($db, $lang);

        case 'filter_centres_by_gov':
            return actionFilterCentresByGov($db, $message, $lang);

        case 'suggest_by_sector':
            $sectorId = $intent['sector_id'] ?? null;
            return actionSuggestBySector($db, $sectorId, $lang);

        case 'start_orientation':
            return actionStartOrientation($db, $conversationId, $lang);

        case 'ask_niveau':
            return actionStartOrientation($db, $conversationId, $lang);

        default:
            return ['data' => null];
    }
}

/**
 * Lister les secteurs
 */
function actionListSecteurs(PDO $db, string $lang): array
{
    $col = $lang === 'ar' ? 'nom_ar' : 'nom_fr';
    $stmt = $db->query("SELECT id, {$col} AS nom FROM secteurs ORDER BY id");
    $secteurs = $stmt->fetchAll();

    $lines = [];
    foreach ($secteurs as $s) {
        $lines[] = "• " . $s['nom'];
    }

    return [
        'extra_reply' => implode("\n", $lines),
        'data' => ['type' => 'secteurs', 'items' => $secteurs],
    ];
}

/**
 * Lister les centres
 */
function actionListCentres(PDO $db, string $lang): array
{
    $nomC = $lang === 'ar' ? 'c.nom_ar' : 'c.nom_fr';
    $nomG = $lang === 'ar' ? 'g.nom_ar' : 'g.nom_fr';

    $stmt = $db->query("
        SELECT c.id, {$nomC} AS nom, c.telephone, {$nomG} AS gouvernorat
        FROM centres c
        JOIN gouvernorats g ON c.gouvernorat_id = g.id
        ORDER BY g.id, c.id
    ");
    $centres = $stmt->fetchAll();

    $lines = [];
    foreach ($centres as $c) {
        $tel = $c['telephone'] ? " 📞 " . $c['telephone'] : '';
        $lines[] = "🏢 " . $c['nom'] . " (" . $c['gouvernorat'] . ")" . $tel;
    }

    return [
        'extra_reply' => implode("\n", $lines),
        'data' => ['type' => 'centres', 'items' => $centres],
    ];
}

/**
 * Filtrer les centres par gouvernorat
 */
function actionFilterCentresByGov(PDO $db, string $message, string $lang): array
{
    $messageLower = mb_strtolower($message, 'UTF-8');

    // Chercher le gouvernorat mentionné
    $stmt = $db->query("SELECT id, nom_fr, nom_ar FROM gouvernorats");
    $gouvs = $stmt->fetchAll();

    $govId = null;
    foreach ($gouvs as $g) {
        if (mb_strpos($messageLower, mb_strtolower($g['nom_fr'])) !== false ||
            mb_strpos($message, $g['nom_ar']) !== false) {
            $govId = $g['id'];
            break;
        }
    }

    if (!$govId) {
        return ['data' => null];
    }

    $nomC = $lang === 'ar' ? 'c.nom_ar' : 'c.nom_fr';
    $stmt = $db->prepare("
        SELECT c.id, {$nomC} AS nom, c.telephone, c.adresse_fr AS adresse
        FROM centres c
        WHERE c.gouvernorat_id = :gid
        ORDER BY c.id
    ");
    $stmt->execute([':gid' => $govId]);
    $centres = $stmt->fetchAll();

    if (empty($centres)) {
        $noResult = $lang === 'ar'
            ? 'لا توجد مراكز مسجلة حاليا في هذه الولاية في قاعدة البيانات.'
            : 'Aucun centre trouvé dans ce gouvernorat pour le moment.';
        return ['extra_reply' => $noResult, 'data' => null];
    }

    $lines = [];
    foreach ($centres as $c) {
        $addr = $c['adresse'] ? " - " . $c['adresse'] : '';
        $tel  = $c['telephone'] ? " 📞 " . $c['telephone'] : '';
        $lines[] = "🏢 " . $c['nom'] . $addr . $tel;
    }

    return [
        'extra_reply' => implode("\n", $lines),
        'data' => ['type' => 'centres', 'items' => $centres],
    ];
}

/**
 * Suggérer des spécialités par secteur
 */
function actionSuggestBySector(PDO $db, ?int $sectorId, string $lang): array
{
    if (!$sectorId) {
        return ['data' => null];
    }

    $nom  = $lang === 'ar' ? 'nom_ar' : 'nom_fr';
    $desc = $lang === 'ar' ? 'description_ar' : 'description_fr';
    $deb  = $lang === 'ar' ? 'debouches_fr' : 'debouches_fr'; // debouches only in FR for now

    $stmt = $db->prepare("
        SELECT id, {$nom} AS nom, niveau, duree_mois, {$desc} AS description, debouches_fr AS debouches
        FROM specialites
        WHERE secteur_id = :sid
        ORDER BY niveau, nom_fr
    ");
    $stmt->execute([':sid' => $sectorId]);
    $specs = $stmt->fetchAll();

    if (empty($specs)) {
        return ['data' => null];
    }

    $lines = [];
    foreach ($specs as $s) {
        $duree = $s['duree_mois'] . ($lang === 'ar' ? ' شهر' : ' mois');
        $lines[] = "🎓 **" . $s['nom'] . "** (" . $s['niveau'] . " - " . $duree . ")";
        if ($s['description']) {
            $lines[] = "   " . $s['description'];
        }
        if ($s['debouches']) {
            $debLabel = $lang === 'ar' ? '   آفاق: ' : '   Débouchés : ';
            $lines[] = $debLabel . $s['debouches'];
        }
        $lines[] = "";
    }

    return [
        'extra_reply' => implode("\n", $lines),
        'data' => ['type' => 'specialites', 'items' => $specs],
    ];
}

/**
 * Démarrer le parcours d'orientation
 */
function actionStartOrientation(PDO $db, int $conversationId, string $lang): array
{
    // Créer ou mettre à jour le profil
    $stmt = $db->prepare('SELECT id FROM profils WHERE conversation_id = :cid');
    $stmt->execute([':cid' => $conversationId]);

    if (!$stmt->fetch()) {
        $stmt = $db->prepare(
            'INSERT INTO profils (conversation_id, scores) VALUES (:cid, :scores)'
        );
        $stmt->execute([
            ':cid'    => $conversationId,
            ':scores' => json_encode(['step' => 'niveau']),
        ]);
    } else {
        $stmt = $db->prepare(
            'UPDATE profils SET scores = :scores WHERE conversation_id = :cid'
        );
        $stmt->execute([
            ':cid'    => $conversationId,
            ':scores' => json_encode(['step' => 'niveau']),
        ]);
    }

    return ['data' => ['type' => 'orientation', 'step' => 'niveau']];
}

/**
 * Obtenir le contexte de conversation (orientation en cours)
 */
function getConversationContext(PDO $db, int $conversationId): ?array
{
    $stmt = $db->prepare('SELECT * FROM profils WHERE conversation_id = :cid');
    $stmt->execute([':cid' => $conversationId]);
    $profil = $stmt->fetch();

    if (!$profil) {
        return null;
    }

    $scores = json_decode($profil['scores'] ?? '{}', true);
    if (empty($scores['step'])) {
        return null;
    }

    return [
        'profil' => $profil,
        'scores' => $scores,
    ];
}

/**
 * Gérer le flux d'orientation
 */
function handleOrientationFlow(PDO $db, string $message, string $lang, int $conversationId, array $context): array
{
    $scores = $context['scores'];
    $step   = $scores['step'];

    switch ($step) {
        case 'niveau':
            return handleNiveauStep($db, $message, $lang, $conversationId, $scores);

        case 'interets':
            return handleInteretsStep($db, $message, $lang, $conversationId, $scores);

        case 'gouvernorat':
            return handleGouvernoratStep($db, $message, $lang, $conversationId, $scores);

        default:
            // Réinitialiser
            $stmt = $db->prepare('DELETE FROM profils WHERE conversation_id = :cid');
            $stmt->execute([':cid' => $conversationId]);
            return processMessageWithoutContext($db, $message, $lang, $conversationId);
    }
}

/**
 * Traiter sans contexte (fallback)
 */
function processMessageWithoutContext(PDO $db, string $message, string $lang, int $conversationId): array
{
    // Supprimer le profil pour éviter la boucle
    $stmt = $db->prepare('DELETE FROM profils WHERE conversation_id = :cid');
    $stmt->execute([':cid' => $conversationId]);

    $intents = loadIntents();
    $messageLower = mb_strtolower($message, 'UTF-8');

    $bestIntent = null;
    $bestScore  = 0;

    foreach ($intents['intents'] ?? [] as $intent) {
        if ($intent['tag'] === 'default') continue;
        $patterns = $intent['patterns'][$lang] ?? [];
        $score = calculateScore($messageLower, $patterns);
        if ($score > $bestScore) {
            $bestScore  = $score;
            $bestIntent = $intent;
        }
    }

    if ($bestScore < 0.3 || !$bestIntent) {
        return getDefaultResponse($intents, $lang);
    }

    $reply = getRandomResponse($bestIntent['responses'][$lang] ?? []);
    return ['reply' => $reply, 'intent' => $bestIntent['tag']];
}

/**
 * Étape niveau scolaire
 */
function handleNiveauStep(PDO $db, string $message, string $lang, int $conversationId, array $scores): array
{
    $messageLower = mb_strtolower($message, 'UTF-8');

    // Détecter le niveau
    $niveau = null;
    $niveauAccessible = [];

    if (preg_match('/bac|باكالوريا|baccalauréat/ui', $messageLower)) {
        $niveau = 'Baccalauréat';
        $niveauAccessible = ['CAP', 'BTP', 'BTS'];
    } elseif (preg_match('/9[èe]me|neuvième|تاسعة|9ème/ui', $messageLower)) {
        $niveau = '9ème année';
        $niveauAccessible = ['CAP', 'BTP'];
    } elseif (preg_match('/7[èe]me|septième|سابعة|7ème/ui', $messageLower)) {
        $niveau = '7ème année';
        $niveauAccessible = ['CAP'];
    } elseif (preg_match('/6[èe]me|sixième|سادسة|6ème/ui', $messageLower)) {
        $niveau = '6ème année';
        $niveauAccessible = ['CAP'];
    }

    if (!$niveau) {
        $retry = $lang === 'ar'
            ? "لم أتعرف على مستواك. الرجاء تحديد: 6، 7، 9 أساسي أو باكالوريا."
            : "Je n'ai pas reconnu votre niveau. Précisez : 6ème, 7ème, 9ème année ou Baccalauréat.";
        return ['reply' => $retry, 'intent' => 'orientation'];
    }

    // Mettre à jour le profil
    $scores['niveau'] = $niveau;
    $scores['niveaux_accessibles'] = $niveauAccessible;
    $scores['step'] = 'interets';

    $stmt = $db->prepare('UPDATE profils SET niveau_scolaire = :niv, scores = :scores WHERE conversation_id = :cid');
    $stmt->execute([
        ':niv'    => $niveau,
        ':scores' => json_encode($scores),
        ':cid'    => $conversationId,
    ]);

    $niveauxStr = implode(', ', $niveauAccessible);

    if ($lang === 'ar') {
        $reply = "✅ مستوى {$niveau} - يمكنك الوصول إلى شهادات: {$niveauxStr}\n\n";
        $reply .= "🎯 ما هي اهتماماتك؟ اختر مجالا أو أكثر:\n";
        $reply .= "💻 إعلامية | ⚡ كهرباء | 🔧 ميكانيك | 🏗️ بناء\n";
        $reply .= "🏨 سياحة وفندقة | ✂️ حلاقة وتجميل | 📊 تجارة";
    } else {
        $reply = "✅ Niveau {$niveau} — vous pouvez accéder aux diplômes : {$niveauxStr}\n\n";
        $reply .= "🎯 Quels sont vos centres d'intérêt ? Choisissez un ou plusieurs domaines :\n";
        $reply .= "💻 Informatique | ⚡ Électricité | 🔧 Mécanique | 🏗️ BTP\n";
        $reply .= "🏨 Tourisme & Hôtellerie | ✂️ Coiffure & Esthétique | 📊 Commerce";
    }

    return ['reply' => $reply, 'intent' => 'orientation', 'data' => ['type' => 'orientation', 'step' => 'interets']];
}

/**
 * Étape centres d'intérêt
 */
function handleInteretsStep(PDO $db, string $message, string $lang, int $conversationId, array $scores): array
{
    $messageLower = mb_strtolower($message, 'UTF-8');
    $niveaux = $scores['niveaux_accessibles'] ?? ['CAP', 'BTP', 'BTS'];

    // Mapping mots-clés -> secteur_id
    $sectorKeywords = [
        1  => ['informatique', 'ordinateur', 'web', 'code', 'إعلامية', 'حاسوب', 'ويب'],
        2  => ['électricité', 'électronique', 'froid', 'clim', 'كهرباء', 'إلكترونيك', 'تبريد'],
        3  => ['mécanique', 'voiture', 'soudure', 'ميكانيك', 'سيارة', 'لحام'],
        4  => ['bâtiment', 'btp', 'construction', 'maçon', 'plombier', 'بناء', 'سبّاك'],
        6  => ['tourisme', 'hôtel', 'cuisine', 'pâtisserie', 'سياحة', 'فندق', 'طبخ'],
        9  => ['coiffure', 'esthétique', 'beauté', 'حلاقة', 'تجميل', 'جمال'],
        11 => ['commerce', 'vente', 'marketing', 'gestion', 'تجارة', 'بيع', 'تسويق'],
    ];

    $matchedSectors = [];
    foreach ($sectorKeywords as $sId => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($messageLower, $kw) !== false) {
                $matchedSectors[] = $sId;
                break;
            }
        }
    }

    if (empty($matchedSectors)) {
        $retry = $lang === 'ar'
            ? "لم أتعرف على اهتماماتك. اختر من: 💻 إعلامية | ⚡ كهرباء | 🔧 ميكانيك | 🏗️ بناء | 🏨 سياحة | ✂️ تجميل | 📊 تجارة"
            : "Je n'ai pas identifié vos intérêts. Choisissez parmi : 💻 Informatique | ⚡ Électricité | 🔧 Mécanique | 🏗️ BTP | 🏨 Tourisme | ✂️ Esthétique | 📊 Commerce";
        return ['reply' => $retry, 'intent' => 'orientation'];
    }

    // Chercher les spécialités correspondantes
    $placeholders = implode(',', array_fill(0, count($matchedSectors), '?'));
    $niveauPlaceholders = implode(',', array_fill(0, count($niveaux), '?'));

    $nom  = $lang === 'ar' ? 's.nom_ar' : 's.nom_fr';
    $desc = $lang === 'ar' ? 's.description_ar' : 's.description_fr';
    $secNom = $lang === 'ar' ? 'sec.nom_ar' : 'sec.nom_fr';

    $sql = "
        SELECT s.id, {$nom} AS nom, s.niveau, s.duree_mois, {$desc} AS description,
               s.debouches_fr AS debouches, {$secNom} AS secteur
        FROM specialites s
        JOIN secteurs sec ON s.secteur_id = sec.id
        WHERE s.secteur_id IN ({$placeholders})
        AND s.niveau IN ({$niveauPlaceholders})
        ORDER BY s.secteur_id, s.niveau, s.nom_fr
    ";

    $stmt = $db->prepare($sql);
    $params = array_merge($matchedSectors, $niveaux);
    $stmt->execute($params);
    $specs = $stmt->fetchAll();

    // Mettre à jour profil
    $scores['interets'] = $matchedSectors;
    $scores['step'] = 'gouvernorat';

    $stmt2 = $db->prepare('UPDATE profils SET interets = :int, scores = :scores, recommandations = :rec WHERE conversation_id = :cid');
    $stmt2->execute([
        ':int'    => json_encode($matchedSectors),
        ':scores' => json_encode($scores),
        ':rec'    => json_encode(array_column($specs, 'id')),
        ':cid'    => $conversationId,
    ]);

    if (empty($specs)) {
        $noResult = $lang === 'ar'
            ? "لا توجد تكوينات متاحة تتناسب مع مستواك واهتماماتك حاليا."
            : "Aucune formation ne correspond à votre niveau et vos intérêts pour le moment.";

        // Reset orientation
        $scores['step'] = null;
        $stmt3 = $db->prepare('UPDATE profils SET scores = :scores WHERE conversation_id = :cid');
        $stmt3->execute([':scores' => json_encode($scores), ':cid' => $conversationId]);

        return ['reply' => $noResult, 'intent' => 'orientation'];
    }

    // Construire la réponse
    if ($lang === 'ar') {
        $reply = "🎓 بناء على مستواك واهتماماتك، إليك التكوينات المقترحة:\n\n";
    } else {
        $reply = "🎓 Voici les formations recommandées selon votre profil :\n\n";
    }

    foreach ($specs as $s) {
        $duree = $s['duree_mois'] . ($lang === 'ar' ? ' شهر' : ' mois');
        $reply .= "📌 **" . $s['nom'] . "** (" . $s['niveau'] . " - " . $duree . ")\n";
        $reply .= "   📂 " . $s['secteur'] . "\n";
        if ($s['description']) {
            $reply .= "   " . $s['description'] . "\n";
        }
        if ($s['debouches']) {
            $label = $lang === 'ar' ? '   🚀 آفاق: ' : '   🚀 Débouchés : ';
            $reply .= $label . $s['debouches'] . "\n";
        }
        $reply .= "\n";
    }

    if ($lang === 'ar') {
        $reply .= "📍 في أي ولاية أنت؟ سأبحث لك عن أقرب مركز تكوين.";
    } else {
        $reply .= "📍 Dans quel gouvernorat êtes-vous ? Je chercherai le centre le plus proche pour vous.";
    }

    return [
        'reply'  => $reply,
        'intent' => 'orientation',
        'data'   => ['type' => 'recommandations', 'items' => $specs],
    ];
}

/**
 * Étape gouvernorat
 */
function handleGouvernoratStep(PDO $db, string $message, string $lang, int $conversationId, array $scores): array
{
    $messageLower = mb_strtolower($message, 'UTF-8');

    // Chercher le gouvernorat
    $stmt = $db->query("SELECT id, nom_fr, nom_ar FROM gouvernorats");
    $gouvs = $stmt->fetchAll();

    $govId   = null;
    $govNom  = '';
    foreach ($gouvs as $g) {
        if (mb_strpos($messageLower, mb_strtolower($g['nom_fr'])) !== false ||
            mb_strpos($message, $g['nom_ar']) !== false) {
            $govId  = $g['id'];
            $govNom = $lang === 'ar' ? $g['nom_ar'] : $g['nom_fr'];
            break;
        }
    }

    if (!$govId) {
        $retry = $lang === 'ar'
            ? "لم أتعرف على الولاية. أعد كتابة اسم ولايتك (مثال: تونس، سوسة، صفاقس...)"
            : "Je n'ai pas reconnu le gouvernorat. Réessayez (ex : Tunis, Sousse, Sfax...)";
        return ['reply' => $retry, 'intent' => 'orientation'];
    }

    // Chercher les centres avec les spécialités recommandées
    $recIds = json_decode($scores['recommandations'] ?? '[]', true);
    // Récupérer les recommandations depuis le profil si pas dans scores
    if (empty($recIds)) {
        $stmt2 = $db->prepare('SELECT recommandations FROM profils WHERE conversation_id = :cid');
        $stmt2->execute([':cid' => $conversationId]);
        $profil = $stmt2->fetch();
        $recIds = json_decode($profil['recommandations'] ?? '[]', true);
    }

    $nomC = $lang === 'ar' ? 'c.nom_ar' : 'c.nom_fr';
    $nomS = $lang === 'ar' ? 's.nom_ar' : 's.nom_fr';

    $centres = [];
    if (!empty($recIds)) {
        $spPlaceholders = implode(',', array_fill(0, count($recIds), '?'));
        $sql = "
            SELECT DISTINCT c.id, {$nomC} AS centre_nom, c.telephone, c.adresse_fr AS adresse,
                   GROUP_CONCAT(DISTINCT {$nomS} SEPARATOR ', ') AS specialites
            FROM centres c
            JOIN centre_specialite cs ON c.id = cs.centre_id
            JOIN specialites s ON cs.specialite_id = s.id
            WHERE c.gouvernorat_id = ?
            AND s.id IN ({$spPlaceholders})
            GROUP BY c.id
        ";
        $stmt3 = $db->prepare($sql);
        $params = array_merge([$govId], $recIds);
        $stmt3->execute($params);
        $centres = $stmt3->fetchAll();
    }

    // Fin de l'orientation
    $scores['step'] = null;
    $scores['gouvernorat'] = $govId;
    $stmt4 = $db->prepare('UPDATE profils SET scores = :scores WHERE conversation_id = :cid');
    $stmt4->execute([':scores' => json_encode($scores), ':cid' => $conversationId]);

    if (empty($centres)) {
        // Même si pas de centre avec les spécialités recommandées, montrer les centres du gouvernorat
        $stmt5 = $db->prepare("SELECT c.id, {$nomC} AS centre_nom, c.telephone, c.adresse_fr AS adresse FROM centres c WHERE c.gouvernorat_id = ?");
        $stmt5->execute([$govId]);
        $allCentres = $stmt5->fetchAll();

        if (empty($allCentres)) {
            $reply = $lang === 'ar'
                ? "📍 للأسف لا توجد مراكز مسجلة في {$govNom} في قاعدة البيانات حاليا.\n\nيمكنك زيارة الموقع الرسمي للوكالة: www.atfp.tn\n\nهل تريد البحث في ولاية أخرى؟"
                : "📍 Malheureusement, aucun centre n'est enregistré à {$govNom} dans notre base pour le moment.\n\nVous pouvez consulter le site officiel : www.atfp.tn\n\nVoulez-vous chercher dans un autre gouvernorat ?";
        } else {
            $reply = $lang === 'ar'
                ? "📍 لا يوجد مركز في {$govNom} يقدم بالضبط التكوينات المقترحة، لكن إليك المراكز المتاحة:\n\n"
                : "📍 Aucun centre à {$govNom} ne propose exactement les formations recommandées, mais voici les centres disponibles :\n\n";
            foreach ($allCentres as $c) {
                $tel = $c['telephone'] ? " 📞 " . $c['telephone'] : '';
                $reply .= "🏢 " . $c['centre_nom'] . $tel . "\n";
            }
            $reply .= $lang === 'ar'
                ? "\nتواصل مع المركز لمعرفة التكوينات المتاحة. حظ سعيد! 🍀"
                : "\nContactez le centre pour connaître les formations disponibles. Bonne chance ! 🍀";
        }
    } else {
        $reply = $lang === 'ar'
            ? "📍 إليك المراكز في {$govNom} التي تقدم التكوينات المقترحة:\n\n"
            : "📍 Voici les centres à {$govNom} qui proposent vos formations recommandées :\n\n";

        foreach ($centres as $c) {
            $tel = $c['telephone'] ? " 📞 " . $c['telephone'] : '';
            $addr = $c['adresse'] ? "\n   📫 " . $c['adresse'] : '';
            $reply .= "🏢 " . $c['centre_nom'] . $tel . $addr . "\n";
            $reply .= "   🎓 " . $c['specialites'] . "\n\n";
        }

        $reply .= $lang === 'ar'
            ? "✅ هذه هي توصيتي لك! هل تريد معرفة المزيد عن شروط التسجيل؟"
            : "✅ Voilà ma recommandation ! Voulez-vous en savoir plus sur les conditions d'inscription ?";
    }

    return [
        'reply'  => $reply,
        'intent' => 'orientation',
        'data'   => ['type' => 'centres_recommandes', 'items' => $centres],
    ];
}

/**
 * Réponse par défaut
 */
function getDefaultResponse(array $intents, string $lang): array
{
    foreach ($intents['intents'] ?? [] as $intent) {
        if ($intent['tag'] === 'default') {
            return [
                'reply'  => getRandomResponse($intent['responses'][$lang] ?? []),
                'intent' => 'default',
            ];
        }
    }

    $fallback = $lang === 'ar'
        ? 'عذرا، لم أفهم سؤالك. حاول مرة أخرى.'
        : 'Désolé, je n\'ai pas compris. Essayez de reformuler.';

    return ['reply' => $fallback, 'intent' => 'default'];
}

/**
 * Choisir une réponse aléatoire
 */
function getRandomResponse(array $responses): string
{
    if (empty($responses)) {
        return '';
    }
    return $responses[array_rand($responses)];
}
