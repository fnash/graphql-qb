<?php

namespace Tests\Fnash\GraphQL;

use Fnash\GraphQL\Mutation;
use Fnash\GraphQL\Query;
use PHPUnit\Framework\TestCase;

class MutationTest extends TestCase
{
    public function testMutation()
    {
        $mutation = Mutation::create('createReview')
            ->operationName('CreateReviewForEpisode')
            ->variables([
                '$ep' => 'Episode!',
                '$review' => 'ReviewInput!',
            ])
            ->arguments([
                'episode' => '$ep',
                'review' => '$review',
            ])
            ->fields([
                'stars',
                'commentary',
            ]);

        $expected =
'mutation CreateReviewForEpisode($ep: Episode!, $review: ReviewInput!) {
  createReview(episode: $ep, review: $review) {
    commentary
    stars
  }
}
';
        $this->assertEquals($expected, (string) $mutation);
    }


    /**
     * Tests operation name generation and printing.
     */
    public function testOperationName()
    {
        // simple mutation without variables nor operation name
        $mutation = Mutation::create('createReview')
            ->arguments([
                'episode' => 123,
                'review' => 'great review as string',
            ])
            ->fields([
                'stars',
                'commentary',
            ]);

        $expected =
'mutation {
  createReview(episode: 123, review: "great review as string") {
    commentary
    stars
  }
}
';

        $this->assertEquals($expected, (string) $mutation);


        // mutation with variables but no operation name
        $mutation = Mutation::create('createReview')
            ->variables([
                '$ep' => 'Episode!',
                '$review' => 'ReviewInput!',
            ])
            ->arguments([
                'episode' => '$ep',
                'review' => '$review',
            ])
            ->fields([
                'stars',
                'commentary',
            ]);

        $expected =
'mutation mutation_4db26a2e56d1146f7c7715edf0d1d5d55eeb261c($ep: Episode!, $review: ReviewInput!) {
  createReview(episode: $ep, review: $review) {
    commentary
    stars
  }
}
';

        $this->assertEquals($expected, (string) $mutation);
    }
}
