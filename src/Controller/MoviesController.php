<?php

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MoviesController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/movies', name: 'movies')]
    public function index(EntityManagerInterface $manager): Response
    {
        $repository = $this->em->getRepository(Movie::class);
        $movies = $repository->findAll();

        return $this->render('index.html.twig', [
            'movies' => $movies
        ]);
    }
}
