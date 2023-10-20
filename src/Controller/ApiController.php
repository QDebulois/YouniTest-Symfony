<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class ApiController extends AbstractController
{
    #[Route('/user', name: 'app_api_user', methods: ["POST", "GET", "PATCH", "DELETE"])]
    public function apiUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $uph): JsonResponse
    {
        $USER_POST = function(string $payload, EntityManagerInterface $em, UserPasswordHasherInterface $uph) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("email", $payload) || !array_key_exists("password", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            if ($em->getRepository(User::class)->findOneBy(["email" => $payload["email"]])) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = new User();
            $hashedPassword = $uph->hashPassword(
                $user,
                $payload["password"],
            );
            $user->setEmail($payload["email"]);
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTimeImmutable());
            $em->persist($user);
            $em->flush();
            $body = $user ? [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ] : null;
            return ["statusCode" => 201, "body" => $body];
        };
        $USER_GET = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            $body = $user ? [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ] : null;
            return ["statusCode" => 200, "body" => $body];
        };
        $USER_PATCH = function(string $payload, EntityManagerInterface $em, UserPasswordHasherInterface $uph) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            if (!$user) {
                return ["statusCode" => 400, "body" => null];
            }
            unset($payload["id"]);
            foreach ($payload as $key => $value) {
                $setter = "set".ucfirst($key);
                if (method_exists($user, $setter)) {
                    if ($key === "password") {
                        $hashedPassword = $uph->hashPassword(
                            $user,
                            $value,
                        );
                        $user->$setter($hashedPassword);
                    } else {
                        $user->$setter($value);
                    }
                }
            }
            $em->persist($user);
            $em->flush();
            $body = [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ];
            if (array_key_exists("password", $payload)) {
                array_push($body, ["password" => $user->getPassword()]);
            }
            return ["statusCode" => 200, "body" => $body];
        };
        $USER_DELETE = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            if (!$user) {
                return ["statusCode" => 400, "body" => null];
            }
            $em->remove($user);
            $em->flush();
            $body = [
                "id" => $payload["id"],
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ];
            return ["statusCode" => 200, "body" => $body];
        };
        $action = match($request->getMethod()) {
            "POST" => $USER_POST($request->getContent(), $em, $uph),
            "GET" => $USER_GET($request->getContent(), $em),
            "PATCH" => $USER_PATCH($request->getContent(), $em, $uph),
            "DELETE" => $USER_DELETE($request->getContent(), $em),
            default => ["statusCode" => 405, "body" => null]
        };
        $res = new JsonResponse($action["body"]);
        $res->setStatusCode($action["statusCode"]);
        return $res;
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/post', name: 'app_api_post', methods:["POST", "GET", "PATCH", "DELETE"])]
    public function apiPost(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $POST_POST = function(string $payload, EntityManagerInterface $em) {
            $user = $this->getUser();
            dump($user);
            if (!$user) {
                return ["statusCode" => 401, "body" => null];
            }
            $payload = json_decode($payload, true);
            if (!array_key_exists("category_id", $payload)
                || !$em->getRepository(Category::class)->findOneBy(["id" => $payload["category_id"]])
            ) {
                return ["statusCode" => 400, "body" => null];
            }
            $post = new Post();
            $post->setUser($user);
            $post->setCategory($em->getRepository(Category::class)->findOneBy(["id" => $payload["category_id"]]));
            $post->setTitle($payload["title"] ?? "Pas de titre");
            $post->setText($payload["text"] ?? "Pas de texte");
            $post->setCreatedAt(new \DateTimeImmutable());
            $em->persist($post);
            $em->flush();
            $body = $post ? [
                "id" => $post->getId(),
                "email" => $post->getEmail(),
                "roles" => $post->getRoles(),
                "created_at" => $post->getCreatedAt(),
            ] : null;
            return ["statusCode" => 201, "body" => $body];
        };
        $POST_GET = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $post = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            $body = $post ? [
                "id" => $post->getId(),
                "email" => $post->getEmail(),
                "roles" => $post->getRoles(),
                "created_at" => $post->getCreatedAt(),
            ] : null;
            return ["statusCode" => 200, "body" => $body];
        };
        $POST_PATCH = function(string $payload, EntityManagerInterface $em) {
            $user = $this->getUser();
            if (!$user) {
                return ["statusCode" => 401, "body" => null];
            }
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $post = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            if (!$post) {
                return ["statusCode" => 400, "body" => null];
            }
            unset($payload["id"]);
            foreach ($payload as $key => $value) {
                $setter = "set".ucfirst($key);
                if (method_exists($post, $setter)) {
                    $post->$setter($value);
                }
            }
            $em->persist($post);
            $em->flush();
            $body = [
                "id" => $post->getId(),
                "email" => $post->getEmail(),
                "roles" => $post->getRoles(),
                "created_at" => $post->getCreatedAt(),
            ];
            if (array_key_exists("password", $payload)) {
                array_push($body, ["password" => $post->getPassword()]);
            }
            return ["statusCode" => 200, "body" => $body];
        };
        $POST_DELETE = function(string $payload, EntityManagerInterface $em) {
            $user = $this->getUser();
            if (!$user) {
                return ["statusCode" => 401, "body" => null];
            }
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $post = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            if (!$post) {
                return ["statusCode" => 400, "body" => null];
            }
            $em->remove($post);
            $em->flush();
            $body = [
                "id" => $payload["id"],
                "email" => $post->getEmail(),
                "roles" => $post->getRoles(),
                "created_at" => $post->getCreatedAt(),
            ];
            return ["statusCode" => 200, "body" => $body];
        };
        $action = match($request->getMethod()) {
            "POST" => $POST_POST($request->getContent(), $em),
            "GET" => $POST_GET($request->getContent(), $em),
            "PATCH" => $POST_PATCH($request->getContent(), $em),
            "DELETE" => $POST_DELETE($request->getContent(), $em),
            default => ["statusCode" => 405, "body" => null]
        };
        $res = new JsonResponse($action["body"]);
        $res->setStatusCode($action["statusCode"]);
        return $res;
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/category', name: 'app_api_category')]
    public function apiCategory(Request $request): JsonResponse
    {
        $USER_POST = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("email", $payload) || !array_key_exists("password", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            if ($em->getRepository(User::class)->findOneBy(["email" => $payload["email"]])) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = new User();
            $user->setEmail($payload["email"]);
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTimeImmutable());
            $em->persist($user);
            $em->flush();
            $body = $user ? [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ] : null;
            return ["statusCode" => 201, "body" => $body];
        };
        $USER_GET = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            $body = $user ? [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ] : null;
            return ["statusCode" => 200, "body" => $body];
        };
        $USER_PATCH = function(string $payload, EntityManagerInterface $em, UserPasswordHasherInterface $uph) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            if (!$user) {
                return ["statusCode" => 400, "body" => null];
            }
            unset($payload["id"]);
            foreach ($payload as $key => $value) {
                $setter = "set".ucfirst($key);
                if (method_exists($user, $setter)) {
                    if ($key === "password") {
                        $hashedPassword = $uph->hashPassword(
                            $user,
                            $value,
                        );
                        $user->$setter($hashedPassword);
                    } else {
                        $user->$setter($value);
                    }
                }
            }
            $em->persist($user);
            $em->flush();
            $body = [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ];
            if (array_key_exists("password", $payload)) {
                array_push($body, ["password" => $user->getPassword()]);
            }
            return ["statusCode" => 200, "body" => $body];
        };
        $USER_DELETE = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("id", $payload)) {
                return ["statusCode" => 400, "body" => null];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["id"]]);
            if (!$user) {
                return ["statusCode" => 400, "body" => null];
            }
            $em->remove($user);
            $em->flush();
            $body = [
                "id" => $payload["id"],
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ];
            return ["statusCode" => 200, "body" => $body];
        };
        $action = match($request->getMethod()) {
            "POST" => $USER_POST($request->getContent(), $em, $uph),
            "GET" => $USER_GET($request->getContent(), $em),
            "PATCH" => $USER_PATCH($request->getContent(), $em, $uph),
            "DELETE" => $USER_DELETE($request->getContent(), $em),
            default => ["statusCode" => 405, "body" => null]
        };
        $res = new JsonResponse($action["body"]);
        $res->setStatusCode($action["statusCode"]);
        return $res;
    }

    #[Route('/user/login', name: 'app_api_login')]
    public function apiUserLogin(): JsonResponse
    {
        // Géré par JWT -> config/packages/security.yaml
        // Header "content-type: application/json" obligatoire pour être trigger
        $res = new JsonResponse();
        $res->setStatusCode(400);
        return $res;
    }
}
