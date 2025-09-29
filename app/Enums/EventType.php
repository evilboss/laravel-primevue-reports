<?php

namespace App\Enums;

enum EventType: string
{
    case JOB_TYPE_ZIP_COMPLETED = 'job_type_zip_completed';
    case APPOINTMENT_DATETIME_SELECTED = 'appointment_datetime_selected';
    case CUSTOMER_SELECTION = 'customer_selection';
    case TERMS_OF_SERVICE_LOADED = 'terms_of_service_loaded';
    case APPOINTMENT_CONFIRMED = 'appointment_confirmed';

    public static function getStepOrder(): array
    {
        return [
            self::JOB_TYPE_ZIP_COMPLETED,
            self::APPOINTMENT_DATETIME_SELECTED,
            self::CUSTOMER_SELECTION,
            self::TERMS_OF_SERVICE_LOADED,
            self::APPOINTMENT_CONFIRMED,
        ];
    }

    public function getStepNumber(): int
    {
        $steps = self::getStepOrder();
        return array_search($this, $steps) + 1;
    }

    public function getDisplayName(): string
    {
        return match($this) {
            self::JOB_TYPE_ZIP_COMPLETED => 'Job Type & Zip Completed',
            self::APPOINTMENT_DATETIME_SELECTED => 'Appointment Date/Time Selected',
            self::CUSTOMER_SELECTION => 'New / Repeat Customer Selection',
            self::TERMS_OF_SERVICE_LOADED => 'Terms of Service Loaded',
            self::APPOINTMENT_CONFIRMED => 'Appointment Confirmed',
        };
    }
}