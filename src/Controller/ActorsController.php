<?php

namespace App\Controller;

use App\Entity\Actor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ActorsController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/actors', name: 'actors')]
    public function index(): Response
    {
        $repository = $this->em->getRepository(Actor::class);
        $actors = $repository->findAll();

        return $this->render('actors/index.html.twig', [
            'actors' => $actors,
        ]);
    }
}
