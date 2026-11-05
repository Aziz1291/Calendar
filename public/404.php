<?php
http_response_code(404);
require_once __DIR__ . '/../src/Calendar/bootstrap.php';
render('header.php', ['title' => 'Page Not Found']);
?>

<div class="form-sheet text-center py-5" style="max-width: 520px;">
    <div style="font-size: 4rem; font-weight: 800; color: var(--accent); line-height: 1; letter-spacing: -0.04em;">
        404
    </div>
    <h1 class="mt-3 mb-2" style="font-size: 1.4rem;">Record or Page Not Found</h1>
    <p class="text-muted mb-4" style="font-size: 0.92rem;">
        The calendar event, schedule entry, or endpoint you requested cannot be located. It may have been relocated or permanently deleted.
    </p>
    <a href="index.php" class="btn btn-primary">
        &larr; Return to Calendar Grid
    </a>
</div>

<?php render('footer.php'); ?>
