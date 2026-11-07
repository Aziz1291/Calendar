<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('../src/Calendar/bootstrap.php');
require_once('../src/Calendar/Events.php');
require_once('../src/Calendar/Event.php');
require_once('../src/Calendar/User.php');

if (!is_authenticated()) {
    header('Location: login.php');
    exit;
}

$user = current_user();
if (!$user || !$user->isAdmin()) {
    header('Location: eventsTable.php');
    exit;
}

$pdo = get_pdo();
$events = new \Calendar\Events($pdo);

$eventId = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$eventId) {
    header('Location: eventsTable.php');
    exit;
}

try {
    $event = $events->find((int)$eventId);
} catch (\Exception $e) {
    e404();
}

$from = $_GET['from'] ?? $_POST['from'] ?? '';
$returnUrl = ($from === 'dashboard') ? 'dashboard.php' : 'eventsTable.php';

$reasonError = null;
$reason = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim((string) ($_POST['rejectionReason'] ?? ''));
    // The reason is required on the server too, not only by the browser.
    if (mb_strlen($reason) < 3) {
        $reasonError = 'Give a reason of at least 3 characters.';
    } elseif (mb_strlen($reason) > 1000) {
        $reasonError = 'The reason must be 1000 characters or fewer.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reasonError === null) {
    $event->setRejectionReason($reason);
    $event->setStatus('Rejected');
    $events->update($event);
    $_SESSION['success'] = "Leave request '{$event->getName()}' has been declined.";
    header('Location: ' . $returnUrl);
    exit;
}

$eventCreator = new \Calendar\User();
$eventCreator->getUser($event->getUserId());

render('header.php', ['title' => 'Decline Request: ' . $event->getName()]);
?>

<div class="form-sheet" style="max-width: 580px;">
    <div class="mb-3">
        <a href="<?= $returnUrl ?>" class="text-muted" style="font-size: 0.85rem;">
            &larr; <?= ($from === 'dashboard') ? 'Back to Executive Dashboard' : 'Back to Requests Matrix' ?>
        </a>
    </div>

    <div class="form-sheet__head">
        <div class="text-danger fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">
            Administrative Action
        </div>
        <h1 class="text-danger">Decline Leave Request</h1>
        <p>Review details and supply a mandatory explanation for employee transparency.</p>
    </div>

    <!-- Target Event Summary -->
    <div class="p-3 mb-4 rounded border" style="background: var(--tint); border-color: var(--line) !important; font-size: 0.88rem;">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Target Employee</span>
            <span class="fw-semibold text-dark"><?= h($eventCreator->getUsername()) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Request Reason</span>
            <span class="fw-semibold text-dark"><?= h($event->getName()) ?></span>
        </div>
        <div class="d-flex justify-content-between">
            <span class="text-muted">Schedule Span</span>
            <span class="font-monospace text-dark">
                <?= $event->getStart()->format('Y-m-d') ?> &rarr; <?= $event->getEnd()->format('Y-m-d') ?>
            </span>
        </div>
    </div>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?= $event->getId() ?>">
        <input type="hidden" name="from" value="<?= h($from) ?>">

        <div class="form-group mb-4">
            <label for="rejectionReason" class="form-label">
                Reason for Rejection *
            </label>
            <?php if ($reasonError): ?>
                <div class="alert alert-danger py-2" role="alert"><?= h($reasonError) ?></div>
            <?php endif; ?>
            <textarea name="rejectionReason" id="rejectionReason" rows="4" class="form-control" minlength="3" maxlength="1000"
                      placeholder="e.g. Schedule conflicts with core team release milestone, or insufficient remaining PTO allowance..." required><?= h($reason) ?></textarea>
            <div class="form-text">This feedback will be recorded in the employee's dossier and audit log.</div>
        </div>

        <div class="form-actions justify-content-between">
            <a href="<?= $returnUrl ?>" class="btn btn-secondary">Cancel &amp; Keep Pending</a>
            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
        </div>
    </form>
</div>

<?php render('footer.php'); ?>
