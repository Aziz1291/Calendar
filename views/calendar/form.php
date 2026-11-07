<?php $formUser = current_user(); ?>
<?php
// Employees can only request time off from today on; the server checks the same rule.
$minDate = $formUser->isAdmin() ? '' : ' min="' . date('Y-m-d') . '"';
$fieldError = function (string $field) use ($errors) {
    if (isset($errors[$field])) {
        echo '<div class="form-text text-danger">&bull; ' . h($errors[$field]) . '</div>';
    }
};
?>

<div class="form-group">
    <label for="name" class="form-label"><?= $formUser->isAdmin() ? 'Event Title / Subject *' : 'Reason for Day Off *' ?></label>
    <input type="text" name="name" id="name" class="form-control" minlength="3" maxlength="255"
        placeholder="<?= $formUser->isAdmin() ? 'e.g. Q2 Company All-Hands or National Holiday' : 'e.g. Annual Family Leave or Medical Appointment' ?>"
        value="<?= isset($data['name']) ? h($data['name']) : '' ?>" required>
    <?php $fieldError('name'); ?>
</div>

<div class="row g-3">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="start_date" class="form-label"><?= $formUser->isAdmin() ? 'Event Date *' : 'Start Date *' ?></label>
            <input type="date" name="start_date" id="start_date" class="form-control"<?= $minDate ?>
                value="<?= isset($data['start_date']) ? h($data['start_date']) : '' ?>" required>
            <?php $fieldError('start_date'); ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="end_date" class="form-label"><?= $formUser->isAdmin() ? 'End Date (Optional)' : 'End Date (Optional)' ?></label>
            <input type="date" name="end_date" id="end_date" class="form-control"<?= $minDate ?>
                value="<?= isset($data['end_date']) ? h($data['end_date']) : '' ?>">
            <div class="form-text">Leave blank for a single-day <?= $formUser->isAdmin() ? 'event' : 'request' ?>.</div>
            <?php $fieldError('end_date'); ?>
        </div>
    </div>
</div>

<?php if ($formUser->isAdmin()): ?>
    <div class="row g-3">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="start_time" class="form-label">Start Time (Optional)</label>
                <input type="time" name="start_time" id="start_time" class="form-control"
                    value="<?= isset($data['start_time']) ? h($data['start_time']) : '' ?>">
                <?php $fieldError('start_time'); ?>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="end_time" class="form-label">End Time (Optional)</label>
                <input type="time" name="end_time" id="end_time" class="form-control"
                    value="<?= isset($data['end_time']) ? h($data['end_time']) : '' ?>">
                <?php $fieldError('end_time'); ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <?php $fieldError('end_time'); ?>
<?php endif; ?>

<div class="form-group">
    <label for="description" class="form-label"><?= $formUser->isAdmin() ? 'Detailed Description / Agenda' : 'Additional Notes / Justification' ?></label>
    <textarea name="description" id="description" rows="4" class="form-control" maxlength="2000"
        placeholder="<?= $formUser->isAdmin() ? 'Provide logistical details, conference rooms, video links...' : 'Note covering personnel, emergency contact, or context...' ?>"><?= isset($data['description']) ? h($data['description']) : '' ?></textarea>
    <?php $fieldError('description'); ?>
</div>
