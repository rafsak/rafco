# MOA - Phase 1 (Schéma + Structure + Auth/RBAC)

## Objectif
Mettre en place les fondations production-ready du futur ERP clone Finco.tn en PHP natif, avec contraintes légales tunisiennes 2026.

## Schéma base de données
Le script `database/install.sql` contient:
- Tables IAM: `users`, `roles`, `permissions`, `user_roles`, `role_permissions`
- Paramétrage société/fiscalité: `company_settings`, `company_tax_rates`
- Référentiels métier: `partners`, `products`, `product_categories`, `warehouses`
- Cycle vente/achat: `quotes`, `quote_lines`, `invoices`, `invoice_lines`
- Stocks et règlements: `stock_movements`, `payments`, `payment_allocations`
- Conformité e-facture: `fiscal_integration_logs`, `invoice_qr_codes`, `legal_archives`
- Traçabilité: `audit_logs`

## Principes métier intégrés
- Numérotation continue légale (champ `legal_sequence` unique)
- Immutabilité facture (`is_immutable`, `immutable_hash`, `fiscal_timestamp`)
- Journaux d'intégration TEJ/TTN/TunTrace (requêtes/réponses stockées)
- Archivage légal hashé

## Livrables phase 1
- Schéma SQL complet + données de test
- Arborescence modulaire sans MVC
- Authentification, rôles/permissions, audit
