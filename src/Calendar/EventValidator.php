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
    public function validates(array $data): array
    {
        parent::validates($data);
        if ($this->validate('name', 'minLength', 3)) {
            // events.name is VARCHAR(255); longer titles used to crash the insert.
            $this->validate('name', 'maxLength', 255);
        }
        $this->validate('start_date', 'date');

        if (!empty($data['end_date'])) {
            $this->validate('end_date', 'date');
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                if ($data['end_date'] < $data['start_date']) {
                    $this->errors['end_date'] = 'End date must be after or equal to start date';
                }
            }
        }

        return $this->errors;
    }
}