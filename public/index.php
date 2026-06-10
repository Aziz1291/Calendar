<?php
require_once('../src/Calendar/bootstrap.php');
require_once('../src/Calendar/Month.php');
require_once('../src/Calendar/Events.php');
if (!is_authenticated()) {
    header('Location: login.php');
    exit();
}
$pdo = get_pdo();
$events = new \Calendar\Events($pdo);
$month = new \Calendar\Month($_GET['month'] ?? null, $_GET['year'] ?? null);
$weeks = $month->getWeeks();
$start = $month->getStartingDay();
$start = $start->format('N') === '1' ? $start : $start->modify('last monday');
$end = (clone $start)->modify('+' . (6 + 7 * ($weeks - 1)) . 'days');
$user = current_user();
if ($user->isAdmin()) {
    $userId = null;
} else {
    $userId = $user->getId();
}
$events = $events->getEventsBetweenByDay($start, $end, $userId);
require_once('../views/header.php');
?>
<div class="calendar">
    <div class="d-flex flex-row align-items-center justify-content-between mx-sm-3">
        <h1><?= $month->toString() ?></h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="container">
                <div class="alert alert-success">
                    <?= $user->isAdmin() ? 'Company event created successfully' : 'Day-off request submitted successfully' ?>
                </div>
            </div>
        <?php endif; ?>
        <div>
            <a href="index.php?month=<?= $month->previousMonth()->month ?>&year=<?= $month->previousMonth()->year ?>"
                class="btn btn-primary">&lt;</a>
            <a href="index.php?month=<?= $month->nextMonth()->month ?>&year=<?= $month->nextMonth()->year ?>"
                class="btn btn-primary">&gt;</a>
        </div>
    </div>





    <table class="calendar__table calendar__table--<?= $weeks ?>weeks">
        <?php for ($week = 0; $week < $weeks; $week++): ?>
            <tr>
                <?php foreach ($month->days as $k => $day):
                    $date = (clone $start)->modify("+" . ($k + $week * 7) . " days");
                    $eventsForDay = $events[$date->format('Y-m-d')] ?? [];
                    $isToday = date('Y-m-d') === $date->format('Y-m-d');
                    ?>

                    <td
                        class="<?= !$month->withinMonth($date) ? 'calendar__othermonth' : '' ?> <?= $isToday ? 'is-today' : '' ?>">
                        <?php if ($week === 0): ?>
                            <div class="calendar__weekday"><?= $day ?></div>
                        <?php endif; ?>
                        <a href="add.php?date=<?= $date->format('Y-m-d') ?>" class="calendar__day"><?= $date->format('d') ?></a>
                        <?php foreach ($eventsForDay as $event): ?>
                            <div class="calendar__event">
                                <a href="<?php if ($event['admin_event'] == 1): ?>event.php?id=<?= $event['id']; ?> <?php else: ?>edit.php?id=<?= $event['id']; ?><?php endif; ?>"
                                    class="<?= $event['status'] ?>"><?= htmlspecialchars($event['name']) ?></a>
                            </div>
                        <?php endforeach; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endfor; ?>
    </table>
    <a href="add.php" class="calendar__button">+</a>
</div>

<?php require_once('../views/footer.php'); ?>