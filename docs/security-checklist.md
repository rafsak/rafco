# Security Checklist

- [x] Mots de passe hashés en bcrypt (`password_hash`, `password_verify`)
- [x] Requêtes SQL en statements préparés PDO
- [x] Sessions HTTPOnly + SameSite + rotation d'ID
- [x] Protection CSRF sur formulaires sensibles
- [x] Protection XSS via fonction `e()`
- [x] RBAC centralisé (`has_permission`, `require_permission`)
- [x] Journal d'audit non éditable applicativement
- [ ] Ajout recommandé: Content Security Policy stricte
- [ ] Ajout recommandé: 2FA pour profils administrateurs
- [ ] Ajout recommandé: chiffrement au repos pour archives légales
