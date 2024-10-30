<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    /**
     * Fournit des cas de test pour tester la pagination et les filtres.
     *
     * @return iterable<string, array{
     *     query: array<string, mixed>,
     *     expectedCount: int,
     *     expectedOffsetFrom: int,
     *     expectedOffsetTo: int,
     *     expectedTotal: int,
     *     expectedPage: ?int,
     *     expectedPaginationLinks: string[],
     *     expectedVideoGames: string[]
     * }>
     */
    public static function provideUseCases(): iterable
    {
        // Cas de la première page sans filtre ni tag
        yield 'First page' => self::createUseCase();
        
        // Cas de la page 2 avec pagination
        yield 'Page #2' => self::createUseCase(
            query: ['page' => 2],
            expectedOffsetFrom: 11,
            expectedOffsetTo: 20,
            expectedPage: 2,
            expectedPaginationLinks: ['1', '2', '3', '4', '5'],
        );

        // Cas où aucun jeu ne correspond au tag non existant
        yield 'First page, filter by non-existent tag' => self::createUseCase(
            query: ['filter' => ['tags' => ['999']]],
            expectedCount: 0,
            expectedTotal: 0,
            expectedOffsetTo: 0,
            expectedVideoGames: []
        );

        // Cas avec aucun tag spécifié, où tous les jeux sont attendus
        yield 'First page, no tags specified' => self::createUseCase(
            query: ['filter' => ['tags' => []]],
            expectedCount: 10,
            expectedTotal: 50,
            expectedVideoGames: array_fill(0, 10, 'Jeu vidéo')
        );

        // Cas avec plusieurs tags, où un seul jeu est attendu
        yield 'First page, filter by many tags' => self::createUseCase(
            query: ['filter' => ['tags' => ['1', '2', '3', '4', '5']]],
            expectedCount: 1,
            expectedTotal: 1,
            expectedVideoGames: ['Jeu vidéo 0']
        );
    }

    /**
     * Teste la pagination et les filtres pour chaque cas fourni par `provideUseCases`.
     * Vérifie que la liste de jeux, la pagination et les autres informations
     * correspondent aux attentes de chaque cas de test.
     *
     * @param array<string, mixed> $query
     * @param string[] $expectedPaginationLinks
     * @param string[] $expectedVideoGames
     * @dataProvider provideUseCases
     */
    public function shouldShowVideoGamesByUseCase(
        array $query,
        int $expectedCount,
        int $expectedOffsetFrom,
        int $expectedOffsetTo,
        int $expectedTotal,
        ?int $expectedPage,
        array $expectedPaginationLinks,
        array $expectedVideoGames
    ): void {
        $this->get('/', $query);
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount($expectedCount);
        $this->assertListInfo($expectedCount, $expectedOffsetFrom, $expectedOffsetTo, $expectedTotal);
        $this->assertPagination($expectedPage, $expectedPaginationLinks);
        $this->assertVideoGameTitles($expectedVideoGames);
    }

    /**
     * Vérifie que le nombre de jeux vidéo affichés correspond au nombre attendu.
     *
     * @param int $expectedCount
     */
    private function assertVideoGameCount(int $expectedCount): void
    {
        self::assertSelectorCount($expectedCount, 'article.game-card');
    }

    /**
     * Vérifie que les informations de la liste (affichage du nombre de jeux et offset)
     * correspondent aux attentes.
     *
     * @param int $expectedCount
     * @param int $expectedOffsetFrom
     * @param int $expectedOffsetTo
     * @param int $expectedTotal
     */
    private function assertListInfo(int $expectedCount, int $expectedOffsetFrom, int $expectedOffsetTo, int $expectedTotal): void
    {
        self::assertSelectorTextSame(
            'div.list-info',
            sprintf(
                'Affiche %d jeux vidéo de %d à %d sur les %d jeux vidéo',
                $expectedCount,
                $expectedOffsetFrom,
                $expectedOffsetTo,
                $expectedTotal
            )
        );
    }

    /**
     * Vérifie que les liens de pagination et la page active correspondent aux attentes.
     *
     * @param ?int $expectedPage
     * @param string[] $expectedPaginationLinks
     */
    private function assertPagination(?int $expectedPage, array $expectedPaginationLinks): void
    {
        if ($expectedPage === null) {
            self::assertSelectorNotExists('nav[aria-label="Pagination"]');
        } else {
            self::assertSelectorTextSame('li.page-item.active', (string) $expectedPage);
            self::assertSelectorCount(count($expectedPaginationLinks), 'li.page-item');
            foreach ($expectedPaginationLinks as $expectedPaginationLink) {
                self::assertSelectorExists(sprintf('li.page-item[aria-label="%s"]', $expectedPaginationLink));
            }
        }
    }

    /**
     * Vérifie que les titres de jeux vidéo affichés correspondent aux attentes.
     *
     * @param string[] $expectedVideoGames
     */
    private function assertVideoGameTitles(array $expectedVideoGames): void
    {
        foreach (array_values($expectedVideoGames) as $index => $expectedVideoGame) {
            self::assertSelectorTextSame(
                sprintf('article.game-card:nth-child(%d) h5.game-card-title a', $index + 1),
                $expectedVideoGame
            );
        }
    }

    /**
     * Teste le tri des jeux vidéo selon les paramètres donnés (par titre et ordre croissant).
     */
    public function testShouldSortVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount(10);

        $this->submit('Trier', ['limit' => 25, 'sorting' => 'Title', 'direction' => 'Ascending'], 'GET');
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount(25);
    }

    /**
     * Teste le filtrage des jeux vidéo par recherche de titre.
     */
    public function testShouldFilterBySearchVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount(10);

        $this->submit('Filtrer', ['filter[search]' => 'Jeu vidéo 0'], 'GET');
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount(1);
        $this->assertVideoGameTitles(['Jeu vidéo 0']);
    }

    /**
     * Teste le filtrage des jeux vidéo par tags multiples.
     */
    public function testShouldFilterByTagsVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount(10);

        $this->submit(
            'Filtrer',
            [
                'filter[tags][0]' => '1',
                'filter[tags][1]' => '2',
                'filter[tags][2]' => '3',
                'filter[tags][3]' => '4',
                'filter[tags][4]' => '5',
            ],
            'GET'
        );
        self::assertResponseIsSuccessful();
        $this->assertVideoGameCount(2);
        $this->assertVideoGameTitles(['Jeu vidéo 0', 'Jeu vidéo 25']);
    }

    /**
     * Crée un cas de test avec les valeurs de paramètres données.
     * Utilisé pour générer différents cas pour `provideUseCases`.
     *
     * @param array<string, mixed> $query
     * @param int $expectedCount
     * @param int $expectedOffsetFrom
     * @param int $expectedOffsetTo
     * @param int $expectedTotal
     * @param ?int $expectedPage
     * @param null|string[] $expectedPaginationLinks
     * @param null|string[] $expectedVideoGames
     * @return array{
     *     query: array<string, mixed>,
     *     expectedCount: int,
     *     expectedOffsetFrom: int,
     *     expectedOffsetTo: int,
     *     expectedTotal: int,
     *     expectedPage: int|null,
     *     expectedPaginationLinks: string[],
     *     expectedVideoGames: string[]
     * }
     */
    private static function createUseCase(
        array $query = [],
        int $expectedCount = 10,
        int $expectedOffsetFrom = 1,
        int $expectedOffsetTo = 10,
        int $expectedTotal = 50,
        ?int $expectedPage = 1,
        ?array $expectedPaginationLinks = null,
        ?array $expectedVideoGames = null
    ): array {
        if ($expectedPage !== null) {
            $expectedPaginationLinks = $expectedPaginationLinks ?? ['1', '2', '3', '4'];

            if ($expectedPage > 1) {
                $expectedPaginationLinks = array_merge(['Première page', 'Précédent'], $expectedPaginationLinks);
            }

            if ($expectedCount > 0 && $expectedPage < ceil($expectedTotal / $expectedCount)) {
                $expectedPaginationLinks = array_merge($expectedPaginationLinks, ['Suivant', 'Dernière page']);
            }
        }

        return [
            'query' => $query,
            'expectedCount' => $expectedCount,
            'expectedOffsetFrom' => $expectedOffsetFrom,
            'expectedOffsetTo' => $expectedOffsetTo,
            'expectedTotal' => $expectedTotal,
            'expectedPage' => $expectedPage,
            'expectedPaginationLinks' => $expectedPaginationLinks ?? [],
            'expectedVideoGames' => $expectedVideoGames ?? array_fill_callback(
                $expectedOffsetFrom - 1,
                $expectedCount,
                static fn (int $index) => sprintf('Jeu vidéo %d', $index)
            )
        ];
    }
}
