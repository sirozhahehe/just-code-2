<?php

namespace App\Service\Statistics;

use App\Exception\ServiceNotInitializedException;
use App\Utils\DateRange;
use App\Repository\SubscriptionRepository;

class TodaySubscriptionsCountDecorator implements SubscriptionsCountInterface
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
        if (date('Y-m-d') === $range->getEndDate()->format('Y-m-d')) {
            $this->addCurrentSubscribersCount($result);
        }
        return $result;
    }

    private function addCurrentSubscribersCount(&$subscribersCountPerDay): void
    {
        $subscribersCountPerDay[date('Y-m-d')] = array_sum($this->subscriptionRepository->findActiveSubscriptionsCount());
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