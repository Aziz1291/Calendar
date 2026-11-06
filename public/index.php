<?php
require_once('../src/Calendar/bootstrap.php');
require_once('../src/Calendar/Month.php');
require_once('../src/Calendar/Events.php');

if (!is_authenticated()) {
    header('Location: login.php');
    exit();
}

$pdo = get_pdo();
$eventsModel = new \Calendar\Events($pdo);
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

$events = $eventsModel->getEventsBetweenByDay($start, $end, $userId);

// Prepare days of current month for mobile carousel & list feed
$firstOfMonth = $month->getStartingDay();
$daysInMonth = (int)$firstOfMonth->format('t');
$currentYear = (int)$firstOfMonth->format('Y');
$currentMonthNum = (int)$firstOfMonth->format('m');

$mobileDays = [];
$allMonthEvents = [];

for ($d = 1; $d <= $daysInMonth; $d++) {
    $dayDate = (new \DateTime())->setDate($currentYear, $currentMonthNum, $d);
    $dStr = $dayDate->format('Y-m-d');
    $dayEvts = $events[$dStr] ?? [];

    $mobileDays[] = [
        'date'    => $dStr,
        'dayNum'  => $d,
        'dayName' => $dayDate->format('D'),
        'isToday' => ($dStr === date('Y-m-d')),
        'events'  => $dayEvts,
    ];

    foreach ($dayEvts as $evt) {
        $evtWithDay = $evt;
        $evtWithDay['for_date'] = $dStr;
        $allMonthEvents[] = $evtWithDay;
    }
}

require_once('../views/header.php');
?>

