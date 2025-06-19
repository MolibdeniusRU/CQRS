<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\EventSubscriber;

use Molibdenius\CQRS\Action;
use Molibdenius\CQRS\ActionResolverInterface;
use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\NewTaskEvent;
use Molibdenius\CQRS\Event\RequestEvent;
use Molibdenius\CQRS\Exception\ImplementationException;
use Molibdenius\CQRS\Registry\ActionRegistryInterface;
use Molibdenius\CQRS\Registry\PayloadType;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ActionCreator implements EventSubscriberInterface
{
    public function __construct(
        private ActionResolverInterface                   $actionResolver,
        private SerializerInterface&DenormalizerInterface $serializer,
        private ActionRegistryInterface                   $actionRegistry
    )
    {
    }

    public function createFromRequest(RequestEvent $event): void
    {
        try {
            $request = $event->getRequest();
            $actionClass = $this->actionResolver->resolve($request);
            $config = $this->actionRegistry->getActionConfig($actionClass);

            $event->setConfig($config);

            if (null === $config->payloadType) {
                $event->setAction(new $actionClass());

                return;
            }

            $queryStringHandler = function (ServerRequestInterface $request, string $actionClass): Action {
                $data = $request->getQueryParams();

                return $this->denormalizeAction($data, $actionClass);
            };

            $formHandler = function (ServerRequestInterface $request, string $actionClass): Action {
                $data = $request->getParsedBody() ?? [];

                if (is_object($data)) {
                    $data = (array)$data;
                }

                $data += $request->getUploadedFiles();

                return $this->denormalizeAction($data, $actionClass);
            };

            $bodyHandler = function (ServerRequestInterface $request, string $actionClass): Action {
                $data = $request->getBody()->getContents();

                $format = match ($request->getHeaderLine('Content-Type')) {
                    'application/json' => 'json',
                    'application/xml' => 'xml',
                };

                return $this->deserializeAction($data, $actionClass, $format);
            };

            $handler = match ($config->payloadType) {
                PayloadType::QueryString => $queryStringHandler(...),
                PayloadType::Form => $formHandler(...),
                PayloadType::Body => $bodyHandler(...),
            };

            $event->setAction($handler($request, $actionClass));
        } catch (\Throwable $exception) {
            $event->setError($exception);
        }
    }

    public function createFromTask(NewTaskEvent $event): void
    {
        try {
            $payload = $event->getTask()->getPayload();


            $actionClass = $event->getTask()->getHeaderLine('action_class');

            if (!class_exists($actionClass)) {
                throw new \RuntimeException("Class $actionClass not found", 500);
            }

            $action = $this->deserializeAction($payload, $actionClass, 'json');

            $event->setAction($action);

        } catch (\Throwable $exception) {
            $event->setError($exception);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::Request->value => ['createFromRequest', 0],
            Events::NewTask->value => ['createFromTask', 0],
        ];
    }

    /**
     * @throws ImplementationException
     * @throws SerializerExceptionInterface
     */
    private function denormalizeAction(array $data, string $actionClass): Action
    {
        $action = $this->serializer->denormalize(
            $data,
            $actionClass,
            'object',
            ['collect_denormalization_errors' => true, 'filter_bool' => true]
        );

        if (!$action instanceof Action) {
            throw new ImplementationException($actionClass, Action::class);
        }

        return $action;
    }

    /**
     * @throws ImplementationException
     */
    private function deserializeAction(string $data, string $actionClass, string $format): Action
    {
        $action = $this->serializer->deserialize($data, $actionClass, $format);

        if (!$action instanceof Action) {
            throw new ImplementationException($actionClass, Action::class);
        }

        return $action;
    }
}
