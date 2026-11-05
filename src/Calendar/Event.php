<?php
namespace Calendar;

class Event
{
    private $id;
    private $user_id;
    private $name;
    private $description;
    private $start;
    private $end;
    private $status;
    private $admin_event;
    private $rejectionReason;
    private $created_at;

    public function getRejectionReason()
    {
        return $this->rejectionReason;
    }
    public function setRejectionReason(string $rejectionReason)
    {
        $this->rejectionReason = $rejectionReason;
    }
    public function getAdminEvent()
    {
        return $this->admin_event;
    }
    public function setAdminEvent(int $admin_event)
    {
        $this->admin_event = $admin_event;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getStart(): \Datetime
    {
        return new \Datetime($this->start);
    }
    public function getEnd(): \Datetime
    {
        return new \Datetime($this->end);
    }
    public function getDescription(): string
    {
        return $this->description ?? '';
    }
    public function setName(string $name)
    {
        $this->name = $name;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    public function setStart(string $start)
    {
        $this->start = $start;
    }
    public function setEnd(string $end)
    {
        $this->end = $end;
    }

    public function getCreatedAt(): \Datetime
    {
        return !empty($this->created_at) ? new \Datetime($this->created_at) : $this->getStart();
    }

    public function getDurationDays(): int
    {
        $startDate = new \DateTime($this->getStart()->format('Y-m-d'));
        $endDate = new \DateTime($this->getEnd()->format('Y-m-d'));
        $diff = $startDate->diff($endDate);
        return (int)$diff->days + 1;
    }

    public function getFormattedDuration(): string
    {
        $startDate = $this->getStart()->format('Y-m-d');
        $endDate = $this->getEnd()->format('Y-m-d');
        $startTime = $this->getStart()->format('H:i');
        $endTime = $this->getEnd()->format('H:i');
        $isFullDay = ($startTime === '00:00' && $endTime === '23:59');

        if ($startDate === $endDate) {
            if ($isFullDay) {
                return '1 Day';
            } else {
                $hours = round(($this->getEnd()->getTimestamp() - $this->getStart()->getTimestamp()) / 3600, 1);
                return $hours . ' Hours';
            }
        } else {
            $days = $this->getDurationDays();
            return $days . ' Days';
        }
    }

    public function getStatusToneClass(): string
    {
        if ((int)$this->admin_event === 1) {
            return 'tone-sea';
        }
        switch ($this->status) {
            case 'Approved':
                return 'tone-olive';
            case 'Rejected':
                return 'tone-rose';
            case 'Pending':
            default:
                return 'tone-clay';
        }
    }
}
