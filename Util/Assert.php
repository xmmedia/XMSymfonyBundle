<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Validator\Constraints\UrlValidator;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Assert extends \Webmozart\Assert\Assert
{
    public static function passwordComplexity(
        string $password,
        array $userData,
        ?int $minimum = null,
        ?PasswordStrengthInterface $passwordStrength = null,
    ): void {
        if (null === $minimum) {
            $minimum = 2;
        }
        if (null === $passwordStrength) {
            $passwordStrength = new PasswordStrength();
        }

        $score = $passwordStrength($password, $userData)['score'];

        if ($score <= $minimum) {
            throw new \InvalidArgumentException(sprintf('The password complexity is %d out of 4 (minimum: %d).', $score, $minimum));
        }
    }

    public static function compromisedPassword(
        string $password,
        ?HttpClientInterface $httpClient = null,
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
            if (!str_contains($line, ':')) {
                throw new \InvalidArgumentException('Unable to check for compromised password. Bad response.');
            }

            [$hashSuffix, $count] = explode(':', $line);

            // reject if in more than 3 breaches
            if ($hashPrefix.$hashSuffix === $hash && 3 <= (int) $count) {
                throw new \InvalidArgumentException('The entered password has been compromised.');
            }
        }
    }

    public static function url(
        $value,
        $protocols = ['http', 'https'],
        string $message = '',
    ): void {
        self::notEmpty($protocols, 'At least 1 protocol is required. Got 0.');

        self::regex(
            $value,
            sprintf(UrlValidator::PATTERN, implode('|', $protocols)),
            $message ?: 'Expected a value to be a URL with protocol(s) '.implode(',', $protocols).'. Got: %s',
        );
    }

    public static function nullOrUrl(
        $value,
        $protocols = ['http', 'https'],
        string $message = '',
    ): void {
        if (null === $value) {
            return;
        }

        self::url($value, $protocols, $message);
    }

    public static function allScalarRecursive(array $value, string $message = ''): void
    {
        foreach ($value as $key => $_value) {
            if (\is_array($_value)) {
                self::_allScalar($_value, $message);
            } else {
                self::nullOrScalar($_value, $message ?: 'Expected a scalar. Got: %s for '.$key);
            }
        }
    }

    private static function _allScalar(array $value, string $message = ''): void
    {
        foreach ($value as $key => $_value) {
            if (\is_array($_value)) {
                self::_allScalar($_value, $message);
            } else {
                self::nullOrScalar($_value, $message ?: 'Expected a scalar. Got: %s for '.$key);
            }
        }
    }
}
