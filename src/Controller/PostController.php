<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\UserPostModifie;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post, EntityManagerInterface $em): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
            "history" => $em->getRepository(UserPostModifie::class)->findBy(["post" => $post], ["updated_at" => "DESC"]),
        ]);
    }
}
