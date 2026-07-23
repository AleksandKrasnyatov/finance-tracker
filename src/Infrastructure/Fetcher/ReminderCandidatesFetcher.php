<?php

declare(strict_types=1);

namespace App\Infrastructure\Fetcher;

use App\Application\Fetcher\ReminderCandidate;
use App\Application\Fetcher\ReminderCandidatesFetcherInterface;
use App\Domain\Enum\Locale;
use App\Domain\ValueObject\Id;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ReminderCandidatesFetcher implements ReminderCandidatesFetcherInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     * @return list<ReminderCandidate>
     */
    public function fetch(DateTimeImmutable $now): array
    {
        $nowUtc = $now->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        $rows = $this->entityManager->getConnection()->fetchAllAssociative(
            <<<'SQL'
                WITH candidate AS (
                    SELECT
                        u.id,
                        u.locale,
                        u.reminder_timezone AS timezone,
                        u.reminder_reminder_time AS reminder_time,
                        u.reminder_last_reminder_on AS last_reminder_on,
                        (CAST(:now AS TIMESTAMP) AT TIME ZONE 'UTC')
                            AT TIME ZONE u.reminder_timezone AS local_now
                    FROM users u
                    WHERE u.reminder_reminders_enabled = TRUE
                )
                SELECT c.id, c.locale
                FROM candidate c
                WHERE TO_CHAR(c.local_now, 'HH24:MI') = c.reminder_time
                  AND (
                      c.last_reminder_on IS NULL
                      OR c.last_reminder_on <> CAST(c.local_now AS DATE)
                  )
                  AND NOT EXISTS (
                      SELECT 1
                      FROM transactions t
                      INNER JOIN user_accounts ua ON ua.account_id = t.account_id
                      WHERE ua.user_id = c.id
                        AND t.created_at >= (
                            (DATE_TRUNC('day', c.local_now) AT TIME ZONE c.timezone)
                                AT TIME ZONE 'UTC'
                        )
                        AND t.created_at < (
                            ((DATE_TRUNC('day', c.local_now) + INTERVAL '1 day')
                                AT TIME ZONE c.timezone)
                                AT TIME ZONE 'UTC'
                        )
                  )
                SQL,
            ['now' => $nowUtc],
        );

        $candidates = [];
        foreach ($rows as $row) {
            $candidates[] = new ReminderCandidate(
                new Id((string) $row['id']),
                Locale::from((string) $row['locale']),
            );
        }

        return $candidates;
    }
}
