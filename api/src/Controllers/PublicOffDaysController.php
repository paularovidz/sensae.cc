<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\OffDay;
use App\Services\ICSGeneratorService;

class PublicOffDaysController
{
    /**
     * GET /public/calendar/off-days.ics - Export off days as ICS calendar
     */
    public function calendar(): void
    {
        // Get all off days (future and past for calendar sync)
        $offDays = OffDay::getAll();

        // Generate ICS content
        $icsContent = ICSGeneratorService::generateOffDaysCalendar($offDays);

        // Send ICS headers
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="sensea-off-days.ics"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        echo $icsContent;
        exit;
    }
}
