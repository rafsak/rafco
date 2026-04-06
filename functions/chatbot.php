<?php
/**
 * Radio Mehna V2 - Module Chatbot IA
 * Recherche intelligente, suggestions, réponses automatiques
 */

/**
 * Traiter un message du chatbot
 */
function processChatMessage(string $message, string $lang = 'fr'): array
{
    $message = sanitize($message);
    $messageLower = mb_strtolower($message);

    // 1. Chercher dans les réponses prédéfinies
    $predefined = findPredefinedAnswer($messageLower, $lang);
    if ($predefined) {
        return [
            'type'    => 'text',
            'message' => $predefined['answer'],
            'source'  => 'predefined',
        ];
    }

    // 2. Chercher dans les émissions
    $programs = searchProgramsForChat($messageLower);
    if (!empty($programs)) {
        return [
            'type'     => 'programs',
            'message'  => getChatLabel('found_programs', $lang),
            'data'     => $programs,
            'source'   => 'search',
        ];
    }

    // 3. Chercher dans les podcasts
    $podcasts = searchPodcastsForChat($messageLower);
    if (!empty($podcasts)) {
        return [
            'type'     => 'podcasts',
            'message'  => getChatLabel('found_podcasts', $lang),
            'data'     => $podcasts,
            'source'   => 'search',
        ];
    }

    // 4. Réponse par défaut avec suggestions
    return [
        'type'        => 'suggestion',
        'message'     => getChatLabel('no_answer', $lang),
        'suggestions' => getDefaultSuggestions($lang),
        'source'      => 'default',
    ];
}

/**
 * Trouver une réponse prédéfinie (avec tolérance aux fautes)
 */
function findPredefinedAnswer(string $query, string $lang): ?array
{
    try {
        $sql = "SELECT * FROM chatbot_data
                WHERE lang = ? AND is_active = 1
                ORDER BY priority DESC";
        $answers = dbFetchAll($sql, [$lang]);

        foreach ($answers as $answer) {
            $keywords = explode(',', mb_strtolower($answer['keywords']));
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    continue;
                }
                // Correspondance exacte ou partielle
                if (mb_strpos($query, $keyword) !== false) {
                    return $answer;
                }
                // Tolérance aux fautes (distance de Levenshtein)
                $words = explode(' ', $query);
                foreach ($words as $word) {
                    if (mb_strlen($word) > 3 && levenshtein($word, $keyword) <= 2) {
                        return $answer;
                    }
                }
            }
        }
    } catch (Exception $e) {
        // DB pas encore configurée
    }

    return null;
}

/**
 * Chercher des émissions pour le chatbot
 */
function searchProgramsForChat(string $query): array
{
    try {
        $sql = "SELECT id, title, slug, description, start_time, end_time, broadcast_days
                FROM programs
                WHERE is_active = 1 AND (
                    LOWER(title) LIKE ? OR LOWER(description) LIKE ?
                )
                LIMIT 3";
        $like = '%' . $query . '%';
        return dbFetchAll($sql, [$like, $like]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Chercher des podcasts pour le chatbot
 */
function searchPodcastsForChat(string $query): array
{
    try {
        $sql = "SELECT id, title, slug, description, duration, category
                FROM podcasts
                WHERE is_published = 1 AND (
                    LOWER(title) LIKE ? OR LOWER(description) LIKE ?
                )
                LIMIT 3";
        $like = '%' . $query . '%';
        return dbFetchAll($sql, [$like, $like]);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtenir les suggestions par défaut
 */
function getDefaultSuggestions(string $lang): array
{
    $suggestions = [
        'fr' => [
            'Quelles sont les émissions du jour ?',
            'Quels podcasts recommandez-vous ?',
            'Comment écouter Radio Mehna ?',
            'Quels sont vos horaires ?',
        ],
        'ar' => [
            'ما هي برامج اليوم؟',
            'ما البودكاست التي توصون بها؟',
            'كيف أستمع إلى راديو مهنة؟',
            'ما هي مواعيدكم؟',
        ],
        'en' => [
            'What shows are on today?',
            'What podcasts do you recommend?',
            'How can I listen to Radio Mehna?',
            'What are your schedules?',
        ],
    ];

    return $suggestions[$lang] ?? $suggestions['fr'];
}

/**
 * Labels du chatbot par langue
 */
function getChatLabel(string $key, string $lang): string
{
    $labels = [
        'found_programs' => [
            'fr' => 'Voici les émissions que j\'ai trouvées :',
            'ar' => 'إليك البرامج التي وجدتها:',
            'en' => 'Here are the shows I found:',
        ],
        'found_podcasts' => [
            'fr' => 'Voici les podcasts correspondants :',
            'ar' => 'إليك البودكاست المطابقة:',
            'en' => 'Here are the matching podcasts:',
        ],
        'no_answer' => [
            'fr' => 'Je n\'ai pas trouvé de réponse exacte. Voici quelques suggestions qui pourraient vous aider :',
            'ar' => 'لم أجد إجابة دقيقة. إليك بعض الاقتراحات التي قد تساعدك:',
            'en' => 'I couldn\'t find an exact answer. Here are some suggestions that might help:',
        ],
    ];

    return $labels[$key][$lang] ?? $labels[$key]['fr'] ?? $key;
}

/**
 * Obtenir les recommandations IA (basées sur la popularité)
 */
function getAIRecommendations(string $type = 'programs', int $limit = 4): array
{
    try {
        if ($type === 'programs') {
            return getPopularPrograms($limit);
        }
        return getPopularPodcasts($limit);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Recherche intelligente globale (avec tolérance aux fautes)
 */
function smartSearch(string $query, int $limit = 10): array
{
    $results = [];
    $like = '%' . mb_strtolower(sanitize($query)) . '%';

    try {
        // Recherche dans les émissions
        $programs = dbFetchAll(
            "SELECT id, title, slug, description, 'program' as type
             FROM programs WHERE is_active = 1 AND (LOWER(title) LIKE ? OR LOWER(description) LIKE ?)
             LIMIT ?",
            [$like, $like, $limit]
        );

        // Recherche dans les podcasts
        $podcasts = dbFetchAll(
            "SELECT id, title, slug, description, 'podcast' as type
             FROM podcasts WHERE is_published = 1 AND (LOWER(title) LIKE ? OR LOWER(description) LIKE ?)
             LIMIT ?",
            [$like, $like, $limit]
        );

        // Recherche dans les articles
        $posts = dbFetchAll(
            "SELECT id, title, slug, excerpt as description, 'post' as type
             FROM posts WHERE status = 'published' AND (LOWER(title) LIKE ? OR LOWER(content) LIKE ?)
             LIMIT ?",
            [$like, $like, $limit]
        );

        $results = array_merge($programs, $podcasts, $posts);
    } catch (Exception $e) {
        // DB pas encore configurée
    }

    return array_slice($results, 0, $limit);
}
