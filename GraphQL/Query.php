<?php

namespace Commadore\GraphQL;

final class Query extends AbstractQuery
{
    const KEYWORD = 'query';
    const GENERATED_NAME_PREFIX = 'query_';

    public function getKeyword(): string
    {
        return self::KEYWORD;
    }

    public function getPrefix(): string
    {
        return self::GENERATED_NAME_PREFIX;
    }
}
