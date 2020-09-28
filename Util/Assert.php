<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use ZxcvbnPhp\Zxcvbn;

class Assert extends \Webmozart\Assert\Assert
{
    public static function passwordComplexity(
        string $password,
        array $userData
    ): void {
        $score = (new Zxcvbn())->passwordStrength($password, array_values($userData))['score'];

        if ($score <= 2) {
            throw new \InvalidArgumentException('The password complexity is '.$score.' out of 4 (minimum: 2).');
        }
    }

    public static function compromisedPassword(
        string $password,
        HttpClientInterface $httpClient = null
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
            try {
                [$hashSuffix, $count] = explode(':', $line);
            } catch (\Throwable $e) {
                throw new \InvalidArgumentException('Unable to check for compromised password. Bad response.');
            }

            // reject if in more than 3 breaches
            if ($hashPrefix.$hashSuffix === $hash && 3 <= (int) $count) {
                throw new \InvalidArgumentException('The entered password has been compromised.');
            }
        }
    }
}
