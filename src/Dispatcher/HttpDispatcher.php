<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Dispatcher;

use Molibdenius\CQRS\EventLoop\EventLoopInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Throwable;

final readonly class HttpDispatcher implements DispatcherInterface
{
    /**
     * @param EventLoopInterface<ServerRequestInterface|Throwable, ResponseInterface> $eventLoop
     */
    public function __construct(
        private PSR7WorkerInterface $httpWorker,
        private EventLoopInterface  $eventLoop,
    )
    {
    }

    public function serve(): void
    {
        while (true) {
            $request = $this->httpWorker->waitRequest();
            if ($request === null) {
                break;
            }
            try {
                $this->httpWorker->respond($this->eventLoop->run($request));
            } catch (Throwable $exception) {
                $this->httpWorker->respond($this->eventLoop->run($exception));
            }
        }
    }
}
