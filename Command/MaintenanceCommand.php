<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Carbon\CarbonImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RequestContext;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenancePage;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;

#[AsCommand(
    name: 'app:maintenance',
    description: 'Turn maintenance mode on or off, or show whether it\'s on.',
)]
final class MaintenanceCommand extends Command
{
    private const string ACTION = 'action';
    private const string ON = 'on';
    private const string OFF = 'off';
    private const string STATUS = 'status';
    private const string MESSAGE = 'message';
    private const string UNTIL = 'until';
    private const string ALLOW_IP = 'allow-ip';
    private const string ALLOW_MY_IP = 'allow-my-ip';
    private const string REMOVE_IP = 'remove-ip';
    private const string RESET_IPS = 'reset-ips';
    private const string NEW_KEY = 'new-key';
    private const array ON_OPTIONS = [
        self::MESSAGE,
        self::UNTIL,
        self::ALLOW_IP,
        self::ALLOW_MY_IP,
        self::REMOVE_IP,
        self::RESET_IPS,
        self::NEW_KEY,
    ];

    public function __construct(
        private readonly MaintenanceMode $maintenanceMode,
        private readonly MaintenancePage $page,
        private readonly ?string $timeZone = null,
        // for the key's URL: the site's, from framework.router.default_uri
        private readonly ?RequestContext $requestContext = null,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ACTION,
                InputArgument::OPTIONAL,
                \sprintf('%s, %s or %s', self::ON, self::OFF, self::STATUS),
                self::STATUS,
                [self::ON, self::OFF, self::STATUS],
            )
            ->addOption(
                self::MESSAGE,
                null,
                InputOption::VALUE_REQUIRED,
                \sprintf(
                    'Shown to users instead of "%s". Pass "" to go back to it',
                    MaintenanceSettings::DEFAULT_MESSAGE,
                ),
            )
            ->addOption(
                self::UNTIL,
                null,
                InputOption::VALUE_REQUIRED,
                'When it\'s expected to be over, shown to users, eg "15:30", "+30 minutes",'
                .' "2026-09-10 15:30" (in xm_symfony.maintenance.time_zone). Pass "" to remove it',
            )
            ->addOption(
                self::ALLOW_IP,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'An IP or CIDR range that can still use the site, added to any already allowed',
            )
            ->addOption(
                self::ALLOW_MY_IP,
                null,
                InputOption::VALUE_NONE,
                'Allow the IP you\'re connected over SSH from',
            )
            ->addOption(
                self::REMOVE_IP,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Remove an allowed IP or CIDR range',
            )
            ->addOption(
                self::RESET_IPS,
                null,
                InputOption::VALUE_NONE,
                'Remove all allowed IPs (before adding any given)',
            )
            ->addOption(
                self::NEW_KEY,
                null,
                InputOption::VALUE_NONE,
                'Replace the key, so anyone using the current one is blocked',
            )
            ->setHelp(
                <<<'HELP'
                    While it's on, everyone except the allowed IPs & anyone with the key gets the
                    maintenance page (or a 503 for GraphQL requests) & messenger workers pause.

                    A new key is generated each time it's turned on. Opening the key's URL (or any URL
                    with it added) sets a cookie that lets that browser use the site until it's
                    closed. It can also be sent in the X-Maintenance-Key header.

                    Running <info>%command.name% on</info> while it's already on updates it, keeping
                    anything not given (including the key, unless --new-key).
                    HELP,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        return match ($input->getArgument(self::ACTION)) {
            self::ON     => $this->on($input, $io),
            self::OFF    => $this->off($input, $io),
            self::STATUS => $this->status($io),
            default      => throw new InvalidArgumentException(\sprintf(
                'The action must be one of %s, %s or %s.',
                self::ON,
                self::OFF,
                self::STATUS,
            )),
        };
    }

    private function on(InputInterface $input, SymfonyStyle $io): int
    {
        $current = $this->maintenanceMode->settings();
        $settings = $current ?? new MaintenanceSettings();

        if (null !== $input->getOption(self::MESSAGE)) {
            $settings = $settings->withMessage((string) $input->getOption(self::MESSAGE));
        }

        if (null !== $input->getOption(self::UNTIL)) {
            $settings = $settings->withUntil($this->until((string) $input->getOption(self::UNTIL)));
        }

        $settings = $settings->withAllowedIps($this->allowedIps($input, $settings->allowedIps()));

        if (null === $settings->key() || $input->getOption(self::NEW_KEY)) {
            $settings = $settings->withKey(MaintenanceSettings::generateKey());
        }

        $this->maintenanceMode->enable($settings, $this->page->render($settings));

        if (null === $current) {
            $io->success('Maintenance mode is on.');
        } else {
            $io->success('Maintenance mode was already on & has been updated.');
        }
        $this->show($io, $settings);
        $io->comment('Messenger workers pause after the message they\'re on. Turn it off with: app:maintenance off');

        return Command::SUCCESS;
    }

