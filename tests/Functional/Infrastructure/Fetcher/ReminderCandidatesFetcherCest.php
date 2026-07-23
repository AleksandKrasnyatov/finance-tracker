<?php

declare(strict_types=1);

namespace Test\Functional\Infrastructure\Fetcher;

use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Domain\Entity\User;
use DateTimeImmutable;
use DateTimeZone;
use Test\Support\Fixture\ReminderCandidatesFixture;
use Test\Support\FunctionalTester;

final class ReminderCandidatesFetcherCest
{
    public function givenMixedUsersAndTransactionsWhenFetchThenOnlyEligibleCandidatesReturned(
        FunctionalTester $I,
    ): void {
        $I->loadFixtures(ReminderCandidatesFixture::class);

        /** @var ReminderCandidatesFetcherInterface $fetcher */
        $fetcher = $I->grabService(ReminderCandidatesFetcherInterface::class);
        $candidates = $fetcher->fetch(
            new DateTimeImmutable(ReminderCandidatesFixture::NOW, new DateTimeZone('UTC')),
        );

        $candidateIds = array_map(
            static fn ($candidate): string => $candidate->userId->value,
            $candidates,
        );

        $expectedIds = array_map(
            static function (int $telegramId) use ($I): string {
                return $I->grabEntityFromRepository(User::class, ['telegramId' => $telegramId])->id->value;
            },
            ReminderCandidatesFixture::EXPECTED_TELEGRAM_IDS,
        );

        $I->assertEqualsCanonicalizing($expectedIds, $candidateIds);
    }
}
