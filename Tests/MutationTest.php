<?php

namespace Tests\Fnash\GraphQL;

use Fnash\GraphQL\Mutation;
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
}
