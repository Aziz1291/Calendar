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
}