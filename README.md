# graphql-qb
A php GraphQL Query Builder. Nice API. Readable queries. Examples in Unit Tests.

Includes:
- Sorted fields
- Operation/query name
- Add variables
- A predictable operation name is generated if you don't specify one and add variables
- Add arguments
- Directives (Include / Skip)
- Sub query
- Fragment

TODO:
- Arguments in sub queries


```php
<?php

include_once 'vendor/autoload.php';

use Fnash\GraphQL\Query;

$query = Query::create('article')
    ->variables([
        '$withTags' => 'Boolean = false',
    ])
    ->fields([
        'id',
        'title',
        'brand',
        'myLangAlias' => 'lang',
        'tags' => Query::create()->fields([
            'id',
            'brand',
            'lang',
            'label',
            'vocabulary' => Query::create()->fields([
                '_ObjectType_',
                '_ObjectId_',
                'id',
                'brand',
                'myLangAlias' => 'lang',
                'label',
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
query query_812cacc644f4e19fd6fe1525ba99060388e37f47($withTags: Boolean = false) {
  article {
    brand
    id
    myLangAlias: lang
    tags @include(if: $withTags) {
      brand
      id
      label
      lang
      vocabulary {
        _ObjectId_
        _ObjectType_
        brand
        id
        label
        myLangAlias: lang
      }
    }
    title
  }
}

```

