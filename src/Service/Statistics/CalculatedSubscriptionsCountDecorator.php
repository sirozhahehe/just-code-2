<?php

namespace App\Service\Statistics;

use App\Exception\ServiceNotInitializedException;
use App\Utils\DateRange;
use App\Repository\SubscriptionRepository;

class CalculatedSubscriptionsCountDecorator implements SubscriptionsCountInterface
{
    private SubscriptionsCountInterface $subscriptionsService;

    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
    ) {}

    public function setSubscriptionsCountService(SubscriptionsCountInterface $subscriptionsService)
    {
        $this->subscriptionsService = $subscriptionsService;
    }

    public function getSubscriptionsCountInRange(DateRange $range): array
    {
        $this->assertServiceSet();

        $result = $this->subscriptionsService->getSubscriptionsCountInRange($range);
        $start  = $range->getStartDate();
        $end    = $range->getEndDate();
        $startAsString = $range->getStartDate()->format('Y-m-d');
        $hasStartValue = (bool) $result[$start->format('Y-m-d')];

        if (!$hasStartValue) {
            $this->addSubscribersCountAtStart($start, $result);
        }

        $new      = $this->subscriptionRepository->findNewSubscriptionsInRange($start, $end);
        $canceled = $this->subscriptionRepository->findCanceledSubscriptionsInRange($start, $end);

        foreach ($result as $dayString => $value) {
            if (($startAsString === $dayString && $hasStartValue) || ($startAsString !== $dayString && $value)) {
                continue;
            }
            if (!$value && $startAsString !== $dayString) {
                $result[$dayString] = $result[$this->getPreviousDayAsString($dayString)];
            }

            $result[$dayString] = $result[$dayString] + ($new[$dayString] ?? 0) - ($canceled[$dayString] ?? 0);
        }

        return $result;
    }

    private function addSubscribersCountAtStart(\DateTimeInterface $start, &$subscribersCountPerDay)
    {
        $subscribersCountPerDay[$start->format('Y-m-d')] = $this->subscriptionRepository->findSubscriptionsCountAt($start);
    }

    private function getPreviousDayAsString(string $dayString): string
    {
        return (new \DateTime($dayString))->modify('-1 day')->format('Y-m-d');
    }

    private function assertServiceSet(): void
    {
        try {
            $this->subscriptionsService;
        } catch (\Throwable) {
            throw new ServiceNotInitializedException(self::class . ' can not be used without wrapped service.');
        }
    }
}