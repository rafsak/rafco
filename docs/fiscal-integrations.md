# Logique d'intégration TEJ / TTN / TunTrace

## TEJ (retenue à la source)
1. En phase brouillon, le moteur calcule la retenue ligne par ligne (`invoice_lines.line_withholding`).
2. Avant finalisation, la facture passe `PENDING_TEJ`.
3. Envoi du payload TEJ (JSON/XML selon spécification API) et journalisation dans `fiscal_integration_logs`.
4. Si succès: statut `VALIDATED_TEJ` + stockage référence TEJ (`invoices.tej_reference`).
5. Si échec: statut conservé, log `FAILED`, nouvelle tentative contrôlée.

## TTN (transmission & horodatage)
1. Après validation TEJ, envoi du document fiscal à TTN.
2. Enregistrer hash documentaire + timestamp + preuve technique.
3. Persister réponse dans `fiscal_integration_logs` (channel TTN).
4. Si accusé reçu: statut `SENT_TTN` puis `FINALIZED`, facture figée (`is_immutable = 1`).

## TunTrace (QR fiscal)
1. Générer payload QR depuis éléments fiscaux: NIF, numéro, date, TTC, hash.
2. Sauvegarder payload dans `invoice_qr_codes` et `invoices.tuntrace_qr_payload`.
3. Rendre QR visible sur PDF facture.

## Audit et archivage
- Chaque transition est tracée (`audit_logs`).
- Pièces légales (PDF/XML/reçus TTN/TEJ) archivées dans `legal_archives` avec SHA-256.
