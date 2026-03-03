# Déploiement (production)

## Prérequis
- Linux + Nginx/Apache
- PHP 8.2+
- Extensions: pdo_mysql, mbstring, openssl, json
- MySQL 8+

## Étapes
1. Déployer code dans `/var/www/rafco`.
2. Créer base et importer `database/install.sql`.
3. Configurer `config/database.php` et `config/app.php`.
4. Pointer virtual host sur `/public`.
5. Appliquer permissions d'écriture:
   - `public/uploads`
   - `public/archives`
   - `logs`
6. Activer HTTPS obligatoire.
7. Mettre en place rotation des logs et sauvegardes DB.

## Post-déploiement
- Changer immédiatement le mot de passe admin par défaut.
- Vérifier les jobs de sauvegarde et d'archivage légal.
- Mettre en place supervision (erreurs PHP, DB, API fiscales).
