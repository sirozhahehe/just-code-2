<?php

namespace App\Service\Statistics;

use App\Utils\DateRange;

interface SubscriptionsCountInterface
{
    public function getSubscriptionsCountInRange(DateRange $range): array;
}