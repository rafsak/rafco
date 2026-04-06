<?php
/**
 * Radio Mehna V2 - AJAX Track Podcast Play
 */
require_once __DIR__ . '/../includes/init.php';

$podcastId = (int)($_POST['podcast_id'] ?? 0);

if ($podcastId > 0) {
    try {
        incrementPodcastPlays($podcastId);
        trackAction('podcast', $podcastId, 'play');
    } catch (Exception $e) {
        // Silently ignore
    }
}

jsonResponse(['success' => true]);
