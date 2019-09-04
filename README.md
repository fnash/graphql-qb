## Refactored version of https://github.com/fnash/graphql-qb.
I have a use case where I need to mock this lib, so statics and traits aren't desirable

# graphql-qb
A php GraphQL Query Builder. Nice API. Readable queries. Examples in Unit Tests.

Includes:
- Query / Mutation / Fragment
- Sorted Fields
- Custom Operation name
- A predictable operation name is generated if you don't specify one and add variables
- Add variables
- Add arguments
- Directives (Include / Skip)
- Sub query

TODO:
- Arguments in sub queries


```php
<?php

include_once 'vendor/autoload.php';

use Commadore\GraphQL\Query;

$query = Query::create('article')
    ->variables([
        '$withTags' => 'Boolean = false',
    ])
    ->fields([
        'id',
        'title',
        'body',
        'myLanguageAlias' => 'language',
        'tags' => Query::create()->fields([
            'id',
            'tagLabel' => 'label',
            'language',
            'taxonomy' => Query::create()->fields([
                'id',
                'label',
                'language'
            ]),
        ])
    ])
    ->includeIf([
        'tags' => '$withTags'
    ])
;

echo $query;
```


```graphql
query query_d084b5fa08a495bb76e87b51cb5e2b33fc87039a($withTags: Boolean = false) {
  article {
    body
    id
    myLanguageAlias: language
    tags @include(if: $withTags) {
      id
      language
      tagLabel: label
      taxonomy {
        id
        label
        language
      }
    }
    title
  }
}

```

