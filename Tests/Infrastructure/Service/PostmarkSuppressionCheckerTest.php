<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Service;

use Xm\SymfonyBundle\Infrastructure\Service\PostmarkSuppressionChecker;
use Carbon\CarbonImmutable;
use Postmark\Models\PostmarkServer;
use Postmark\Models\Suppressions\PostmarkSuppression;
use Postmark\Models\Suppressions\PostmarkSuppressionList;
use Postmark\PostmarkClient;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class PostmarkSuppressionCheckerTest extends BaseTestCase
{
    public function testEmailSuppressed(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();
        $dateAdded = $faker->dateVoBetween()->format('c');
        $serverId = $faker->numberBetween();

        $suppression = \Mockery::mock(PostmarkSuppression::class);
        $suppression->shouldReceive('getSuppressionReason')
            ->twice()
            ->andReturn('HardBounce');
        $suppression->shouldReceive('getCreatedAt')
            ->once()
            ->andReturn($dateAdded);

        $suppressionsResponse = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([$suppression]);

        $server = \Mockery::mock(PostmarkServer::class);
        $server->shouldReceive('getID')
            ->once()
            ->andReturn($serverId);

        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->with('outbound', null, null, null, null, $email->toString())
            ->andReturn($suppressionsResponse);
        $client->shouldReceive('getServer')
            ->once()
            ->andReturn($server);

        $checker = new PostmarkSuppressionChecker('fake-api-key');

        // Use reflection to inject the mocked client
        $reflection = new \ReflectionClass($checker);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($checker, $client);

        $result = $checker->check($email);

        $this->assertTrue($result['suppressed']);
        $this->assertSame('HardBounce', $result['reason']);
        $this->assertSame('Hard Bounce - Email address is invalid or does not exist', $result['reasonHuman']);
        $this->assertInstanceOf(CarbonImmutable::class, $result['dateAdded']);
        $this->assertSame($dateAdded, $result['dateAdded']->format('c'));
        $this->assertStringContainsString((string) $serverId, $result['postmarkUrl']);
        $this->assertStringContainsString(urlencode($email->toString()), $result['postmarkUrl']);
    }

    public function testEmailNotSuppressed(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();

        $suppressionsResponse = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([]);

        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->with('outbound', null, null, null, null, $email->toString())
            ->andReturn($suppressionsResponse);

        $checker = new PostmarkSuppressionChecker('fake-api-key');

        // Use reflection to inject the mocked client
        $reflection = new \ReflectionClass($checker);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($checker, $client);

        $result = $checker->check($email);

        $this->assertFalse($result['suppressed']);
        $this->assertNull($result['reason']);
        $this->assertNull($result['reasonHuman']);
        $this->assertNull($result['dateAdded']);
        $this->assertNull($result['postmarkUrl']);
    }

    public function testSpamComplaintReason(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();
        $dateAdded = $faker->dateVoBetween()->format('c');
        $serverId = $faker->numberBetween();

        $suppression = \Mockery::mock(PostmarkSuppression::class);
        $suppression->shouldReceive('getSuppressionReason')
            ->twice()
            ->andReturn('SpamComplaint');
        $suppression->shouldReceive('getCreatedAt')
            ->once()
            ->andReturn($dateAdded);

        $suppressionsResponse = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([$suppression]);

        $server = \Mockery::mock(PostmarkServer::class);
        $server->shouldReceive('getID')
            ->once()
            ->andReturn($serverId);

        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->andReturn($suppressionsResponse);
        $client->shouldReceive('getServer')
            ->once()
            ->andReturn($server);

        $checker = new PostmarkSuppressionChecker('fake-api-key');

        $reflection = new \ReflectionClass($checker);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($checker, $client);

        $result = $checker->check($email);

        $this->assertTrue($result['suppressed']);
        $this->assertSame('SpamComplaint', $result['reason']);
        $this->assertSame('Spam Complaint - Recipient marked email as spam', $result['reasonHuman']);
        $this->assertInstanceOf(CarbonImmutable::class, $result['dateAdded']);
        $this->assertSame($dateAdded, $result['dateAdded']->format('c'));
        $this->assertStringContainsString((string) $serverId, $result['postmarkUrl']);
        $this->assertStringContainsString(urlencode($email->toString()), $result['postmarkUrl']);
    }

    public function testManualSuppressionReason(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();
        $dateAdded = $faker->dateVoBetween()->format('c');
        $serverId = $faker->numberBetween();

        $suppression = \Mockery::mock(PostmarkSuppression::class);
        $suppression->shouldReceive('getSuppressionReason')
            ->twice()
            ->andReturn('ManualSuppression');
        $suppression->shouldReceive('getCreatedAt')
            ->once()
            ->andReturn($dateAdded);

        $suppressionsResponse = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([$suppression]);

        $server = \Mockery::mock(PostmarkServer::class);
        $server->shouldReceive('getID')
            ->once()
            ->andReturn($serverId);

        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->andReturn($suppressionsResponse);
        $client->shouldReceive('getServer')
            ->once()
            ->andReturn($server);

        $checker = new PostmarkSuppressionChecker('fake-api-key');

        $reflection = new \ReflectionClass($checker);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($checker, $client);

        $result = $checker->check($email);

        $this->assertTrue($result['suppressed']);
        $this->assertSame('ManualSuppression', $result['reason']);
        $this->assertSame('Manual Suppression - Manually added to suppression list', $result['reasonHuman']);
        $this->assertInstanceOf(CarbonImmutable::class, $result['dateAdded']);
        $this->assertSame($dateAdded, $result['dateAdded']->format('c'));
        $this->assertStringContainsString((string) $serverId, $result['postmarkUrl']);
        $this->assertStringContainsString(urlencode($email->toString()), $result['postmarkUrl']);
    }

    public function testUnknownSuppressionReason(): void
    {
        $faker = $this->faker();
        $email = $faker->emailVo();
        $dateAdded = $faker->dateVoBetween()->format('c');
        $serverId = $faker->numberBetween();
        $unknownReason = 'UnknownReason';

        $suppression = \Mockery::mock(PostmarkSuppression::class);
        $suppression->shouldReceive('getSuppressionReason')
            ->twice()
            ->andReturn($unknownReason);
        $suppression->shouldReceive('getCreatedAt')
            ->once()
            ->andReturn($dateAdded);

        $suppressionsResponse = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([$suppression]);

        $server = \Mockery::mock(PostmarkServer::class);
        $server->shouldReceive('getID')
            ->once()
            ->andReturn($serverId);

        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->andReturn($suppressionsResponse);
        $client->shouldReceive('getServer')
            ->once()
            ->andReturn($server);

        $checker = new PostmarkSuppressionChecker('fake-api-key');

        $reflection = new \ReflectionClass($checker);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($checker, $client);

        $result = $checker->check($email);

        $this->assertTrue($result['suppressed']);
        $this->assertSame($unknownReason, $result['reason']);
        $this->assertSame($unknownReason, $result['reasonHuman']);
        $this->assertInstanceOf(CarbonImmutable::class, $result['dateAdded']);
        $this->assertSame($dateAdded, $result['dateAdded']->format('c'));
        $this->assertStringContainsString((string) $serverId, $result['postmarkUrl']);
        $this->assertStringContainsString(urlencode($email->toString()), $result['postmarkUrl']);
    }

    public function testServerIdCaching(): void
    {
        $faker = $this->faker();
        $email1 = Email::fromString($faker->email());
        $email2 = Email::fromString($faker->email());
        $dateAdded = '2025-11-17T10:30:00Z';
        $serverId = $faker->numberBetween();

        // First suppression
        $suppression1 = \Mockery::mock(PostmarkSuppression::class);
        $suppression1->shouldReceive('getSuppressionReason')
            ->twice()
            ->andReturn('HardBounce');
        $suppression1->shouldReceive('getCreatedAt')
            ->once()
            ->andReturn($dateAdded);

        $suppressionsResponse1 = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse1->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([$suppression1]);

        // Second suppression
        $suppression2 = \Mockery::mock(PostmarkSuppression::class);
        $suppression2->shouldReceive('getSuppressionReason')
            ->twice()
            ->andReturn('SpamComplaint');
        $suppression2->shouldReceive('getCreatedAt')
            ->once()
            ->andReturn($dateAdded);

        $suppressionsResponse2 = \Mockery::mock(PostmarkSuppressionList::class);
        $suppressionsResponse2->shouldReceive('getSuppressions')
            ->once()
            ->andReturn([$suppression2]);

        $server = \Mockery::mock(PostmarkServer::class);
        $server->shouldReceive('getID')
            ->once() // Only called once due to caching
            ->andReturn($serverId);

        $client = \Mockery::mock(PostmarkClient::class);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->with('outbound', null, null, null, null, $email1->toString())
            ->andReturn($suppressionsResponse1);
        $client->shouldReceive('getSuppressions')
            ->once()
            ->with('outbound', null, null, null, null, $email2->toString())
            ->andReturn($suppressionsResponse2);
        $client->shouldReceive('getServer')
            ->once() // Only called once due to caching
            ->andReturn($server);

        $checker = new PostmarkSuppressionChecker('fake-api-key');

        $reflection = new \ReflectionClass($checker);
        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setValue($checker, $client);

        // First call - should fetch server ID
        $result1 = $checker->check($email1);
        $this->assertStringContainsString((string) $serverId, $result1['postmarkUrl']);

        // Second call - should use cached server ID
        $result2 = $checker->check($email2);
        $this->assertStringContainsString((string) $serverId, $result2['postmarkUrl']);
    }
}
