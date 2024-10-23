<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\TestCase;

final class AverageRatingCalculatorTest extends TestCase
{
    /**
     * @dataProvider provideVideoGame
     */
    public function testShouldCalculateAverageRating(VideoGame $videoGame, ?int $expectedAverageRating): void
    {
        $ratingHandler = new RatingHandler();
        
        $ratingHandler->calculateAverage($videoGame);

        // Vérification que la moyenne correspond à la valeur attendue
        self::assertSame($expectedAverageRating, $videoGame->getAverageRating());
    }

    /**
     * Fournit des jeux vidéo avec différentes notes pour le calcul de la moyenne
     */
    public static function provideVideoGame(): iterable
    {
        yield 'No review' => [new VideoGame(), null];

        yield 'One review' => [self::createVideoGame(5), 5];

        yield 'A lot of reviews' => [
            self::createVideoGame(1, 2, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5),
            4,
        ];
    }

    /**
     * Crée un jeu vidéo avec une série de notes
     */
    private static function createVideoGame(int ...$ratings): VideoGame
    {
        $videoGame = new VideoGame();

        // Ajout des reviews au jeu vidéo
        foreach ($ratings as $rating) {
            $videoGame->getReviews()->add((new Review())->setRating($rating));
        }

        return $videoGame;
    }
}
