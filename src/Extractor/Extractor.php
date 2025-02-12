<?php

namespace molibdenius\CQRS\Extractor;

interface Extractor
{

    /**
     * @return mixed[]
     */
    public function extract(): array;
}