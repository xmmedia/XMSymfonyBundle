<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use Webmozart\Assert\Assert;

class Cloudflare
{
    private string $cloudflareZone;
    private string $cloudflareApiToken;
    private Guzzle $adaptor;

    public function __construct(
        string $cloudflareZone,
        string $cloudflareApiToken
    ) {
        $this->cloudflareZone = $cloudflareZone;
        $this->cloudflareApiToken = $cloudflareApiToken;
    }

    public function addRecord(
        string $type,
        string $name,
        string $content,
        int $ttl = 0,
        bool $proxied = true,
        string $priority = '',
        array $data = []
    ): bool {
        $this->connect();

        return (new DNS($this->adaptor))->addRecord(
            $this->cloudflareZone,
            $type,
            $name,
            $content,
            $ttl,
            $proxied,
            $priority,
            $data,
        );
    }

    public function updateRecord(
        string $type,
        string $existingName,
        array $details
    ): bool {
        $this->connect();

        $dns = new DNS($this->adaptor);

        $recordId = $dns->getRecordID($this->cloudflareZone, $type, $existingName);
        if (!$recordId) {
            throw new \InvalidArgumentException('Cannot find record');
        }

        $recordData = (array) $dns->getRecordDetails($this->cloudflareZone, $recordId);

        $newDetails = [
                'type'    => $recordData['type'],
                'name'    => $recordData['name'],
                'content' => $recordData['content'],
                'ttl'     => $recordData['ttl'],
            ] + $details;

        return $dns->updateRecordDetails($this->cloudflareZone, $recordId, $newDetails)->success;
    }

    public function clearCache(): bool
    {
        $this->connect();

        return (new Zones($this->adaptor))->cachePurgeEverything($this->cloudflareZone);
    }

    private function connect(): void
    {
        if (isset($this->adaptor)) {
            return;
        }

        Assert::notEmpty($this->cloudflareZone, 'The Cloudflare zone is not set.');
        Assert::notEmpty($this->cloudflareApiToken, 'The Cloudflare API Token is not set.');

        $key = new APIToken($this->cloudflareApiToken);
        $this->adaptor = new Guzzle($key);
    }
}
