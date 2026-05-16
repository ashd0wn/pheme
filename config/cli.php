<?php

declare(strict_types=1);

use App\Console\Command;

return function (App\Event\BuildConsoleCommands $event) {
    $event->addAliases([
        'pheme:acme:get-certificate' => Command\Acme\GetCertificateCommand::class,
        'pheme:backup' => Command\Backup\BackupCommand::class,
        'pheme:restore' => Command\Backup\RestoreCommand::class,
        'pheme:debug:optimize-tables' => Command\Debug\OptimizeTablesCommand::class,
        'pheme:internal:on-ssl-renewal' => Command\Internal\OnSslRenewal::class,
        'pheme:internal:ip' => Command\Internal\GetIpCommand::class,
        'pheme:locale:generate' => Command\Locale\GenerateCommand::class,
        'pheme:locale:import' => Command\Locale\ImportCommand::class,
        'pheme:queue:process' => Command\MessageQueue\ProcessCommand::class,
        'pheme:queue:clear' => Command\MessageQueue\ClearCommand::class,
        'pheme:settings:list' => Command\Settings\ListCommand::class,
        'pheme:settings:set' => Command\Settings\SetCommand::class,
        'pheme:station-queues:clear' => Command\ClearQueuesCommand::class,
        'pheme:account:list' => Command\Users\ListCommand::class,
        'pheme:account:login-token' => Command\Users\LoginTokenCommand::class,
        'pheme:account:reset-password' => Command\Users\ResetPasswordCommand::class,
        'pheme:account:set-administrator' => Command\Users\SetAdministratorCommand::class,
        'pheme:cache:clear' => Command\ClearCacheCommand::class,
        'pheme:config:migrate' => Command\MigrateConfigCommand::class,
        'pheme:setup:migrate' => Command\MigrateDbCommand::class,
        'pheme:setup:fixtures' => Command\SetupFixturesCommand::class,
        'pheme:setup:rollback' => Command\RollbackDbCommand::class,
        'pheme:setup' => Command\SetupCommand::class,
        'pheme:radio:restart' => Command\RestartRadioCommand::class,
        'pheme:sync:nowplaying' => Command\Sync\NowPlayingCommand::class,
        'pheme:sync:nowplaying:station' => Command\Sync\NowPlayingPerStationCommand::class,
        'pheme:sync:run' => Command\Sync\RunnerCommand::class,
        'pheme:sync:task' => Command\Sync\SingleTaskCommand::class,
        'pheme:media:reprocess' => Command\ReprocessMediaCommand::class,
        'pheme:api:docs' => Command\GenerateApiDocsCommand::class,
        'locale:generate' => Command\Locale\GenerateCommand::class,
        'locale:import' => Command\Locale\ImportCommand::class,
        'queue:process' => Command\MessageQueue\ProcessCommand::class,
        'queue:clear' => Command\MessageQueue\ClearCommand::class,
        'cache:clear' => Command\ClearCacheCommand::class,
        'acme:cert' => Command\Acme\GetCertificateCommand::class,
    ]);
};
