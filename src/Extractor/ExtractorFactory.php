<?php

namespace molibdenius\CQRS\Extractor;

final class ExtractorFactory
{
    /**
     * @var array<string, class-string<Extractor>>
     */
    private static array $extractors = [
        'http.payload.extractor' => HttpPayloadExtractor::class,
    ];

    public static function createExtractor(string $name, mixed ...$args): Extractor
    {
        return new self::$extractors[$name](...$args);
    }
}