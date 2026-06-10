<?php
namespace Calendar;
class Month {
    public int $month;
    public int $year;
    private array $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    public array $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    /**
     * @param int $month month between 1 and 12
     * @param int $year the year in 4 digits
     */
    public function __construct(?int $month=null,  ?int $year=null) {
        if ($month === null || $month < 1 || $month > 12) {
            $month=intval(date("m"));
        }
        if($year===null){
            $year=intval(date("Y"));
        }
        $this->month = $month;
        $this->year = $year;
    }
    /** 
     * @return string the month and year in format "Month Year", e.g. "January 2024"
     */
    public function toString(): string {
        return $this->months[$this->month - 1] . " " . $this->year;
    }
    /** 
     * @return int the number of weeks in the month
     */
    public function getWeeks():int {
        $start = $this->getStartingDay();
        $end = (clone $start)->modify('+1 month -1 day');
        $startDayOfWeek = intval($start->format('N')); // 1=Monday, 7=Sunday
        $daysInMonth = intval($end->format('d'));
        $weeks = intval(ceil(($daysInMonth + $startDayOfWeek - 1) / 7));
        return $weeks;
    }
    /** 
     * @return DateTime the first day of the month
     */
    public function getStartingDay(): \Datetime {
        return new \DateTime("{$this->year}-{$this->month}-01");
    }
    /**
     * @param DateTime $date
     * @return bool whether the given date is within the current month
     */
    public function withinMonth(\Datetime $date): bool {
        return $this->getStartingDay()->format('Y-m') === $date->format('Y-m');
    }
    /**
     * @return Month the next month
     */
    public function nextMonth(): Month {
        $month = $this->month + 1;
        $year = $this->year;
        if($month > 12) {
            $month = 1;
            $year++;
        }
        return new Month($month, $year);
    }
    /**
     * @return Month the previous month
     */
    public function previousMonth(): Month {
        $month = $this->month - 1;
        $year = $this->year;
        if($month < 1) {
            $month = 12;
            $year--;
        }
        return new Month($month, $year);
    }
}