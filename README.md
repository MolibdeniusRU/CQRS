CQRS-framework
==============
  CQRS-framework - это каркас приложения реализующий паттерн 
проектирования - [Command, Query Responsibility Segregation](https://en.wikipedia.org/wiki/Command_Query_Responsibility_Segregation),
 построенный на компонентах [Symfony](https://symfony.com/), [Doctrine](https://www.doctrine-project.org/) и [RoadRunner](https://docs.roadrunner.dev/docs).
  
Базовое использование
=====================

* Установить используя [composer](https://getcomposer.org/)
````
composer require molibdenius/CQRS
````
* Создать пользовательские обработчики и действия, интегрируя их с помощью специальных интерфейсов, трейтов и атрибутов.
````php
// Пример обработки действия-команды.

use molibdenius\CQRS\Action\Action
use molibdenius\CQRS\Action\Actionable
use molibdenius\CQRS\Handler\Handler
use molibdenius\CQRS\Handler\Attribute\AsCommandHandler
use molibdenius\CQRS\Router\HttpMethod
use molibdenius\CQRS\Action\Enum\PayloadType

class CreateUserCommand implements Action
{
    use Actionable;
    
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $confirmPassword
    ) 
    {
    }
}

#[AsCommandHandler(
    commandClass: CreateUserCommand::class,
    path: '/users',
    method: HttpMethod::POST,
    payloadType: PayloadType::Body  
)]
#[Autoconfigure(public: true)] // Необходимо указывать для корректной компиляции сервис-контейнера
class CreateUserHandler implements Handler
{
    public function handle(Action $action) 
    {
        // Ваша логика по созданию пользователя.
    }
    
}
````
````php
// Пример обработки действия-запроса.

class GetUserCollectionQuery implements Action
{
    use Actionable;

    public function __construct(
        public int $id
    )
    {
    }
}

#[QueryHandler(
    queryClass: GetUserCollectionQuery::class,
    path: '/users',
    method: HttpMethod::GET,
    payloadType: PayloadType::Query
)]
class GetUserCollectionHandler implements Handler
{
    public function handle(Action $action)
    {
        // Ваша логика получения пользователя.
    }
}
````
* Добавить файл конфигурации сервис-контейнера ./config/services.yaml, в нем обязательно указать параметр handlers_dirs, 
указывающий каталоги с пользовательскими обработчиками. 

````yaml
# ./config/services.yaml
parameters:
 handlers_namespaces: [App\Handler\Command\, App\Handler\Query\]

services:
 # default configuration for services in *this* file
 _defaults:
  autowire: true      # Automatically injects dependencies in your services.
  autoconfigure: true # Automatically registers your services as commands, event subscribers, etc.


 # makes classes in src/ available to be used as services
 # this creates a service per class whose id is the fully-qualified class name
 App\:
  resource: '../src/'
  exclude:
   - '../src/DependencyInjection/'
   - '../src/Entity/'
````
* Добавить .env-файл и указать переменные окружения
````dotenv
APPLICATION_MODE="dev,prod или test"
DATABASE_URL="{schema}://{user}:{password}@{host}/{database_name}"
````
* Добавить файлы конфигурации doctrine.yaml и migration.yaml, 
необходимы для конфигурации EntityManager`a и консольного приложения управляющего миграциями.
````yaml
# ./config/doctrine.yaml
doctrine:
  dbal:
    url: '%env(resolve:DATABASE_URL)%'

  orm:
    auto_generate_proxy_classes: true
    enable_lazy_ghost_objects: true
    report_fields_where_declared: true
    validate_xml_mapping: true
    naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
    auto_mapping: true
    mappings:
      App:
        type: attribute
        is_bundle: false
        dir: '%kernel.project_dir%/src/Entity'
        prefix: 'App\Entity'
        alias: App
````
````yaml
# ./config/migrations.yaml
table_storage:
  table_name: doctrine_migration_versions
  version_column_name: version
  version_column_length: 191
  executed_at_column_name: executed_at
  execution_time_column_name: execution_time

migrations_paths:
  'Migrations': ./migrations

all_or_nothing: true
transactional: true
check_database_platform: true
organize_migrations: none
````
* Установить исполняющий [файл RoadRunner`a](https://docs.roadrunner.dev/docs/general/install)
* Добавить файл запускающий приложение и указать его в конфигурации RoadRunner`a
````php
// Server.php
require __DIR__ . '/vendor/autoload.php';

use molibdenius\CQRS\Application;

$application = new Application();
$application->run();
````
* Добавить конфигурационный файл для RoadRunner`a
````yaml
version: '3'
rpc:
    listen: 'tcp://127.0.0.1:6001'
server:
    command: 'php Server.php'
    relay: pipes
http:
    address: '0.0.0.0:8080'
    middleware:
        - gzip
        - static
    static:
        dir: public
        forbid:
            - .php
            - .htaccess
    pool:
        num_workers: 1
        supervisor:
            max_worker_memory: 100
jobs:
    pool:
        num_workers: 2
        supervisor:
            max_worker_memory: 100
    pipelines:
        command:
            driver: amqp
            config:
                # QoS - prefetch.
                #
                # Default: 10
                prefetch: 10

                # Pipeline priority
                #
                # If the job has priority set to 0, it will inherit the pipeline's priority. Default: 10.
                priority: 1

                # Redial timeout (in seconds). How long to try to reconnect to the AMQP server.
                #
                # Default: 60
                redial_timeout: 60

                # Durable queue
                #
                # Default: false
                durable: false

                # Durable exchange (rabbitmq option: https://www.rabbitmq.com/tutorials/amqp-concepts.html#exchanges)
                #
                # Default: false
                exchange_durable: false

                # Auto-delete (exchange is deleted when last queue is unbound from it): https://www.rabbitmq.com/tutorials/amqp-concepts.html#exchanges
                #
                # Default: false
                exchange_auto_delete: false

                # Auto-delete (queue that has had at least one consumer is deleted when last consumer unsubscribes) (rabbitmq option: https://www.rabbitmq.com/queues.html#properties)
                #
                # Default: false
                queue_auto_delete: false

                # Delete queue when stopping the pipeline
                #
                # Default: false
                delete_queue_on_stop: false

                # Queue name
                #
                # Default: default
                queue: command

                # Exchange name
                #
                # Default: amqp.default
                exchange: default

                # Exchange type
                #
                # Default: direct.
                exchange_type: direct

                # Routing key for the queue
                #
                # Default: empty.
                routing_key: command

                # Declare a queue exclusive at the exchange
                #
                # Default: false
                exclusive: false

                # When multiple is true, this delivery and all prior unacknowledged deliveries
                # on the same channel will be acknowledged.  This is useful for batch processing
                # of deliveries
                #
                # Default: false
                multiple_ack: false

                # The consumer_id is identified by a string that is unique and scoped for all consumers on this channel.
                #
                # Default: "roadrunner" + uuid.
                consumer_id: "roadrunner-uuid"

                # Use rabbitmq mechanism to requeue the job on fail
                #
                # Default: false
                requeue_on_fail: false

                # Queue headers (new in 2.12.2)
                #
                # Default: null
                queue_headers:
                    x-queue-mode: lazy

    consume: ["command", "query"]
amqp:
    addr: amqp://admin:0000@127.0.0.1:5672
kv:
    local:
        driver: memory
        config:
            interval: 60
metrics:
    address: '127.0.0.1:2112'
````