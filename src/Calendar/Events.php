<?php
namespace Calendar;

require_once 'Event.php';

class Events
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get events between two dates
     * @return array
     * @param \Datetime $start
     * @param \Datetime $end
     */
    public function getEventsBetween(\Datetime $start, \Datetime $end, ?int $userId = null): array
    {
        $sql = "SELECT * FROM events WHERE start BETWEEN '{$start->format('Y-m-d 00:00:00')}' AND '{$end->format('Y-m-d 23:59:59')}'";
        if ($userId) {
            $sql .= " AND (user_id = " . intval($userId) . " OR admin_event = 1)";
        }
        $sql .= " ORDER BY start ASC";
        $statement = $this->pdo->query($sql);
        $result = $statement->fetchAll();
        return $result;
    }

    /**
     * Get events between two dates grouped by day
     * Handles multi-day events by showing them on each day they span
     * @return array
     * @param \Datetime $start
     * @param \Datetime $end
     */
    public function getEventsBetweenByDay(\Datetime $start, \Datetime $end, ?int $userId = null): array
    {
        $events = $this->getEventsBetween($start, $end, $userId);
        $days = [];
        foreach ($events as $event) {
            $eventStart = new \DateTime(explode(' ', $event['start'])[0]);
            $eventEnd = new \DateTime(explode(' ', $event['end'])[0]);

            $current = clone $eventStart;
            while ($current <= $eventEnd) {
                $dateKey = $current->format('Y-m-d');
                if ($current >= $start && $current <= $end) {
                    if (!isset($days[$dateKey])) {
                        $days[$dateKey] = [$event];
                    } else {
                        $found = false;
                        foreach ($days[$dateKey] as $existingEvent) {
                            if ($existingEvent['id'] === $event['id']) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $days[$dateKey][] = $event;
                        }
                    }
                }
                $current->modify('+1 day');
            }
        }
        return $days;
    }

    public function delete(int $id)
    {
        $req = $this->pdo->prepare("DELETE FROM events WHERE id = ?");
        $req->execute([$id]);
    }

    /**
     * Find an event by its ID
     * @return Event|null
     * @param int $id
     * @throws \Exception if event not found
     */
    public function find(int $id): ?Event
    {
        $statement = $this->pdo->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
        $statement->execute([$id]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        $result = $statement->fetch();
        if (!$result) {
            throw new \Exception("Event not found");
        }
        return $result;
    }

    public function getAll()
    {
        $statement = $this->pdo->query("SELECT * FROM events ORDER BY start DESC");
        $statement->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        return $statement->fetchAll();
    }

    public function getAllforUser($id)
    {
        $statement = "SELECT * FROM events WHERE user_id=? OR admin_event=1 ORDER BY start DESC";
        $req = $this->pdo->prepare($statement);
        $req->execute([$id]);
        $req->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        return $req->fetchAll();
    }

    /**
     * Retrieve exclusively an employee's own leave submissions with optional status & search filtering
     */
    public function getEmployeeSubmissions(int $userId, ?string $status = null, ?string $search = null): array
    {
        $sql = "SELECT * FROM events WHERE user_id = :userId";
        $params = [':userId' => $userId];

        if ($status && in_array($status, ['Pending', 'Approved', 'Rejected'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND (name LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY start DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        return $stmt->fetchAll();
    }

    public function hydrate(Event $event, array $data)
    {
        $event->setName(trim($data['name']));
        $event->setDescription(trim($data['description'] ?? ''));

        $startDate = $data['start_date'];
        $endDate = !empty($data['end_date']) ? $data['end_date'] : $startDate;

        $startTime = !empty($data['start_time']) ? $data['start_time'] : '00:00';
        $endTime = !empty($data['end_time']) ? $data['end_time'] : '23:59';

        $event->setStart(\Datetime::createFromFormat('Y-m-d H:i', $startDate . ' ' . $startTime)->format('Y-m-d H:i:s'));
        $event->setEnd(\Datetime::createFromFormat('Y-m-d H:i', $endDate . ' ' . $endTime)->format('Y-m-d H:i:s'));

        return $event;
    }

    /**
     * Create an event
     * @param Event $event
     */
    public function create(Event $event)
    {
        $req = $this->pdo->prepare("INSERT INTO events (name, description, start, end, user_id, admin_event, status, rejectionReason) VALUES (?,?,?,?,?,?,?,?)");
        $req->execute([
            $event->getName(),
            $event->getDescription(),
            $event->getStart()->format('Y-m-d H:i:s'),
            $event->getEnd()->format('Y-m-d H:i:s'),
            $event->getUserId(),
            $event->getAdminEvent() ?: 0,
            $event->getStatus() ?: 'Pending',
            $event->getRejectionReason() ?: '',
        ]);
    }

    /**
     * Update an event
     * @param Event $event
     */
    public function update(Event $event)
    {
        $req = $this->pdo->prepare("UPDATE events SET name = ?, description = ?, start = ?, end = ?, admin_event = ?, status = ?, rejectionReason = ? WHERE id = ?");
        $req->execute([
            $event->getName(),
            $event->getDescription(),
            $event->getStart()->format('Y-m-d H:i:s'),
            $event->getEnd()->format('Y-m-d H:i:s'),
            $event->getAdminEvent() ?: 0,
            $event->getStatus() ?: 'Pending',
            $event->getRejectionReason() ?: '',
            $event->getId()
        ]);
    }

    public function filterByStatus($status)
    {
        $statement = $this->pdo->prepare("SELECT * FROM events WHERE status = ? ORDER BY start DESC");
        $statement->execute([$status]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        return $statement->fetchAll();
    }

    public function countByUserId($userId)
    {
        $statement = $this->pdo->prepare("SELECT COUNT(*) as count FROM events WHERE user_id = ?");
        $statement->execute([$userId]);
        $result = $statement->fetch();
        return (int)$result['count'];
    }

    public function filterBySearch($search)
    {
        $statement = $this->pdo->prepare("SELECT events.* FROM events LEFT JOIN users ON events.user_id = users.id WHERE users.username LIKE ? OR events.name LIKE ? ORDER BY events.start DESC");
        $param = '%' . $search . '%';
        $statement->execute([$param, $param]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        return $statement->fetchAll();
    }

    public function filterBySearchAndStatus($search, $status)
    {
        $statement = $this->pdo->prepare("SELECT events.* FROM events LEFT JOIN users ON events.user_id = users.id WHERE (users.username LIKE ? OR events.name LIKE ?) AND events.status = ? ORDER BY events.start DESC");
        $param = '%' . $search . '%';
        $statement->execute([$param, $param, $status]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, \Calendar\Event::class);
        return $statement->fetchAll();
    }
}
