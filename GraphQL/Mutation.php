<?php

namespace Fnash\GraphQL;

final class Mutation
{
    use QueryTrait;

    const KEYWORD = 'mutation';
    const GENERATED_NAME_PREFIX = 'mutation_';
}