    private function off(InputInterface $input, SymfonyStyle $io): int
    {
        foreach (self::ON_OPTIONS as $option) {
            if ($input->hasParameterOption('--'.$option)) {
                throw new InvalidArgumentException(\sprintf('The --%s option only applies to "%s".', $option, self::ON));
            }
        }

        if ($this->maintenanceMode->disable()) {
            $io->success('Maintenance mode is off.');
        } else {
            $io->note('Maintenance mode was already off.');
        }

        return Command::SUCCESS;
    }

    private function status(SymfonyStyle $io): int
    {
        $settings = $this->maintenanceMode->settings();

        if (null === $settings) {
            $io->writeln('Maintenance mode is <info>off</info>.');

            return Command::SUCCESS;
        }

        $io->writeln('Maintenance mode is <comment>on</comment>.');
        $this->show($io, $settings);

        return Command::SUCCESS;
    }

    private function show(SymfonyStyle $io, MaintenanceSettings $settings): void
    {
        $until = 'not set';
        if (null !== $settings->until()) {
            $until = CarbonImmutable::instance($settings->until())
                ->setTimezone($this->timeZone())
                ->format('Y-m-d g:i a T');
        }

        $allowedIps = 'none';
        if ([] !== $settings->allowedIps()) {
            $allowedIps = implode(', ', $settings->allowedIps());
        }

        $key = 'none';
        if (null !== $settings->key()) {
            $key = $this->keyUrl($settings->key());
        }

        $io->definitionList(
            ['Message' => $settings->message() ?? \sprintf('"%s" (default)', MaintenanceSettings::DEFAULT_MESSAGE)],
            ['Until' => $until],
            ['Allowed IPs' => $allowedIps],
            ['Key URL' => $key],
        );
    }

    private function keyUrl(string $key): string
    {
        $path = '/?'.http_build_query([MaintenanceGate::KEY_PARAMETER => $key]);

        $context = $this->requestContext;
        if (null === $context) {
            return $path;
        }

        $port = '';
        if ('http' === $context->getScheme() && 80 !== $context->getHttpPort()) {
            $port = ':'.$context->getHttpPort();
        } elseif ('https' === $context->getScheme() && 443 !== $context->getHttpsPort()) {
            $port = ':'.$context->getHttpsPort();
        }

        return $context->getScheme().'://'.$context->getHost().$port.$context->getBaseUrl().$path;
    }

    private function until(string $until): ?CarbonImmutable
    {
        if ('' === trim($until)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::parse($until, $this->timeZone());
        } catch (\Exception $e) {
            throw new InvalidArgumentException(\sprintf('"%s" is not a date/time that can be read.', $until), 0, $e);
        }

        if ($parsed->isPast()) {
            throw new InvalidArgumentException(\sprintf(
                '"%s" (%s) is in the past.',
                $until,
                $parsed->format('Y-m-d g:i a T'),
            ));
        }

        return $parsed;
    }

    /**
     * @param list<string> $allowedIps
     *
     * @return list<string>
     */
    private function allowedIps(InputInterface $input, array $allowedIps): array
    {
        if ($input->getOption(self::RESET_IPS)) {
            $allowedIps = [];
        }

        $allowedIps = array_values(array_diff($allowedIps, $input->getOption(self::REMOVE_IP)));

        $add = $input->getOption(self::ALLOW_IP);
        if ($input->getOption(self::ALLOW_MY_IP)) {
            $add[] = $this->sshClientIp();
        }

        foreach ($add as $ip) {
            if (!$this->isValidIp($ip)) {
                throw new InvalidArgumentException(\sprintf('"%s" is not an IP or CIDR range.', $ip));
            }

            $allowedIps[] = $ip;
        }

        return $allowedIps;
    }

    /**
     * SSH_CLIENT is "<client ip> <client port> <server port>".
     */
    private function sshClientIp(): string
    {
        $sshClient = getenv('SSH_CLIENT');

        if (false === $sshClient || '' === trim($sshClient)) {
            throw new InvalidArgumentException(\sprintf(
                'Can\'t tell your IP because SSH_CLIENT isn\'t set. Use --%s instead.',
                self::ALLOW_IP,
            ));
        }

        return explode(' ', trim($sshClient))[0];
    }

    private function isValidIp(string $ip): bool
    {
        [$address, $mask] = explode('/', $ip, 2) + [1 => null];

        if (false === filter_var($address, \FILTER_VALIDATE_IP)) {
            return false;
        }

        if (null === $mask) {
            return true;
        }

        $maxMask = 32;
        if (str_contains($address, ':')) {
            $maxMask = 128;
        }

        return ctype_digit($mask) && (int) $mask <= $maxMask;
    }

    private function timeZone(): string
    {
        return $this->timeZone ?? date_default_timezone_get();
    }
}
