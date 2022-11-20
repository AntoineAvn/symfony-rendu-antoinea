<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/blog')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository,Request $request, PaginatorInterface $paginator): Response
    {

        $articles = $articleRepository->findBy(
            ['statut' => 1],
            ['createdAt' => 'DESC'],
            null,
            null
        );

        $articles = $paginator->paginate(
            $articles,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/profil/public/{id}', name: 'app_article_showPublicByUser', methods: ['GET'])]
    public function showPublicByUser(ArticleRepository $articleRepository, $id): Response
    {

        //Get articles by user_id && when statut is Published (=1)
        $articles = $articleRepository->findBy(
            array('user' => $id, 'statut' => 1),
            ['createdAt' => 'DESC'],
            null,
            null
        );

        return $this->render('article/showByUser.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/profil/private/{id}', name: 'app_article_showPrivateByUser', methods: ['GET'])]
    public function showPrivateByUser(ArticleRepository $articleRepository, $id): Response
    {

        //Get articles by user_id
        $articles = $articleRepository->findBy(
            ['user' => $id],
            ['createdAt' => 'DESC'],
            null,
            null
        );

        return $this->render('article/showByUser.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticleRepository $articleRepository): Response
    {
        // Get user object of the user connected
        $user = $this->getUser();

        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Default field (dateTime, user who create the project)
            $article->setCreatedAt(new \DateTime())->setUser($user);

            $articleRepository->save($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->save($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/{user_id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository, $user_id): Response
    {
        if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $articleRepository->remove($article, true);
        }

        return $this->redirectToRoute('app_article_showPrivateByUser', ['id' => $user_id], Response::HTTP_SEE_OTHER);
    }
}
