<?php
/**
 * Radio Mehna V2 - Page Contact
 */
setPageMeta(__('contact.title'), __('contact.title') . ' - ' . __('site.description'));

$success = false;
$error = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = true;
    } else {
        $name = postParam('name');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $subject = postParam('subject');
        $message = postParam('message');

        if (empty($name) || empty($email) || empty($message)) {
            $error = true;
        } elseif (!isValidEmail($email)) {
            $error = true;
        } else {
            try {
                dbInsert('contacts', [
                    'name'       => $name,
                    'email'      => $email,
                    'subject'    => $subject,
                    'message'    => $message,
                    'ip_address' => getClientIP(),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $success = true;
            } catch (Exception $e) {
                $error = true;
            }
        }
    }
}

require_once INCLUDES_PATH . '../templates/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1><i class="bi bi-envelope text-accent"></i> <?= __('contact.title') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= langUrl('/') ?>"><?= __('nav.home') ?></a></li>
                <li class="breadcrumb-item active"><?= __('contact.title') ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <?php if ($success): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= __('contact.success') ?></div>
                <?php elseif ($error): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> <?= __('contact.error') ?></div>
                <?php endif; ?>

                <div class="card-mehna p-4">
                    <form method="POST" class="contact-form">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label"><?= __('contact.name') ?></label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="<?= __('contact.name') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= __('contact.email') ?></label>
                            <input type="email" name="email" class="form-control" required
                                   placeholder="<?= __('contact.email') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= __('contact.subject') ?></label>
                            <input type="text" name="subject" class="form-control"
                                   placeholder="<?= __('contact.subject') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><?= __('contact.message') ?></label>
                            <textarea name="message" class="form-control" rows="5" required
                                      placeholder="<?= __('contact.message') ?>"></textarea>
                        </div>

                        <button type="submit" class="btn btn-accent">
                            <i class="bi bi-send-fill"></i> <?= __('contact.send') ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <!-- Contact Info -->
                <div class="card-mehna p-4 mb-4">
                    <h5 class="text-accent mb-3"><i class="bi bi-geo-alt-fill"></i> <?= __('contact.address') ?></h5>
                    <p class="text-muted">Tunisie</p>

                    <h5 class="text-accent mb-3 mt-4"><i class="bi bi-telephone-fill"></i> <?= __('contact.phone') ?></h5>
                    <p class="text-muted">+216 XX XXX XXX</p>

                    <h5 class="text-accent mb-3 mt-4"><i class="bi bi-envelope-fill"></i> <?= __('contact.email') ?></h5>
                    <p class="text-muted">contact@radiomehna.tn</p>
                </div>

                <!-- Social -->
                <div class="card-mehna p-4">
                    <h5 class="text-accent mb-3"><i class="bi bi-share-fill"></i> <?= __('contact.social') ?></h5>
                    <div class="footer-social">
                        <a href="https://facebook.com/radiomehna" target="_blank"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.youtube.com/@RadioMehna-2024" target="_blank"><i class="bi bi-youtube"></i></a>
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once INCLUDES_PATH . '../templates/footer.php'; ?>