<div class="calendar-view">
    <!-- Feedback Alerts -->
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2">
            <span>&check;</span>
            <span><?= $user->isAdmin() ? 'Company event scheduled successfully.' : 'Day-off request submitted successfully.' ?></span>
        </div>
    <?php endif; ?>

    <!-- Calendar Toolbar -->
    <div class="calendar-toolbar">
        <div class="calendar-title-group">
            <h1 class="calendar-title mb-0"><?= h($month->toString()) ?></h1>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="segmented-control" role="group" aria-label="Month navigation">
                <a href="index.php?month=<?= $month->previousMonth()->month ?>&year=<?= $month->previousMonth()->year ?>"
                   class="btn" title="Previous Month">&larr;</a>
                <a href="index.php" class="btn" title="Current Month">Today</a>
                <a href="index.php?month=<?= $month->nextMonth()->month ?>&year=<?= $month->nextMonth()->year ?>"
                   class="btn" title="Next Month">&rarr;</a>
            </div>

            <a href="add.php" class="btn btn-primary">
                <?= $user->isAdmin() ? '+ Create Company Event' : '+ Request Day Off' ?>
            </a>
        </div>
    </div>

    <!-- Architectural Grid (Desktop) -->
    <div class="calendar-grid-card">
        <table class="calendar-grid">
            <thead>
                <tr>
                    <?php foreach ($month->days as $day): ?>
                        <th><?= h($day) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($week = 0; $week < $weeks; $week++): ?>
                    <tr>
                        <?php foreach ($month->days as $k => $day):
                            $date = (clone $start)->modify("+" . ($k + $week * 7) . " days");
                            $eventsForDay = $events[$date->format('Y-m-d')] ?? [];
                            $isToday = (date('Y-m-d') === $date->format('Y-m-d'));
                            $isOtherMonth = !$month->withinMonth($date);
                        ?>
                            <td class="<?= $isOtherMonth ? 'calendar__othermonth' : '' ?> <?= $isToday ? 'is-today' : '' ?>">
                                <div class="calendar-cell__header">
                                    <a href="add.php?date=<?= $date->format('Y-m-d') ?>"
                                       class="calendar-day-num"
                                       title="Schedule for <?= $date->format('M j, Y') ?>">
                                        <?= $date->format('j') ?>
                                    </a>
                                    <?php if ($isToday): ?>
                                        <span class="badge" style="font-size: 9px; padding: 2px 4px; background: var(--accent); color: #fff;">TODAY</span>
                                    <?php endif; ?>
                                </div>

                                <div class="calendar-events-stream">
                                    <?php foreach ($eventsForDay as $event):
                                        $statusClass = $event['status'];
                                        $isAdminEvt = (int)($event['admin_event'] ?? 0) === 1;
                                        $chipClass = $isAdminEvt ? 'admin-event' : $statusClass;
                                        $toneClass = $isAdminEvt ? 'tone-sea' : ($statusClass === 'Approved' ? 'tone-olive' : ($statusClass === 'Rejected' ? 'tone-rose' : 'tone-clay'));
                                        $url = ($isAdminEvt && !$user->isAdmin()) ? "event.php?id=" . $event['id'] : "edit.php?id=" . $event['id'];
                                    ?>
                                        <a href="<?= $url ?>"
                                           class="event-chip <?= $chipClass ?> <?= $toneClass ?>"
                                           title="<?= h($event['name']) ?> (<?= $event['status'] ?>)">
                                            <?= h($event['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile Hybrid Layout (Carousel + List Feed) -->
    <div class="calendar-mobile-view">
        <!-- Horizontal Date Carousel -->
        <div class="mobile-carousel-container">
            <div class="mobile-carousel" role="tablist" id="mobileCarousel">
                <button type="button" class="mobile-carousel-day mobile-carousel-day--all" data-date="all" title="View all events in month">
                    <span class="mobile-carousel-day__name">ALL</span>
                    <span class="mobile-carousel-day__num">&infin;</span>
                    <span class="mobile-carousel-day__dots"></span>
                </button>
                <?php foreach ($mobileDays as $mDay):
                    $hasEvents = count($mDay['events']) > 0;
                ?>
                    <button type="button"
                            class="mobile-carousel-day <?= $mDay['isToday'] ? 'is-today' : '' ?> <?= $hasEvents ? 'has-events' : '' ?>"
                            data-date="<?= $mDay['date'] ?>"
                            data-daynum="<?= $mDay['dayNum'] ?>"
                            data-has-events="<?= $hasEvents ? '1' : '0' ?>"
                            title="<?= $mDay['dayName'] ?>, <?= date('M j', strtotime($mDay['date'])) ?>">
                        <span class="mobile-carousel-day__name"><?= $mDay['dayName'] ?></span>
                        <span class="mobile-carousel-day__num"><?= $mDay['dayNum'] ?></span>
                        <span class="mobile-carousel-day__dots">
                            <?php
                            $dotsShown = 0;
                            foreach ($mDay['events'] as $evt):
                                if ($dotsShown >= 3) break;
                                $isAdmin = (int)($evt['admin_event'] ?? 0) === 1;
                                $dotTone = $isAdmin ? 'tone-sea' : ($evt['status'] === 'Approved' ? 'tone-olive' : ($evt['status'] === 'Rejected' ? 'tone-rose' : 'tone-clay'));
                            ?>
                                <span class="carousel-dot <?= $dotTone ?>"></span>
                            <?php $dotsShown++; endforeach; ?>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Mobile List Feed -->
        <div class="mobile-feed">
            <div class="mobile-feed__header">
                <div>
                    <h3 id="mobileFeedDateTitle" class="mobile-feed__title">Agenda Feed</h3>
                    <div id="mobileFeedCount" class="mobile-feed__count">Showing events for current month</div>
                </div>
                <a href="add.php" id="mobileAddBtn" class="btn btn-sm btn-primary">
                    <?= $user->isAdmin() ? '+ New Event' : '+ Request Off' ?>
                </a>
            </div>

            <div id="mobileFeedList" class="mobile-feed__list">
                <?php if (empty($allMonthEvents)): ?>
                    <div class="mobile-feed-empty">
                        <p class="mb-2">No events scheduled for <?= h($month->toString()) ?>.</p>
                        <a href="add.php" class="btn btn-sm btn-primary">
                            <?= $user->isAdmin() ? '+ Create Company Event' : '+ Submit Leave Request' ?>
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($allMonthEvents as $ev):
                        $isAdminEvt = (int)($ev['admin_event'] ?? 0) === 1;
                        $chipClass = $isAdminEvt ? 'admin-event' : $ev['status'];
                        $toneClass = $isAdminEvt ? 'tone-sea' : ($ev['status'] === 'Approved' ? 'tone-olive' : ($ev['status'] === 'Rejected' ? 'tone-rose' : 'tone-clay'));
                        $detailUrl = ($isAdminEvt && !$user->isAdmin()) ? "event.php?id=" . $ev['id'] : "edit.php?id=" . $ev['id'];
                        $viewUrl = "event.php?id=" . $ev['id'];
                        $dateFormatted = date('M j, Y', strtotime($ev['for_date']));
                    ?>
                        <div class="mobile-feed-card <?= $toneClass ?>" data-date="<?= $ev['for_date'] ?>">
                            <div class="mobile-feed-card__top">
                                <div class="mobile-feed-card__title"><?= h($ev['name']) ?></div>
                                <span class="status-pill <?= $ev['status'] ?> <?= $toneClass ?>">
                                    <?= $ev['status'] ?>
                                </span>
                            </div>
                            <div class="mobile-feed-card__meta">
                                <span class="mobile-feed-card__time"><?= $dateFormatted ?></span>
                                <span>&middot;</span>
                                <span><?= $isAdminEvt ? 'Company Milestone' : 'Personal Leave' ?></span>
                            </div>
                            <?php if (!empty($ev['description'])): ?>
                                <div style="font-size: 0.82rem; color: var(--ink-secondary); line-height: 1.4;">
                                    <?= h($ev['description']) ?>
                                </div>
                            <?php endif; ?>
                            <div class="mobile-feed-card__footer">
                                <span style="font-size: 0.75rem; color: var(--muted); font-family: var(--font-mono);">
                                    Span: <?= date('M j', strtotime($ev['start'])) ?> &rarr; <?= date('M j', strtotime($ev['end'])) ?>
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!$isAdminEvt && $ev['status'] === 'Pending'): ?>
                                        <a href="<?= $detailUrl ?>" class="btn btn-sm btn-secondary" style="font-size: 0.76rem; padding: 2px 8px;">Edit</a>
                                    <?php endif; ?>
                                    <a href="<?= $viewUrl ?>" class="btn btn-sm btn-ghost" style="font-size: 0.76rem; padding: 2px 8px;">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="mobileFeedEmptyState" class="mobile-feed-empty d-none">
                <p class="mb-2">No scheduled events for this selected day.</p>
                <a href="add.php" id="mobileAddForDayBtn" class="btn btn-sm btn-primary">
                    <?= $user->isAdmin() ? '+ Schedule Event on this Day' : '+ Request Day Off on this Day' ?>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('mobileCarousel');
    if (!carousel) return;

    const dayButtons = carousel.querySelectorAll('.mobile-carousel-day');
    const feedCards = document.querySelectorAll('.mobile-feed-card');
    const feedTitle = document.getElementById('mobileFeedDateTitle');
    const feedCount = document.getElementById('mobileFeedCount');
    const emptyState = document.getElementById('mobileFeedEmptyState');
    const addForDayBtn = document.getElementById('mobileAddForDayBtn');

    function filterFeedByDate(dateStr, dayNum, dayName) {
        let visibleCount = 0;
        if (dateStr === 'all') {
            feedCards.forEach(c => {
                c.classList.remove('d-none');
                visibleCount++;
            });
            if (feedTitle) feedTitle.textContent = 'All Month Events';
            if (feedCount) feedCount.textContent = 'Showing ' + visibleCount + ' total events';
            if (emptyState) emptyState.classList.add('d-none');
        } else {
            feedCards.forEach(c => {
                if (c.getAttribute('data-date') === dateStr) {
                    c.classList.remove('d-none');
                    visibleCount++;
                } else {
                    c.classList.add('d-none');
                }
            });
            if (feedTitle) feedTitle.textContent = (dayName ? dayName + ', ' : '') + dateStr;
            if (feedCount) feedCount.textContent = visibleCount + (visibleCount === 1 ? ' event scheduled' : ' events scheduled');

            if (visibleCount === 0) {
                if (emptyState) {
                    emptyState.classList.remove('d-none');
                    if (addForDayBtn) addForDayBtn.href = 'add.php?date=' + dateStr;
                }
            } else {
                if (emptyState) emptyState.classList.add('d-none');
            }
        }
    }

    dayButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            dayButtons.forEach(b => b.classList.remove('is-active'));
            this.classList.add('is-active');
            const targetDate = this.getAttribute('data-date');
            const dayNum = this.getAttribute('data-daynum');
            const dayName = this.querySelector('.mobile-carousel-day__name')?.textContent || '';
            filterFeedByDate(targetDate, dayNum, dayName);
            this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        });
    });

    // Initialize selection: prefer today, else first day with events, else 'all'
    const todayBtn = carousel.querySelector('.mobile-carousel-day.is-today');
    const firstEventBtn = carousel.querySelector('.mobile-carousel-day.has-events');
    const defaultBtn = todayBtn || firstEventBtn || carousel.querySelector('.mobile-carousel-day--all');

    if (defaultBtn) {
        defaultBtn.classList.add('is-active');
        const targetDate = defaultBtn.getAttribute('data-date');
        const dayNum = defaultBtn.getAttribute('data-daynum');
        const dayName = defaultBtn.querySelector('.mobile-carousel-day__name')?.textContent || '';
        filterFeedByDate(targetDate, dayNum, dayName);
        setTimeout(() => {
            defaultBtn.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
        }, 100);
    }
});
</script>

<?php require_once('../views/footer.php'); ?>
