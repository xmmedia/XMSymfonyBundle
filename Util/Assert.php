<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class Assert extends \Webmozart\Assert\Assert
{
    public static function compromisedPassword(
        string $password,
        HttpClient $httpClient = null
    ): void {
        $endpoint = 'https://api.pwnedpasswords.com/range/%s';
        if (null === $httpClient) {
            $httpClient = HttpClient::create();
        }

        $hash = strtoupper(sha1($password));
        $hashPrefix = substr($hash, 0, 5);
        $url = sprintf($endpoint, $hashPrefix);

        try {
            $result = $httpClient->request('GET', $url)->getContent();
        } catch (ExceptionInterface $e) {
            throw new \InvalidArgumentException(sprintf('Unable to check for compromised password. HTTP error: %s', $e->getMessage()), 0, $e);
        }

        foreach (explode("\r\n", $result) as $line) {
            list($hashSuffix, $count) = explode(':', $line);

            // reject if in more than 3 breaches
            if ($hashPrefix.$hashSuffix === $hash && 3 <= (int) $count) {
                throw new \InvalidArgumentException('The entered password has been compromised.');
            }
        }
    }
}
