<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Controller extends AbstractController
{

    public function __construct(protected ManagerRegistry $registery){

    }
    
    #[Route('/', name: 'home')]
    public function index(): Response
    {

        $articleRegistery = $this->registery->getRepository(Article::class);
        $articles = $articleRegistery->findBy(
            ['statut' => 1],
            ['createdAt' => 'DESC'],
            3,
            null
        );
        
        if (!$articles) {
            throw $this->createNotFoundException(
                'No product found'
            );
        }

        return $this->render('/Home/index.html.twig', [
            'controller_name' => 'Controller',
            'articles' => $articles
        ]);
    }
}
