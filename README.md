# Rafco ERP Tunisia (PHP natif)

Base de démarrage ERP de type Finco.tn orientée conformité Tunisie 2026.

## 1) Structure des dossiers

```text
/config                Configuration applicative, DB, permissions
/includes              Bootstrapping, sécurité, authentification, RBAC, audit, layout
/modules               Modules fonctionnels (auth, dashboard, users, roles, audit, ...)
/assets                CSS/JS statiques
/database              Scripts SQL (install, seed)
/docs                  Documents MOA, intégration fiscale, sécurité, déploiement
/public                Point d'entrée web
/public/uploads        Fichiers téléversés
/public/archives       Archivage légal (PDF/XML/reçus)
/logs                  Logs applicatifs
```

## 2) Installation rapide

1. Créer la base MySQL `rafco_erp`.
2. Importer `database/install.sql`.
3. Ajuster `config/database.php`.
4. Démarrer le serveur PHP dans la racine:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```
5. Connexion par défaut:
   - Email: `admin@erp.local`
   - Mot de passe: `Admin@2026`

## 3) Modules implémentés dans cette phase

- Authentification sécurisée (login/logout)
- Hash bcrypt
- Sessions protégées + régénération périodique d'ID
- Protection CSRF
- Échappement XSS côté rendu
- RBAC (rôles/permissions)
- Journal d'audit non éditable
- Dashboard de base

## 4) Documents de référence

- `docs/moadesign.md`
- `docs/fiscal-integrations.md`
- `docs/security-checklist.md`
- `docs/deployment.md`
