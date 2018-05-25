<?php

namespace Tests\Fnash\GraphQL;

use Fnash\GraphQL\Fragment;
use Fnash\GraphQL\Query;
use PHPUnit\Framework\TestCase;

class FragmentTest extends TestCase
{
    /**
     * Tests adding fields and args using constructor parameters or method call.
     */
    public function testAddFields()
    {
        $fragment1 = Fragment::create('articleFragment', 'article', [
           'id',
           'title',
           'image' => Query::create()->fields([
                'width',
                'height',
                'filename',
                'size',
           ]),
        ]);

        $fragment2 = Fragment::create('articleFragment', 'article')->fields([
           'id',
           'title',
           'image' => Query::create()->fields([
                'width',
                'height',
                'filename',
                'size',
           ]),
        ]);

        $expected =
            'fragment articleFragment on article {
  id
  image {
    filename
    height
    size
    width
  }
  title
}
';
        $this->assertEquals($expected, (string) $fragment1);
        $this->assertEquals($expected, (string) $fragment2);
    }

    /**
     * Tests the order of fields.
     */
    public function testSortFields()
    {
        $fragment1 = Fragment::create('articleFragment', 'article', [
            'id',
            'title',
            'image',
        ]);

        $fragment2 = Fragment::create('articleFragment', 'article')->fields([
            'title',
            'image',
            'id',
        ]);

        $this->assertEquals((string) $fragment1, (string) $fragment2);
    }
}
