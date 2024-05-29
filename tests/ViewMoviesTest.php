<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Twig\Components\SearchDropdown;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class ViewMoviesTest extends ApiTestCase
{
    public function test_the_index_page_shows_movies(): void
    {
        $mockedPopularMoviesData = $this->getFakePopularMoviesData();
        $mockedNowPlayingMoviesData = $this->getFakeNowPlayingMoviesData();
        $mockedGenreData = $this->getFakeGenreData();

        $responses = [
            'https://api.themoviedb.org/3/movie/popular' => new MockResponse(json_encode($mockedPopularMoviesData), ['http_code' => 200]),
            'https://api.themoviedb.org/3/movie/now_playing' => new MockResponse(json_encode($mockedNowPlayingMoviesData), ['http_code' => 200]),
            'https://api.themoviedb.org/3/genre/movie/list' => new MockResponse(json_encode($mockedGenreData), ['http_code' => 200])
        ];

        $mockHttpClient = new MockHttpClient(function ($method, $url) use ($responses) {
            return $responses[$url] ?? new MockResponse('', ['http_code' => 404]);
        });

        self::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client = static::createClient();

        $client->request('GET', '/movies');

        $responsePopular = $mockHttpClient->request('GET', 'https://api.themoviedb.org/3/movie/popular');
        $this->assertResponseIsSuccessful();
        $contentPopular = $responsePopular->getContent();
        $this->assertStringContainsString('Fake Popular Movie', $contentPopular);
//        $this->assertStringContainsString('Science Fiction, Action, Adventure', $contentPopular);

        $responseNowPlaying = $mockHttpClient->request('GET', 'https://api.themoviedb.org/3/movie/now_playing');
        $this->assertResponseIsSuccessful();
        $contentNowPlaying = $responseNowPlaying->getContent();
        $this->assertStringContainsString('Now Playing Fake Movie', $contentNowPlaying);

//        $responseGenres = $mockHttpClient->request('GET', 'https://api.themoviedb.org/3/genre/movie/list');
//        $this->assertResponseIsSuccessful();
//        $contentGenres = $responseGenres->getContent();
//        $this->assertStringContainsString('Science Fiction, Action, Adventure', $contentGenres);
    }

    public function test_the_movie_page_shows_specific_info(): void
    {
        $mockedSingleMovieData = $this->getFakeSingleMovieData();

        $response = new MockResponse(json_encode($mockedSingleMovieData), ['http_code' => 200]);
        $mockHttpClient = new MockHttpClient($response);

        self::getContainer()->set(HttpClientInterface::class, $mockHttpClient);

        $client = static::createClient();
        $client->request('GET', '/movies/653346');

        $responseMovieDetails = $mockHttpClient->request('GET', '/movies/653346');

        $this->assertResponseIsSuccessful();
        $contentMovieDetails = $responseMovieDetails->getContent();
        $this->assertStringContainsString('Fake Kingdom', $contentMovieDetails);
        $this->assertStringContainsString('Wes Ball', $contentMovieDetails);
        $this->assertStringContainsString('Owen Teague', $contentMovieDetails);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function test_the_search_dropdown_works(): void
    {
        $response = new MockResponse(json_encode($this->getFakeSearchMovies()), ['http_code' => 200]);
        $httpClient = new MockHttpClient($response);

        $component = new SearchDropdown($httpClient);

        // Step 2: Simulate the Live Component interaction
        $component->query = 'jumanji';
        $results = $component->getSearchResults();

        $this->assertCount(7, $results, 'There should be 7 results');
        $this->assertSame('Jumanji', $results['original_title']);
    }

    /**
     * @return array[]
     */
    public function getFakePopularMoviesData(): array
    {
        return [
            'results' => [
                [
                    "adult" => false,
                    "backdrop_path" => "/qrGtVFxaD8c7et0jUtaYhyTzzPg.jpg",
                    "genre_ids" => [878, 28, 12],
                    "id" => 823464,
                    "original_language" => "en",
                    "original_title" => "Fake Popular Movie",
                    "overview" => "Fake popular movie desc. Following their explosive showdown, Godzilla and Kong must reunite against a colossal undiscovered threat hidden within our world, challenging their very existence – and our own.",
                    "popularity" => 8350.714,
                    "poster_path" => "/z1p34vh7dEOnLDmyCrlUVLuoDzd.jpg",
                    "release_date" => "2024-03-27",
                    "title" => "Fake Popular Movie",
                    "video" => false,
                    "vote_average" => 7.123,
                    "vote_count" => 1609
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getFakeNowPlayingMoviesData(): array
    {
        return [
            'results' => [
                "adult" => false,
                "backdrop_path" => "/qrGtVFxaD8c7et0jUtaYhyTzzPg.jpg",
                "genre_ids" => [878, 28, 12],
                "id" => 823464,
                "original_language" => "en",
                "original_title" => "Now Playing Fake Movie",
                "overview" => "Now playing fake movie. Following their explosive showdown, Godzilla and Kong must reunite against a colossal undiscovered threat hidden within our world, challenging their very existence – and our own.",
                "popularity" => 8350.714,
                "poster_path" => "/z1p34vh7dEOnLDmyCrlUVLuoDzd.jpg",
                "release_date" => "2024-03-27",
                "title" => "Now Playing Fake Movie",
                "video" => false,
                "vote_average" => 7.124,
                "vote_count" => 1628,
            ]
        ];
    }

    /**
     * @return array[]
     */
    private function getFakeGenreData(): array
    {
        return [
            'genres' => [
                [
                    "id" => 28,
                    "name" => "Action"
                ],
                [
                    "id" => 12,
                    "name" => "Adventure"
                ],
                [
                    "id" => 16,
                    "name" => "Animation"
                ],
                [
                    "id" => 35,
                    "name" => "Comedy"
                ],
                [
                    "id" => 80,
                    "name" => "Crime"
                ],
                [
                    "id" => 99,
                    "name" => "Documentary"
                ],
                [
                    "id" => 18,
                    "name" => "Drama"
                ],
                [
                    "id" => 10751,
                    "name" => "Family"
                ],
                [
                    "id" => 14,
                    "name" => "Fantasy"
                ],
                [
                    "id" => 36,
                    "name" => "History"
                ],
                [
                    "id" => 27,
                    "name" => "Horror"
                ],
                [
                    "id" => 10402,
                    "name" => "Music"
                ],
                [
                    "id" => 9648,
                    "name" => "Mystery"
                ],
                [
                    "id" => 10749,
                    "name" => "Romance"
                ],
                [
                    "id" => 878,
                    "name" => "Science Fiction"
                ],
                [
                    "id" => 10770,
                    "name" => "TV Movie"
                ],
                [
                    "id" => 53,
                    "name" => "Thriller"
                ],
                [
                    "id" => 10752,
                    "name" => "War"
                ],
                [
                    "id" => 37,
                    "name" => "Western"
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    private function getFakeSingleMovieData(): array
    {
        return [
            "adult" => false,
            "backdrop_path" => "/fypydCipcWDKDTTCoPucBsdGYXW.jpg",
            "belongs_to_collection" => [
                "id" => 173710,
                "name" => "Planet of the Apes (Reboot) Collection",
                "poster_path" => "/afGkMC4HF0YtXYNkyfCgTDLFe6m.jpg",
                "backdrop_path" => "/2ZkvqfOJUCINozB00wmYuGJQW81.jpg"
            ],
            "budget" => 160000000,
            "genres" => [
                ["id" => 878, "name" => "Science Fiction"],
                ["id" => 12, "name" => "Adventure"],
                ["id" => 28, "name" => "Action"]
            ],
            "homepage" => "https://www.20thcenturystudios.com/movies/kingdom-of-the-planet-of-the-apes",
            "id" => 653346,
            "imdb_id" => "tt11389872",
            "origin_country" => ["US"],
            "original_language" => "en",
            "original_title" => "Fake Kingdom of the Planet of the Apes",
            "overview" => "Several generations in the future following Caesar's reign, apes are now the dominant species and live harmoniously while humans have been reduced to living in ",
            "popularity" => 1802.132,
            "poster_path" => "/gKkl37BQuKTanygYQG1pyYgLVgf.jpg",
            "production_companies" => [
                [
                    "id" => 127928,
                    "logo_path" => "/h0rjX5vjW5r8yEnUBStFarjcLT4.png",
                    "name" => "20th Century Studios",
                    "origin_country" => "US",
                ],
                [
                    "id" => 133024,
                    "logo_path" => null,
                    "name" => "Oddball Entertainment",
                    "origin_country" => "US"
                ],
                [
                    "id" => 89254,
                    "logo_path" => null,
                    "name" => "Jason T. Reed Productions",
                    "origin_country" => "US"
                ]
            ],
            "production_countries" => [
                "iso_3166_1" => "US",
                "name" => "United States of America"
            ],
            "release_date" => "2024-05-08",
            "revenue" => 145000000,
            "runtime" => 145,
            "spoken_languages" => [],
            "status" => "Released",
            "tagline" => "No one can stop the reign.",
            "title" => "Fake Kingdom of the Planet of the Apes",
            "video" => false,
            "vote_average" => 7.201,
            "vote_count" => 388,
            "credits" => [
                "cast" => [
                    [
                        "adult" => false,
                        "gender" => 2,
                        "id" => 1586047,
                        "known_for_department" => "Acting",
                        "name" => "Owen Teague",
                        "original_name" => "Owen Teague",
                        "popularity" => 36.845,
                        "profile_path" => "/tgCkGE0LIggyjMmgSwHhpZAkfJs.jpg",
                        "cast_id" => 9,
                        "character" => "Noa",
                        "credit_id" => "630449a821118f007d331afa",
                        "order" => 0
                    ]
                ],
                "crew" => [
                    [
                        "adult" => false,
                        "gender" => 2,
                        "id" => 1179066,
                        "known_for_department" => "Directing",
                        "name" => "Wes Ball",
                        "original_name" => "Wes Ball",
                        "popularity" => 25.125,
                        "profile_path" => "/zVPXrhuAxYAWlwDEWCaqeUPycFx.jpg",
                        "credit_id" => "5de6f63611386c001354710d",
                        "department" => "Directing",
                        "job" => "Director"
                    ]
                ]
            ],
            "videos" => [
                "results" => [
                    [
                        "iso_639_1" => "en",
                        "iso_3166_1" => "US",
                        "name" => "No.1 Review",
                        "key" => "dNYgq3BfnsY",
                        "site" => "YouTube",
                        "size" => 1080,
                        "type" => "Teaser",
                        "official" => true,
                        "published_at" => "2024-05-16T04:00:21.000Z",
                        "id" => "66459c85e68b7c68cb782dab"
                    ]
                ]
            ],
            "images" => [
                "backdrops" => [
                    [
                        "aspect_ratio" => 1.778,
                        "height" => 2160,
                        "iso_639_1" => null,
                        "file_path" => "/fypydCipcWDKDTTCoPucBsdGYXW.jpg",
                        "vote_average" => 5.39,
                        "vote_count" => 6,
                        "width" => 3840
                    ]
                ],
                "logos" => [
                    [
                        "aspect_ratio" => 1.909,
                        "height" => 1688,
                        "iso_639_1" => "en",
                        "file_path" => "/odfQQNwtLKPlTPahXUPreSUT0YV.png",
                        "vote_average" => 5.384,
                        "vote_count" => 2,
                        "width" => 3223
                    ]
                ],
                "posters" => [
                    [
                        "aspect_ratio" => 0.667,
                        "height" => 3000,
                        "iso_639_1" => "en",
                        "file_path" => "/gKkl37BQuKTanygYQG1pyYgLVgf.jpg",
                        "vote_average" => 5.224,
                        "vote_count" => 32,
                        "width" => 2000
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array[]
     */
    private function getFakeSearchMovies(): array
    {
        return [
            'results' => [
                "adult" => false,
                "backdrop_path" => "/i7jKGJZjGmLZFt48xLtwSgn9Cdw.jpg",
                "genre_ids" => [12, 14, 10751],
                "id" => 8844,
                "original_language" => "en",
                "original_title" => "Jumanji",
                "overview" => "When siblings Judy and Peter discover an enchanted board game that opens the door to a magical world, they unwittingly invite Alan -- an adult who's been trapped inside the game for 26 years -- into their living room. Alan's only hope for freedom is to finish the game, which proves risky as all three find themselves running from giant rhinoceroses, evil monkeys and other terrifying creatures.",
                "popularity" => 24.314,
                "poster_path" => "/vgpXmVaVyUL7GGiDeiK1mKEKzcX.jpg",
                "release_date" => "1995-12-15",
                "title" => "Jumanji",
                "video" => false,
                "vote_average" => 7.24,
                "vote_count" => 10229
            ]
        ];
    }
}
