<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_authentication();

$user = current_user();

$kpis = [
    'total_users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'active_clients' => (int) db()->query("SELECT COUNT(*) FROM partners WHERE partner_type IN ('CLIENT','BOTH') AND is_active = 1")->fetchColumn(),
    'draft_invoices' => (int) db()->query("SELECT COUNT(*) FROM invoices WHERE status = 'DRAFT'")->fetchColumn(),
    'pending_fiscal' => (int) db()->query("SELECT COUNT(*) FROM invoices WHERE status IN ('PENDING_TEJ','VALIDATED_TEJ','SENT_TTN')")->fetchColumn(),
];

$pageTitle = 'Dashboard';
include __DIR__ . '/../../includes/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3">Bienvenue, <?= e($user['full_name']) ?></h1>
        <p class="text-muted mb-0">ERP tunisien - base conforme 2026.</p>
    </div>
</div>
<div class="row g-3">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Utilisateurs</div><div class="h4"><?= $kpis['total_users'] ?></div></div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Clients actifs</div><div class="h4"><?= $kpis['active_clients'] ?></div></div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Factures brouillon</div><div class="h4"><?= $kpis['draft_invoices'] ?></div></div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Flux fiscal en attente</div><div class="h4"><?= $kpis['pending_fiscal'] ?></div></div></div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/layout/footer.php'; ?>
