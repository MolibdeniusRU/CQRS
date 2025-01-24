<?php

namespace Migrations;

require_once __DIR__ . '/../helpers/functions.php';
require get_project_dir() . '/vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\YamlFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use molibdenius\CQRS\ApplicationMode;
use Symfony\Component\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

$projectDir = get_project_dir();

$dotenv = new Dotenv();
$dotenv->bootEnv($projectDir . '/.env');
$dnsParser = new DsnParser(require __DIR__ . '/../config/pdo_map.php');

$paths = [$projectDir . '/src/Entity'];

$ORMConfig = ORMSetup::createAttributeMetadataConfiguration(
    paths: $paths,
    isDevMode: $_ENV['APPLICATION_MODE'] === ApplicationMode::Development->value
);
$connection = DriverManager::getConnection(
    params: $dnsParser->parse($_ENV['DATABASE_URL']),
    config: $ORMConfig
);

$entityManager = new EntityManager(
    conn: $connection,
    config: $ORMConfig
);

$dependencyFactory = DependencyFactory::fromEntityManager(
    configurationLoader: new YamlFile($projectDir . '/config/migrations.yaml'),
    emLoader: new ExistingEntityManager($entityManager)
);

$cli = new Application('Doctrine Migrations');
$cli->addCommands(array(
    new Command\CurrentCommand($dependencyFactory),
    new Command\DiffCommand($dependencyFactory),
    new Command\DumpSchemaCommand($dependencyFactory),
    new Command\ExecuteCommand($dependencyFactory),
    new Command\GenerateCommand($dependencyFactory),
    new Command\LatestCommand($dependencyFactory),
    new Command\ListCommand($dependencyFactory),
    new Command\MigrateCommand($dependencyFactory),
    new Command\RollupCommand($dependencyFactory),
    new Command\StatusCommand($dependencyFactory),
    new Command\SyncMetadataCommand($dependencyFactory),
    new Command\UpToDateCommand($dependencyFactory),
    new Command\VersionCommand($dependencyFactory),
));
$cli->run();
