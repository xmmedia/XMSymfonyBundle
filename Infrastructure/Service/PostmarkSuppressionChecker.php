<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Carbon\CarbonImmutable;
use JetBrains\PhpStorm\ArrayShape;
use Postmark\Models\Suppressions\PostmarkSuppression;
use Postmark\PostmarkClient;
use Xm\SymfonyBundle\Model\Email;

final class PostmarkSuppressionChecker implements EmailSuppressionCheckerInterface
{
    private ?PostmarkClient $client = null;
    private ?string $serverId = null;

    public function __construct(private readonly string $postmarkApiKey)
    {
    }

    /**
     * Check if an email address is in Postmark's suppression list.
     */
    #[ArrayShape([
        'suppressed'  => 'bool',
        'reason'      => 'null|string',
        'reasonHuman' => 'null|string',
        'dateAdded'   => CarbonImmutable::class.'|null',
        'postmarkUrl' => 'string',
    ])]
    public function check(Email $email): array
    {
        $this->createClient();

        $result = $this->client->getSuppressions(
            messageStream: 'outbound',
            emailAddress: $email->toString(),
        );

        $suppressions = $result->getSuppressions();

        // If suppressions array is empty, the email is not suppressed
        if ([] === $suppressions) {
            return [
                'suppressed'  => false,
                'reason'      => null,
                'reasonHuman' => null,
                'dateAdded'   => null,
                'postmarkUrl' => null,
            ];
        }

        /** @var PostmarkSuppression $suppression */
        $suppression = $suppressions[0];

        $postmarkUrl = \sprintf(
            'https://account.postmarkapp.com/servers/%s/streams/outbound/suppressions?email_address=%s',
            $this->getServerId(),
            urlencode($email->toString()),
        );

        return [
            'suppressed'  => true,
            'reason'      => $suppression->getSuppressionReason(),
            'reasonHuman' => $this->formatReason($suppression),
            'dateAdded'   => CarbonImmutable::createFromFormat('c', $suppression->getCreatedAt()),
            'postmarkUrl' => $postmarkUrl,
        ];
    }

    private function getServerId(): string
    {
        if (null !== $this->serverId) {
            return $this->serverId;
        }

        $this->serverId = (string) $this->client->getServer()->getID();

        return $this->serverId;
    }

    private function formatReason(PostmarkSuppression $suppression): string
    {
        $reason = $suppression->getSuppressionReason();

        $reasons = [
            'HardBounce'        => 'Hard Bounce - Email address is invalid or does not exist',
            'SpamComplaint'     => 'Spam Complaint - Recipient marked email as spam',
            'ManualSuppression' => 'Manual Suppression - Manually added to suppression list',
        ];

        return $reasons[$reason] ?? $reason;
    }

    private function createClient(): void
    {
        if (null === $this->client) {
            $this->client = new PostmarkClient($this->postmarkApiKey);
        }
    }
}
