<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MoviesController extends AbstractController
{
    private EntityManagerInterface $em;
    private MovieRepository $movieRepository;
    private Filesystem $filesystem;

    public function __construct(EntityManagerInterface $em, MovieRepository $movieRepository, Filesystem $filesystem)
    {
        $this->em = $em;
        $this->movieRepository = $movieRepository;
        $this->filesystem = $filesystem;
    }

    #[Route('/movies', name: 'movies', methods: ['GET'])]
    public function index(): Response
    {
        $movies = $this->movieRepository->findAll();

        return $this->render('movies/index.html.twig', [
            'movies' => $movies
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
        $movie = $this->movieRepository->find($id);

        return $this->render('movies/show.html.twig', [
            'movie' => $movie
        ]);
    }

    #[Route('/movies/edit/{id}', name: 'edit_movie')]
    public function edit(int $id, Request $request): Response
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
                        if ($this->filesystem->exists($currentImagePath)) {
                            $this->filesystem->remove($currentImagePath);
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
    public function delete(int $id): Response
    {
        $movie = $this->movieRepository->find($id);

        if ($movie) {
            $imagePath = $movie->getImagePath();

            if ($imagePath) {
                $absoluteImagePath = $this->getParameter('kernel.project_dir') . '/public' . $imagePath;

                // Check if the image already exists and delete it
                if ($this->filesystem->exists($absoluteImagePath)) {
                    $this->filesystem->remove($absoluteImagePath);
                }
            }
        }

        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies');
    }
}
