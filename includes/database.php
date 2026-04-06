<?php
/**
 * Radio Mehna V2 - Connexion Base de Données
 * PDO sécurisé avec prepared statements
 */

require_once __DIR__ . '/config.php';

/**
 * Singleton pour la connexion PDO
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die('Erreur de connexion : ' . $e->getMessage());
            } else {
                die('Erreur de connexion à la base de données.');
            }
        }
    }

    return $pdo;
}

/**
 * Exécuter une requête avec des paramètres
 */
function dbQuery(string $sql, array $params = []): PDOStatement
{
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Récupérer une seule ligne
 */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $result = dbQuery($sql, $params)->fetch();
    return $result ?: null;
}

/**
 * Récupérer toutes les lignes
 */
function dbFetchAll(string $sql, array $params = []): array
{
    return dbQuery($sql, $params)->fetchAll();
}

/**
 * Insérer et retourner l'ID
 */
function dbInsert(string $table, array $data): int
{
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));

    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    dbQuery($sql, array_values($data));

    return (int) getDB()->lastInsertId();
}

/**
 * Mettre à jour des données
 */
function dbUpdate(string $table, array $data, string $where, array $whereParams = []): int
{
    $setParts = [];
    foreach (array_keys($data) as $column) {
        $setParts[] = "{$column} = ?";
    }
    $setClause = implode(', ', $setParts);

    $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $params = array_merge(array_values($data), $whereParams);

    return dbQuery($sql, $params)->rowCount();
}

/**
 * Supprimer des données
 */
function dbDelete(string $table, string $where, array $params = []): int
{
    $sql = "DELETE FROM {$table} WHERE {$where}";
    return dbQuery($sql, $params)->rowCount();
}

/**
 * Compter les résultats
 */
function dbCount(string $table, string $where = '1=1', array $params = []): int
{
    $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$where}";
    $result = dbFetchOne($sql, $params);
    return (int) ($result['total'] ?? 0);
}
