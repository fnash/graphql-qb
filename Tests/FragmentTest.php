<?php

namespace Tests\Commadore\GraphQL;

use Commadore\GraphQL\Fragment;
use Commadore\GraphQL\Query;
use PHPUnit\Framework\TestCase;

class FragmentTest extends TestCase
{
    /**
     * Tests adding fields and args using constructor parameters or method call.
     */
    public function testAddFields()
    {
        $fragment1 = new Fragment('articleFragment', 'article', [
            'id',
            'title',
            'image' => (new Query())->fields([
                'width',
                'height',
                'filename',
                'size',
            ]),
        ]);

        $fragment2 = new Fragment('articleFragment', 'article', [
            'id',
            'title',
            'image' => (new Query())->fields([
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
        $fragment1 = new Fragment('articleFragment', 'article', [
            'id',
            'title',
            'image',
        ]);

        $fragment2 = (new Fragment('articleFragment', 'article'))->fields([
            'title',
            'image',
            'id',
        ]);

        $this->assertEquals((string) $fragment1, (string) $fragment2);
    }
}
