<?php

namespace Commadore\GraphQL;

final class Mutation extends AbstractQuery
{
    const KEYWORD = 'mutation';
    const GENERATED_NAME_PREFIX = 'mutation_';

    public function getKeyword(): string
    {
        return self::KEYWORD;
    }

    public function getPrefix(): string
    {
        return self::GENERATED_NAME_PREFIX;
    }
}
