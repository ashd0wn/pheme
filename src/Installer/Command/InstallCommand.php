<?php

declare(strict_types=1);

namespace App\Installer\Command;

use App\Container\EnvironmentAwareTrait;
use App\Enums\SupportedLocales;
use App\Environment;
use App\Installer\EnvFiles\AbstractEnvFile;
use App\Installer\EnvFiles\PhemeEnvFile;
use App\Installer\EnvFiles\EnvFile;
use App\Radio\Configuration;
use App\Utilities\Strings;
use App\Utilities\Types;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'install'
)]
final class InstallCommand extends Command
{
    use EnvironmentAwareTrait;

    public const DEFAULT_BASE_DIRECTORY = '/installer';

    protected function configure(): void
    {
        $this->addArgument('base-dir', InputArgument::OPTIONAL)
            ->addOption('update', null, InputOption::VALUE_NONE)
            ->addOption('defaults', null, InputOption::VALUE_NONE)
            ->addOption('http-port', null, InputOption::VALUE_OPTIONAL)
            ->addOption('https-port', null, InputOption::VALUE_OPTIONAL)
            ->addOption('release-channel', null, InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $baseDir = Types::string($input->getArgument('base-dir'), self::DEFAULT_BASE_DIRECTORY);
        $update = Types::bool($input->getOption('update'));
        $defaults = Types::bool($input->getOption('defaults'));
        $httpPort = Types::intOrNull($input->getOption('http-port'));
        $httpsPort = Types::intOrNull($input->getOption('https-port'));
        $releaseChannel = Types::stringOrNull($input->getOption('release-channel'));

        $devMode = ($baseDir !== self::DEFAULT_BASE_DIRECTORY);

        // Initialize all the environment variables.
        $envPath = EnvFile::buildPathFromBase($baseDir);
        $phemeEnvPath = PhemeEnvFile::buildPathFromBase($baseDir);

        // Fail early if permissions aren't present.
        if (!is_writable($envPath)) {
            $io->error(
                'Permissions error: cannot write to work directory. Exiting installer and using defaults instead.'
            );
            return 1;
        }

        $isNewInstall = !$update;

        try {
            $env = EnvFile::fromEnvFile($envPath);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $env = new EnvFile($envPath);
        }

        try {
            $phemeEnv = PhemeEnvFile::fromEnvFile($phemeEnvPath);
        } catch (InvalidArgumentException $e) {
            $io->error($e->getMessage());
            $phemeEnv = new PhemeEnvFile($envPath);
        }

        // Podman support
        $isPodman = $env->getAsBool('PHEME_PODMAN_MODE', false);
        if ($isPodman) {
            $phemeEnv[Environment::ENABLE_WEB_UPDATER] = 'false';
        }

        // Initialize locale for translated installer/updater.
        if (!$defaults && ($isNewInstall || empty($phemeEnv[Environment::LANG]))) {
            $langOptions = [];
            foreach (SupportedLocales::cases() as $supportedLocale) {
                $langOptions[$supportedLocale->getLocaleWithoutEncoding()] = $supportedLocale->getLocalName();
            }

            $phemeEnv[Environment::LANG] = $io->choice(
                'Select Language',
                $langOptions,
                SupportedLocales::default()->getLocaleWithoutEncoding()
            );
        }

        $locale = SupportedLocales::getValidLocale($phemeEnv[Environment::LANG] ?? null);
        $locale->register($this->environment);

        $envConfig = EnvFile::getConfiguration($this->environment);
        $env->setFromDefaults($this->environment);

        $phemeEnvConfig = PhemeEnvFile::getConfiguration($this->environment);
        $phemeEnv->setFromDefaults($this->environment);

        // Apply values passed via flags
        if (null !== $releaseChannel) {
            $env['PHEME_VERSION'] = $releaseChannel;
        }
        if (null !== $httpPort) {
            $env['PHEME_HTTP_PORT'] = (string)$httpPort;
        }
        if (null !== $httpsPort) {
            $env['PHEME_HTTPS_PORT'] = (string)$httpsPort;
        }

        // Migrate legacy config values.
        if (isset($phemeEnv['PREFER_RELEASE_BUILDS'])) {
            $env['PHEME_VERSION'] = ('true' === $phemeEnv['PREFER_RELEASE_BUILDS'])
                ? 'stable'
                : 'latest';

            unset($phemeEnv['PREFER_RELEASE_BUILDS']);
        }

        unset($phemeEnv['ENABLE_ADVANCED_FEATURES']);

        // Randomize the MariaDB root password for new installs.
        if ($isNewInstall) {
            if ($devMode) {
                if (empty($phemeEnv['MYSQL_ROOT_PASSWORD'])) {
                    $phemeEnv['MYSQL_ROOT_PASSWORD'] = 'azur4c457_root';
                }
            } else {
                if (
                    empty($phemeEnv[Environment::DB_PASSWORD])
                    || 'azur4c457' === $phemeEnv[Environment::DB_PASSWORD]
                ) {
                    $phemeEnv[Environment::DB_PASSWORD] = Strings::generatePassword(12);
                }

                if (empty($phemeEnv['MYSQL_ROOT_PASSWORD'])) {
                    $phemeEnv['MYSQL_ROOT_PASSWORD'] = Strings::generatePassword(20);
                }
            }
        }

        if (!empty($phemeEnv['MYSQL_ROOT_PASSWORD'])) {
            unset($phemeEnv['MYSQL_RANDOM_ROOT_PASSWORD']);
        } else {
            $phemeEnv['MYSQL_RANDOM_ROOT_PASSWORD'] = 'yes';
        }

        // Special fixes for transitioning to standalone installations.
        if ($this->environment->isDocker()) {
            if ('mariadb' === $phemeEnv['MYSQL_HOST']) {
                unset($phemeEnv['MYSQL_HOST']);
            }
            if ('redis' === $phemeEnv['REDIS_HOST']) {
                unset($phemeEnv['REDIS_HOST']);
            }
        }

        // Display header messages
        if ($isNewInstall) {
            $io->title(
                __('Pheme Installer')
            );
            $io->block(
                __('Welcome to Pheme! Complete the initial server setup by answering a few questions.')
            );

            $customize = !$defaults;
        } else {
            $io->title(
                __('Pheme Updater')
            );

            if ($defaults) {
                $customize = false;
            } else {
                $customize = $io->confirm(
                    __('Change installation settings?'),
                    false
                );
            }
        }

        if ($customize) {
            // Port customization
            $io->writeln(
                __('Pheme is currently configured to listen on the following ports:'),
            );
            $io->listing(
                [
                    sprintf(__('HTTP Port: %d'), $env['PHEME_HTTP_PORT']),
                    sprintf(__('HTTPS Port: %d'), $env['PHEME_HTTPS_PORT']),
                    sprintf(__('SFTP Port: %d'), $env['PHEME_SFTP_PORT']),
                    sprintf(__('Radio Ports: %s'), $env['PHEME_STATION_PORTS']),
                ],
            );

            $customizePorts = $io->confirm(
                __('Customize ports used for Pheme?'),
                false
            );

            if ($customizePorts) {
                $simplePorts = [
                    'PHEME_HTTP_PORT',
                    'PHEME_HTTPS_PORT',
                    'PHEME_SFTP_PORT',
                ];

                foreach ($simplePorts as $port) {
                    $env[$port] = $io->ask(
                        sprintf(
                            '%s - %s',
                            $envConfig[$port]['name'],
                            $envConfig[$port]['description'] ?? ''
                        ),
                        $env[$port]
                    );
                }

                $phemeEnv[Environment::AUTO_ASSIGN_PORT_MIN] = $io->ask(
                    $phemeEnvConfig[Environment::AUTO_ASSIGN_PORT_MIN]['name'],
                    $phemeEnv[Environment::AUTO_ASSIGN_PORT_MIN]
                );

                $phemeEnv[Environment::AUTO_ASSIGN_PORT_MAX] = $io->ask(
                    $phemeEnvConfig[Environment::AUTO_ASSIGN_PORT_MAX]['name'],
                    $phemeEnv[Environment::AUTO_ASSIGN_PORT_MAX]
                );

                $stationPorts = Configuration::enumerateDefaultPorts(
                    rangeMin: Types::int($phemeEnv[Environment::AUTO_ASSIGN_PORT_MIN]),
                    rangeMax: Types::int($phemeEnv[Environment::AUTO_ASSIGN_PORT_MAX])
                );
                $env['PHEME_STATION_PORTS'] = implode(',', $stationPorts);
            }

            $phemeEnv['COMPOSER_PLUGIN_MODE'] = $io->confirm(
                $phemeEnvConfig['COMPOSER_PLUGIN_MODE']['name'],
                $phemeEnv->getAsBool('COMPOSER_PLUGIN_MODE', false)
            ) ? 'true' : 'false';

            if (!$isPodman) {
                $phemeEnv[Environment::ENABLE_WEB_UPDATER] = $io->confirm(
                    $phemeEnvConfig[Environment::ENABLE_WEB_UPDATER]['name'],
                    $phemeEnv->getAsBool(Environment::ENABLE_WEB_UPDATER, true)
                ) ? 'true' : 'false';
            }
        }

        $io->writeln(
            __('Writing configuration files...')
        );

        $envStr = $env->writeToFile($this->environment);
        $phemeEnvStr = $phemeEnv->writeToFile($this->environment);

        if ($io->isVerbose()) {
            $io->section($env->getBasename());
            $io->block($envStr);

            $io->section($phemeEnv->getBasename());
            $io->block($phemeEnvStr);
        }

        $dockerComposePath = ($devMode)
            ? $baseDir . '/docker-compose.yml'
            : $baseDir . '/docker-compose.new.yml';
        $dockerComposeStr = $this->updateDockerCompose($dockerComposePath, $env, $phemeEnv);

        if ($io->isVerbose()) {
            $io->section(basename($dockerComposePath));
            $io->block($dockerComposeStr);
        }

        $io->success(
            __('Server configuration complete!')
        );
        return 0;
    }

    private function updateDockerCompose(
        string $dockerComposePath,
        AbstractEnvFile $env,
        AbstractEnvFile $phemeEnv
    ): string {
        // Attempt to parse Docker Compose YAML file
        $sampleFile = $this->environment->getBaseDirectory() . '/docker-compose.sample.yml';

        /** @var array $yaml */
        $yaml = Yaml::parseFile($sampleFile);

        // Parse port listing and convert into YAML format.
        $ports = $env['PHEME_STATION_PORTS'] ?? '';

        $envConfig = $env::getConfiguration($this->environment);
        $defaultPorts = $envConfig['PHEME_STATION_PORTS']['default'] ?? '';

        if (!empty($ports) && 0 !== strcmp($ports, $defaultPorts)) {
            $yamlPorts = [];
            $nginxRadioPorts = [];
            $nginxWebDjPorts = [];

            foreach (explode(',', $ports) as $port) {
                $port = (int)$port;
                if ($port <= 0) {
                    continue;
                }

                $yamlPorts[] = $port . ':' . $port;

                if (0 === $port % 10) {
                    $nginxRadioPorts[] = $port;
                } elseif (5 === $port % 10) {
                    $nginxWebDjPorts[] = $port;
                }
            }

            if (!empty($yamlPorts)) {
                $existingPorts = [];
                foreach ($yaml['services']['web']['ports'] as $port) {
                    if (str_starts_with($port, '$')) {
                        $existingPorts[] = $port;
                    }
                }

                $yaml['services']['web']['ports'] = array_merge($existingPorts, $yamlPorts);
            }
            if (!empty($nginxRadioPorts)) {
                $yaml['services']['web']['environment']['NGINX_RADIO_PORTS'] = '(' . implode(
                    '|',
                    $nginxRadioPorts
                ) . ')';
            }
            if (!empty($nginxWebDjPorts)) {
                $yaml['services']['web']['environment']['NGINX_WEBDJ_PORTS'] = '(' . implode(
                    '|',
                    $nginxWebDjPorts
                ) . ')';
            }
        }

        // Add plugin mode if it's selected.
        if ($phemeEnv->getAsBool('COMPOSER_PLUGIN_MODE', false)) {
            $yaml['services']['web']['volumes'][] = 'www_vendor:/var/pheme/www/vendor';
            $yaml['volumes']['www_vendor'] = [];
        }

        // Remove privileged-mode settings if not enabled.
        if (!$env->getAsBool('PHEME_COMPOSE_PRIVILEGED', true)) {
            foreach ($yaml['services'] as &$service) {
                unset(
                    $service['ulimits'],
                    $service['sysctls']
                );
            }
            unset($service);
        }

        // Remove web updater if disabled.
        if (!$phemeEnv->getAsBool(Environment::ENABLE_WEB_UPDATER, true)) {
            unset($yaml['services']['updater']);
        }

        // Podman privileged mode explicit specification.
        if ($env->getAsBool('PHEME_PODMAN_MODE', false)) {
            $yaml['services']['web']['privileged'] = 'true';
        }

        $yamlRaw = Yaml::dump($yaml, PHP_INT_MAX);
        file_put_contents($dockerComposePath, $yamlRaw);

        return $yamlRaw;
    }
}
