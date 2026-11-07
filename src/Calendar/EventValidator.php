<?php
namespace Calendar;

use App\Validator;

class EventValidator extends Validator
{
    /**
     * Validates event data for date range support
     * @param array $data
     * @return array|bool
     */
    public function validates(array $data, bool $allowPast = true): array
    {
        parent::validates($data);
        if ($this->validate('name', 'minLength', 3)) {
            // events.name is VARCHAR(255); longer titles used to crash the insert.
            $this->validate('name', 'maxLength', 255);
        }
        if (!empty($data['description'])) {
            $this->validate('description', 'maxLength', 2000);
        }
        $datesOk = $this->validate('start_date', 'date');

        if (!empty($data['end_date'])) {
            $datesOk = $this->validate('end_date', 'date') && $datesOk;
            if ($datesOk && $data['end_date'] < $data['start_date']) {
                $this->errors['end_date'] = 'End date must be on or after the start date.';
                $datesOk = false;
            }
        }

        // Optional times; empty means the whole day (00:00 to 23:59).
        $timesOk = true;
        foreach (['start_time', 'end_time'] as $field) {
            if (!empty($data[$field])) {
                $timesOk = $this->validate($field, 'time') && $timesOk;
            }
        }

        if ($datesOk && $timesOk) {
            $start = $data['start_date'] . ' ' . (!empty($data['start_time']) ? $data['start_time'] : '00:00');
            $end = (!empty($data['end_date']) ? $data['end_date'] : $data['start_date'])
                . ' ' . (!empty($data['end_time']) ? $data['end_time'] : '23:59');
            if ($end <= $start) {
                $this->errors['end_time'] = 'End time must be after the start time.';
            }
            if (!$allowPast && $data['start_date'] < date('Y-m-d')) {
                $this->errors['start_date'] = 'Requests cannot start in the past.';
            }
        }

        return $this->errors;
    }
}