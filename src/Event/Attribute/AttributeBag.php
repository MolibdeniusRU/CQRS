<?php

declare(strict_types=1);

namespace Molibdenius\CQRS\Event\Attribute;

use Molibdenius\CQRS\Action;
use Molibdenius\CQRS\Registry\ActionConfig;
use Molibdenius\CQRS\Result\Result;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class AttributeBag
{
    public const string ACTION = 'action';

    public const string RESULT = 'result';

    public const string ERROR = 'error';

    public const string CONFIG = 'config';

    public const string RESPONSE = 'response';

    public const string DEBUG = 'debug';

    public const array ALLOWED_ATTRIBUTES = [
        self::ACTION,
        self::RESULT,
        self::ERROR,
        self::CONFIG,
        self::RESPONSE,
        self::DEBUG,
    ];

    public function __construct(
        public ?Action            $action = null,
        public ?Result            $result = null,
        public ?Throwable         $error = null,
        public ?ActionConfig      $config = null,
        public ?ResponseInterface $response = null,
        public bool               $debug = false
    )
    {
    }
}
