<?php

namespace molibdenius\CQRS\Extractor;

use JsonException;
use Psr\Http\Message\ServerRequestInterface;

final readonly class HttpPayloadExtractor implements Extractor
{
    public function __construct(
        private ServerRequestInterface $request,
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function extract(): array
    {
        return array_merge(
            $this->request->getQueryParams(),
            $this->getBodyParams()
        );
    }

    /**
     * @return mixed[]
     * @throws JsonException
     */
    private function getBodyParams(): array
    {
        $body = $this->request->getParsedBody() ?? $this->request->getBody()->getContents();

        if (is_string($body)) {
            if ($body === '') {
                return [];
            }

            $body = json_decode(
                json: $body,
                associative: true,
                depth: 512,
                flags: JSON_THROW_ON_ERROR
            );
        }

        return $body;
    }
}