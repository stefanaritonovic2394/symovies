<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MoviesController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var MovieRepository
     */
    private MovieRepository $movieRepository;

    public function __construct(EntityManagerInterface $em, MovieRepository $movieRepository)
    {
        $this->em = $em;
        $this->movieRepository = $movieRepository;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/movies', name: 'movies', methods: ['GET'])]
    public function index(): Response
    {
        $httpClient = HttpClient::create();

        $popularMovies = $httpClient->request('GET', 'https://api.themoviedb.org/3/movie/popular', [
            'query' => [
                'api_key' => $this->getParameter('tmdb_api_key')
            ]
        ])->toArray()['results'];

        $nowPlayingMovies = $httpClient->request('GET', 'https://api.themoviedb.org/3/movie/now_playing', [
            'query' => [
                'api_key' => $this->getParameter('tmdb_api_key')
            ]
        ])->toArray()['results'];

        $genresArray =  $httpClient->request('GET', 'https://api.themoviedb.org/3/genre/movie/list', [
            'query' => [
                'api_key' => $this->getParameter('tmdb_api_key')
            ]
        ])->toArray()['genres'];

        $genres = array_reduce($genresArray, function ($result, $genre) {
            $result[$genre['id']] = $genre['name'];
            return $result;
        }, []);

//        $genres = array_column($genresArray, 'name', 'id');

        return $this->render('movies/index.html.twig', [
            'popularMovies' => $popularMovies,
            'nowPlayingMovies' => $nowPlayingMovies,
            'genres' => $genres
        ]);
    }

    #[Route('/movies/create', name: 'create_movie')]
    public function create(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newMovie = $form->getData();
            $currentDate = new DateTime();

            $imagePath = $form->get('imagePath')->getData();

            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $newMovie->setCreatedAt($currentDate);
                $newMovie->setImagePath('/uploads/' . $newFileName);
            }

            $this->em->persist($newMovie);
            $this->em->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('movies/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/movies/{id}', name: 'show_movie', methods: ['GET'])]
    public function show(int $id): Response
    {
        $httpClient = HttpClient::create();

        $movie = $httpClient->request('GET', 'https://api.themoviedb.org/3/movie/' . $id . '?append_to_response=credits,videos,images', [
            'query' => [
                'api_key' => $this->getParameter('tmdb_api_key')
            ]
        ])->toArray();

        return $this->render('movies/show.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route('/movies/edit/{id}', name: 'edit_movie')]
    public function edit(int $id, Request $request, Filesystem $filesystem): Response
    {
        $movie = $this->movieRepository->find($id);
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('imagePath')->getData();
        $currentImagePath = $this->getParameter('kernel.project_dir') . '/public' . $movie->getImagePath();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                if ($movie->getImagePath() !== null) {
                    if (file_exists(
                        $currentImagePath
                    )) {
                        $currentImagePath = $this->getParameter('kernel.project_dir') . '/public' . $movie->getImagePath();

                        // Check if the image already exists and delete it
                        if ($filesystem->exists($currentImagePath)) {
                            $filesystem->remove($currentImagePath);
                        }

                        $this->getParameter('kernel.project_dir') . $movie->getImagePath();
                        $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                        try {
                            $imagePath->move(
                                $this->getParameter('kernel.project_dir') . '/public/uploads',
                                $newFileName
                            );
                        } catch (FileException $e) {
                            return new Response($e->getMessage());
                        }

                        $movie->setImagePath('/uploads/' . $newFileName);
                        $this->em->flush();

                        return $this->redirectToRoute('movies');
                    }
                }
            } else {
                $movie->setTitle($form->get('title')->getData());
                $movie->setReleaseYear($form->get('releaseYear')->getData());
                $movie->setDescription($form->get('description')->getData());

                $this->em->flush();

                return $this->redirectToRoute('movies');
            }
        }

        return $this->render('movies/edit.html.twig', [
            'movie' => $movie,
            'form' => $form->createView()
        ]);
    }

    #[Route('/movies/delete/{id}', name: 'delete_movie', methods: ['GET', 'DELETE'])]
    public function delete(int $id, Filesystem $filesystem): Response
    {
        $movie = $this->movieRepository->find($id);

        if ($movie) {
            $imagePath = $movie->getImagePath();

            if ($imagePath) {
                $absoluteImagePath = $this->getParameter('kernel.project_dir') . '/public' . $imagePath;

                // Check if the image already exists and delete it
                if ($filesystem->exists($absoluteImagePath)) {
                    $filesystem->remove($absoluteImagePath);
                }
            }
        }

        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies');
    }

    #[Route('/search', name: 'search_movie', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $title = $request->query->get('query');
        $moviesByTitle = $this->movieRepository->findByTitleField($title);

        return $this->render('movies/search.html.twig', [
            'movies' => $moviesByTitle
        ]);
    }
}
