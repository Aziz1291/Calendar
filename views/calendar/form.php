<?php $formUser = current_user(); ?>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="name"><?= $formUser->isAdmin() ? 'Event Title:' : 'Reason for Day Off:' ?></label>
            <input type="text" name="name" id="name" class="form-control"
                value="<?= isset($data['name']) ? h($data['name']) : '' ?>" required>
            <?php if (isset($errors['name'])): ?>
                <small class="form-text text-muted"><?= $errors['name'] ?></small>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label for="start_date"><?= $formUser->isAdmin() ? 'Event Date:' : 'Start Date:' ?></label>
            <input type="date" name="start_date" id="start_date" class="form-control"
                value="<?= isset($data['start_date']) ? h($data['start_date']) : '' ?>" required>
            <?php if (isset($errors['start_date'])): ?>
                <small class="form-text text-muted"><?= $errors['start_date'] ?></small>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <div class="form-group">
            <label for="end_date"><?= $formUser->isAdmin() ? 'End Date (optional):' : 'End Date:' ?></label>
            <input type="date" name="end_date" id="end_date" class="form-control"
                value="<?= isset($data['end_date']) ? h($data['end_date']) : '' ?>"
                <?= !$formUser->isAdmin() ? 'required' : '' ?>>
            <small class="form-text text-muted">Leave blank for a single day <?= $formUser->isAdmin() ? 'event' : 'off' ?></small>
            <?php if (isset($errors['end_date'])): ?>
                <small class="form-text text-danger"><?= $errors['end_date'] ?></small>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($formUser->isAdmin()): ?>
    <div class="col-sm-3">
        <div class="form-group">
            <label for="start_time">Start Time (optional):</label>
            <input type="time" name="start_time" id="start_time" class="form-control"
                value="<?= isset($data['start_time']) ? h($data['start_time']) : '' ?>" placeholder="HH:MM">
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label for="end_time">End Time (optional):</label>
            <input type="time" name="end_time" id="end_time" class="form-control"
                value="<?= isset($data['end_time']) ? h($data['end_time']) : '' ?>" placeholder="HH:MM">
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="form-group">
    <label for="description"><?= $formUser->isAdmin() ? 'Event Description:' : 'Additional Notes (optional):' ?></label>
    <textarea name="description" id="description"
        class="form-control"><?= isset($data['description']) ? h($data['description']) : '' ?></textarea>
</div>