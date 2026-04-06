<?php
/**
 * ATFP Chatbot — Installateur automatique
 * Exécute le script SQL pour créer la base et les données de test
 */

// Paramètres (à adapter si besoin)
$host = 'localhost';
$user = 'root';
$pass = '';

$sqlFile = __DIR__ . '/database.sql';

if (!file_exists($sqlFile)) {
    die("❌ Fichier database.sql introuvable.\n");
}

try {
    // Connexion sans base de données
    $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✅ Connexion MySQL réussie.\n";

    // Lire et exécuter le SQL statement par statement
    $sql = file_get_contents($sqlFile);

    // Supprimer les commentaires SQL
    $sql = preg_replace('/--.*$/m', '', $sql);

    // Séparer les requêtes par ;
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($s) { return $s !== ''; }
    );

    $count = 0;
    foreach ($statements as $stmt) {
        $pdo->exec($stmt);
        $count++;
    }

    echo "✅ {$count} requêtes exécutées.\n";

    echo "✅ Base de données 'atfp_chatbot' créée avec succès.\n";
    echo "✅ Tables créées et données de test insérées.\n";
    echo "\n";
    echo "🎉 Installation terminée !\n";
    echo "   → Ouvrez index.html dans votre navigateur\n";
    echo "   → Admin : admin.php (login: admin / admin123)\n";

} catch (PDOException $ex) {
    echo "❌ Erreur : " . $ex->getMessage() . "\n";
    echo "\n";
    echo "Vérifiez que :\n";
    echo "  1. MySQL/MariaDB est démarré dans XAMPP\n";
    echo "  2. Les identifiants sont corrects (root / pas de mot de passe)\n";
    echo "  3. Le port MySQL est bien 3306\n";
}
