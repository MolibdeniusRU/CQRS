<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\EventSubscriber;

use JsonException;
use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\HttpErrorEvent;
use Molibdenius\CQRS\Event\ResponseEvent;
use Molibdenius\CQRS\Exception\EventException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ResponseWriter implements EventSubscriberInterface
{
    public function __construct(
        private SerializerInterface $serializer,
    )
    {
    }

    /**
     * @throws EventException
     */
    public function writeResponse(ResponseEvent $event): void
    {
        if (!$event->hasResponse()) {
            throw new EventException('Event has no response', 500);
        }

        if (!$event->hasResult()) {
            return;
        }

        $content = $this->serializer->serialize($event->getResult()->getContent(), 'json');

        $event->getResponse()->getBody()->write($content);
    }

    /**
     * @throws JsonException
     * @throws EventException
     */
    public function writeErrorResponse(HttpErrorEvent $event): void
    {
        if (!$event->isDebug()) {
            return;
        }

        if (!$event->hasResponse()) {
            throw new EventException('Event has no response', 500);
        }

        $event->getResponse()->getBody()->write($this->exceptionToJson($event->getError()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::Response->value => ['writeResponse', 0],
            Events::HttpError->value => ['writeErrorResponse', 0],
        ];
    }

    /**
     * @throws JsonException
     */
    private function exceptionToJson(\Throwable $exception): string
    {
        $namespaceChunks = explode('\\', $exception::class);
        $exceptionType = array_pop($namespaceChunks);

        return sprintf(
            '{"message": "%s", "code": %s, "exception_type": "%s", "trace": %s, "previous": %s}',
            addslashes($exception->getMessage()),
            $exception->getCode(),
            $exceptionType,
            json_encode($exception->getTrace(), JSON_THROW_ON_ERROR) ?: '',
            $exception->getPrevious() !== null ? $this->exceptionToJson($exception->getPrevious()) : 'null'
        );
    }
}
