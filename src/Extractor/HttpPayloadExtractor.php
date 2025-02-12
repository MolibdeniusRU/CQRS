<?php

namespace molibdenius\CQRS\Extractor;

use JsonException;
use molibdenius\CQRS\Action\Enum\PayloadType;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HttpPayloadExtractor implements Extractor
{
    /**
     * @param ServerRequestInterface $request
     * @param PayloadType[] $payloadTypes
     */
    public function __construct(
        private ServerRequestInterface $request,
        private array                  $payloadTypes,
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function extract(): array
    {
        $payloads = array_map(
            function (PayloadType $payloadType) {
                return match ($payloadType) {
                    PayloadType::Query => $this->request->getQueryParams(),
                    PayloadType::Body => $this->request->getParsedBody() ??
                        json_decode(
                            json: $this->request->getBody()->getContents(),
                            associative: true,
                            depth: 512,
                            flags: JSON_THROW_ON_ERROR
                        ),
                    default => null,
                };
            },
            $this->payloadTypes
        );

        return array_merge_recursive(...$payloads);
    }
}