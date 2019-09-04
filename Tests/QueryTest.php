<?php

namespace Tests\Commadore\GraphQL;

use Commadore\GraphQL\Fragment;
use Commadore\GraphQL\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    /**
     * Tests adding fields and args using constructor parameters or method call.
     */
    public function testAddFields()
    {
        $query1 = new Query('article', [
            'id' => 999,
            'title' => 'Hello World',
            'note' => 3.5,
        ], [
            'id',
            'title',
            'body',
        ]);

        $query2 = (new Query('article'))
            ->arguments([
                'id' => 999,
                'title' => 'Hello World',
                'note' => 3.5,
            ])
            ->fields([
                'id',
                'title',
                'body',
            ]);

        $expected =
'{
  article(id: 999, note: 3.5, title: "Hello World") {
    body
    id
    title
  }
}
';
        $this->assertEquals($expected, (string) $query1);
        $this->assertEquals($expected, (string) $query2);
    }

    /**
     * Tests the order of fields and arguments.
     */
    public function testSortFields()
    {
        $query1 = (new Query('article'))
            ->arguments([
                'title' => 'Hello World',
                'note' => 3.5,
                'id' => 999,
            ])
            ->fields([
                'id',
                'title',
                'body',
            ]);

        $query2 = (new Query('article'))
            ->arguments([
                'id' => 999,
                'title' => 'Hello World',
                'note' => 3.5,
            ])
            ->fields([
                'title',
                'id',
                'body',
            ]);

        $this->assertEquals((string) $query1, (string) $query2);
    }

    /**
     * Tests field alias.
     */
    public function testAlias()
    {
        $query = (new Query('article'))->fields([
                'articleId' => 'id',
                'articleTitle' => 'title',
                'body',
            ]);

        $expected =
'{
  article {
    articleId: id
    articleTitle: title
    body
  }
}
';
        $this->assertEquals($expected, (string) $query);
    }

    /**
     * Tests operation name generation and printing.
     */
    public function testOperationName()
    {
        // query with variables but no operation name
        $query1 = (new Query('article'))
            ->variables([
                '$id' => 'Integer',
            ])
            ->arguments([
                'id' => '$id',
            ])
            ->fields([
                'id',
                'title',
                'body',
            ]);

        $expected1 =
'query query_a6fa4442880a206cf86fc5c24e0a384637ab885d($id: Integer) {
  article(id: $id) {
    body
    id
    title
  }
}
';
        $this->assertEquals($expected1, (string) $query1);

        // query with variables and operation name
        $query2 = (new Query('article'))
            ->operationName('articlesQuery')
            ->variables([
                '$id' => 'Integer',
            ])
            ->arguments([
                'id' => '$id',
            ])
            ->fields([
                'id',
                'title',
                'body',
            ]);

        $expected2 =
'query articlesQuery($id: Integer) {
  article(id: $id) {
    body
    id
    title
  }
}
';
        $this->assertEquals($expected2, (string) $query2);

        // query with only operation name
        $query3 = (new Query('article'))
            ->operationName('articlesQuery')
            ->fields([
                'id',
                'title',
                'body',
            ]);

        $expected3 =
'query articlesQuery {
  article {
    body
    id
    title
  }
}
';
        $this->assertEquals($expected3, (string) $query3);
    }

    /**
     * Tests directives printing.
     */
    public function testDirective()
    {
        // skip if directive
        $query1 = (new Query('article'))
            ->operationName('articlesQuery')
            ->variables([
                '$withoutTags' => 'Boolean',
            ])
            ->fields([
                'id',
                'title',
                'body',
                'tags',
            ])
            ->skipIf([
                'tags' => '$withoutTags',
            ])
        ;

        $expected1 =
'query articlesQuery($withoutTags: Boolean) {
  article {
    body
    id
    tags @skip(if: $withoutTags)
    title
  }
}
';
        $this->assertEquals($expected1, (string) $query1);

        // include if directive
        $query2 = (new Query('article'))
            ->operationName('articlesQuery')
            ->variables([
                '$withTags' => 'Boolean!',
            ])
            ->fields([
                'id',
                'title',
                'body',
                'tags',
            ])
            ->includeIf([
                'tags' => '$withTags',
            ])
        ;

        $expected2 =
'query articlesQuery($withTags: Boolean!) {
  article {
    body
    id
    tags @include(if: $withTags)
    title
  }
}
';
        $this->assertEquals($expected2, (string) $query2);
    }

    /**
     * Tests sub query, with directive and alias.
     */
    public function testSubqueryWithAlias()
    {
        $query = (new Query('article'))
            ->operationName('articlesQuery')
            ->variables([
                '$withTags' => 'Boolean!',
            ])
            ->fields([
                'id',
                'title',
                'body',
                // sub query with type and alias
                'articleTags' => (new Query('tags'))->fields([
                    'id',
                    'tagTitle' => 'title',
                ]),
            ])
            ->includeIf([
                'articleTags' => '$withTags',
            ])
        ;

        $expected =
'query articlesQuery($withTags: Boolean!) {
  article {
    articleTags: tags @include(if: $withTags) {
      id
      tagTitle: title
    }
    body
    id
    title
  }
}
';
        $this->assertEquals($expected, (string) $query);
    }

    public function testSubqueryWithoutAlias()
    {
        $query = (new Query('article'))
            ->operationName('articlesQuery')
            ->variables([
                '$withTags' => 'Boolean!',
            ])
            ->fields([
                'id',
                'title',
                'body',
                // sub query without type parameter, so we take the alias
                'tags' => (new Query())->fields([
                    'id',
                    'tagTitle' => 'title',
                ]),
            ])
            ->includeIf([
                'tags' => '$withTags',
            ])
        ;

        $expected =
'query articlesQuery($withTags: Boolean!) {
  article {
    body
    id
    tags @include(if: $withTags) {
      id
      tagTitle: title
    }
    title
  }
}
';
        $this->assertEquals($expected, (string) $query);
    }

    public function testQueryWithFragment()
    {
        $query = (new Query('article'))
            ->operationName('articlesQuery')
            ->fields([
                'id',
                'title',
                'body',
                '...imageFragment',
            ])
            ->addFragment(new Fragment('imageFragment', 'image', [
                'height',
                'width',
                'filename',
                'size',
                'formats' => (new Query())->fields([
                    'id',
                    'name',
                    'url',
                ]),
            ]))
        ;

        $expected =
            'query articlesQuery {
  article {
    ...imageFragment
    body
    id
    title
  }
}

fragment imageFragment on image {
  filename
  formats {
    id
    name
    url
  }
  height
  size
  width
}
';
        $this->assertEquals($expected, (string) $query);
    }
}
