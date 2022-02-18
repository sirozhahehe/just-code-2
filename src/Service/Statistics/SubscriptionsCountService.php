<?php

namespace App\Service\Statistics;

use App\Utils\DateRange;
use App\Entity\Statistics\SubscriptionStatistics;
use App\Repository\SubscriptionStatisticsRepository;

class SubscriptionsCountService implements SubscriptionsCountInterface
{
    public function __construct(
        private SubscriptionStatisticsRepository $statisticsRepository,
    ) {}

    public function getSubscriptionsCountInRange(DateRange $range): array
    {
        $subscriptionStatistics = $this->getSubscriptionsStatistics($range);

        $result = [];

        foreach ($range->getDatePeriod('P1D') as $day) {
            $dayString = $day->format('Y-m-d');

            if (!isset($subscriptionStatistics[$dayString])) {
                $result[$dayString] = 0;
                continue;
            }
            $result[$dayString] = $subscriptionStatistics[$dayString]->getCurrentSubscribersCount();
        }

        return $result;
    }

    /**
     * @return SubscriptionStatistics[]
     */
    private function getSubscriptionsStatistics(DateRange $range): array
    {
        $subscriptionStatistics = $this->statisticsRepository->findStatisticsInRange(
            $range->getStartDate(),
            $range->getEndDate(),
        );

        $formattedSubscriptionStatistics = [];
        foreach ($subscriptionStatistics as $statistic) {
            $formattedSubscriptionStatistics[$statistic->getStatisticsDay()->format('Y-m-d')] = $statistic;
        }
        unset($subscriptionStatistics);
        return $formattedSubscriptionStatistics;
    }

}