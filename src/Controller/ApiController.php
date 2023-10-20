<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            "message" => "index",
        ]);
    }

    #[Route('/api/user', name: 'app_api_user')]
    public function apiUser(): JsonResponse
    {
        return new JsonResponse([
            "message" => "user",
        ]);
    }

    #[Route('/api/post', name: 'app_api_post')]
    public function apiPost(): JsonResponse
    {
        return new JsonResponse([
            "message" => "post",
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/api/category', name: 'app_api_category')]
    public function apiCategory(): JsonResponse
    {
        return new JsonResponse([
            "message" => "category",
        ]);
    }

    #[Route('/api/user/login', name: 'app_api_login')]
    public function apiUserLogin(): JsonResponse
    {
        // Géré par JWT -> config/packages/security.yaml
    }
}
