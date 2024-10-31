<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateInterval;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();
        $users = array_chunk($manager->getRepository(User::class)->findAll(), 5);
        $fakeText = $this->faker->paragraphs(5, true);

        $videoGames = [];
        for ($index = 0; $index < 50; $index++) {
            $videoGame = (new VideoGame())
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription($fakeText)
                ->setReleaseDate((new DateTimeImmutable())->sub(new DateInterval(sprintf('P%dD', $index))))
                ->setTest($fakeText)
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872);

            // Assign tags
            for ($tagIndex = 0; $tagIndex < 5; $tagIndex++) {
                $videoGame->getTags()->add($tags[($index + $tagIndex) % count($tags)]);
            }

            $videoGames[] = $videoGame;
            $manager->persist($videoGame);
        }

        $manager->flush();

        foreach ($videoGames as $index => $videoGame) {
            $filteredUsers = $users[$index % count($users)];

            foreach ($filteredUsers as $user) {
                $comment = $this->faker->paragraphs(1, true);

                $review = (new Review())
                    ->setUser($user)
                    ->setVideoGame($videoGame)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment($comment);

                $videoGame->getReviews()->add($review);
                $manager->persist($review);

                $this->calculateAverageRating->calculateAverage($videoGame);
                $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TagFixtures::class, UserFixtures::class];
    }
}
