<?php

namespace Molibdenius\CQRS\EventLoop;

use Molibdenius\CQRS\Event\Attribute\AttributeBag;
use Molibdenius\CQRS\Event\Events;
use Molibdenius\CQRS\Event\HttpErrorEvent;
use Molibdenius\CQRS\Event\NewActionEvent;
use Molibdenius\CQRS\Event\RequestEvent;
use Molibdenius\CQRS\Event\ResponseEvent;
use Molibdenius\CQRS\Exception\EventException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use WeakMap;

/**
 * @implements EventLoopInterface<ServerRequestInterface|Throwable, ResponseInterface>
 */
final readonly class HttpEventLoop implements EventLoopInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private EventDispatcherInterface $eventDispatcher,
        private bool                     $debug = false
    )
    {
    }

    public function run(object $initiator): ResponseInterface
    {
        if ($initiator instanceof Throwable) {
            return $this->handleError($initiator);
        }

        $request = $initiator;

        try {
            $event = new RequestEvent($request, new \WeakMap());

            $event = $this->eventDispatcher->dispatch($event, Events::Request->value);

            if (!$event->hasAction() || !$event->hasConfig()) {
                if ($event->hasError()) {
                    return $this->handleError($event->getError());
                }

                throw new EventException('Event Request does not contain action or configuration', 500);
            }

            $event = new NewActionEvent($request, $event->getStorage());

            $event = $this->eventDispatcher->dispatch($event, Events::NewAction->value);

            if (!$event->hasResult()) {
                if ($event->hasError()) {
                    return $this->handleError($event->getError());
                }

                throw new EventException('Event NewAction does not contain result', 500);
            }

            $statusCode = $event->getResult()->getCode();

            $event = new ResponseEvent($request, $event->getStorage(), [
                AttributeBag::RESPONSE => $this->responseFactory->createResponse($statusCode)
            ]);

            $event = $this->eventDispatcher->dispatch($event, Events::Response->value);

            if (!$event->hasResponse()) {
                if ($event->hasError()) {
                    return $this->handleError($event->getError());
                }

                throw new EventException('Event Response does not contain response', 500);
            }
            return $event->getResponse();
        } catch (Throwable $error) {
            return $this->handleError($error);
        }
    }

    /**
     * @throws EventException
     */
    private function handleError(Throwable $error): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($error->getCode() == 0 ? 500 : (int)$error->getCode());

        $event = new HttpErrorEvent($error, new WeakMap(), [
            AttributeBag::RESPONSE => $response,
            AttributeBag::DEBUG => $this->debug
        ]);

        $event = $this->eventDispatcher->dispatch($event, Events::HttpError->value);

        if (!$event->hasResponse()) {
            throw new EventException('Event HttpError does not contain response');
        }

        return $event->getResponse();
    }
}
