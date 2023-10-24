<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\UserPostModifie;
use DateTimeImmutable;
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
    #[Route('/user/login', name: 'app_api_login')]
    public function apiUserLogin(): JsonResponse
    {
        // Géré par JWT -> config/packages/security.yaml, username && password obligatoire pour connection
        // Header "content-type: application/json" obligatoire pour être trigger, sinon:
        $res = new JsonResponse("SET THE HEADER AS APPLICATION/JSON, FIELDS REQUIERED: username, password");
        $res->setStatusCode(400);
        return $res;
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/user', name: 'app_api_user', methods: ["GET", "DELETE"])]
    public function apiUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $uph): JsonResponse
    {
        $USER_GET = function(Request $request, EntityManagerInterface $em) {
            $queryUserId = $request->query->get("user_id");
            if ($queryUserId) {
                $users = $em->getRepository(User::class)->findBy(
                    ["id" => $queryUserId],
                );
            } else {
                $users = $em->getRepository(User::class)->findBy(
                    [],
                    [],
                    $request->query->get("offset"),
                    $request->query->get("start"),
                );
            }
            $body = [];
            foreach ($users as $u) {
                $body[] = [
                    "id" => $u->getId(),
                    "email" => $u->getEmail(),
                    "roles" => $u->getRoles(),
                    "created_at" => $u->getCreatedAt(),
                ];
            }
            return ["statusCode" => 200, "body" => $body];
        };
        $USER_DELETE = function(string $payload, EntityManagerInterface $em) {
            $payload = json_decode($payload, true);
            if (!array_key_exists("user_id", $payload ?? [])) {
                return ["statusCode" => 400, "body" => "'user_id' field REQUIERED"];
            }
            $user = $em->getRepository(User::class)->findOneBy(["id" => $payload["user_id"]]);
            if (!$user) {
                return ["statusCode" => 400, "body" => "No user with ID:".$payload["user_id"]];
            }
            $em->remove($user);
            $em->flush();
            $body = [
                "id" => $payload["user_id"],
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "created_at" => $user->getCreatedAt(),
            ];
            return ["statusCode" => 200, "body" => $body];
        };
        $action = match($request->getMethod()) {
            "GET" => $USER_GET($request, $em),
            "DELETE" => $USER_DELETE($request->getContent(), $em),
            default => ["statusCode" => 405, "body" => null]
        };
        $res = new JsonResponse($action["body"]);
        $res->setStatusCode($action["statusCode"]);
        return $res;
    }

    #[Route('/post', name: 'app_api_post', methods:["POST", "GET", "PATCH", "DELETE"])]
    public function apiPost(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $POST_POST = function(string $payload, EntityManagerInterface $em) {
            if (!$this->getUser()){
                return ["statusCode" => 401, "body" => "Authentification REQUIERED"];
            }
            $payload = json_decode($payload, true);
            if (!array_key_exists("category_id", $payload ?? [])) {
                return ["statusCode" => 400, "body" => "'category_id' field REQUIERED"];
            }
            $category = $em->getRepository(Category::class)->findOneBy(["id" => $payload["category_id"]]);
            if (!$category) {
                return ["statusCode" => 400, "body" => "No category with ID:".$payload["category_id"]];
            }
            $post = new Post();
            $post->setUser($this->getUser());
            $post->setCategory($category);
            $post->setTitle(array_key_exists("title", $payload) ? $payload["title"] : "Pas de titre");
            $post->setText(array_key_exists("text", $payload) ? $payload["text"] : "Pas de texte");
            $post->setCreatedAt(new \DateTimeImmutable());
            $em->persist($post);
            $em->flush();
            $body = [
                "id" => $post->getId(),
                "author" => $post->getUser()->getEmail(),
                "category" => $post->getCategory()->getTitle(),
                "title" => $post->getTitle(),
                "text" => $post->getText(),
                "created_at" => $post->getCreatedAt(),
            ];
            return ["statusCode" => 201, "body" => $body];
        };
        $POST_GET = function(Request $request, EntityManagerInterface $em) {
            $queryPostId = $request->query->get("post_id");
            if ($queryPostId) {
                $posts = $em->getRepository(Post::class)->findBy(
                    ["id" => $queryPostId]
                );
            } else {
                $posts = $em->getRepository(Post::class)->findBy(
                    [],
                    [],
                    $request->query->get("offset"),
                    $request->query->get("start"),
                );
            }
            $body = [];
            foreach ($posts as $p) {
                $body[] = [
                    "id" => $p->getId(),
                    "author" => $p->getUser()->getEmail(),
                    "category" => $p->getCategory()->getTitle(),
                    "title" => $p->getTitle(),
                    "text" => $p->getText(),
                    "created_at" => $p->getCreatedAt(),
                ];
            }
            return ["statusCode" => 200, "body" => $body];
        };
        $POST_PATCH = function(string $payload, EntityManagerInterface $em) {
            if (!$this->getUser()){
                return ["statusCode" => 401, "body" => "Authentification REQUIERED"];
            }
            $payload = json_decode($payload, true);
            if (!array_key_exists("post_id", $payload ?? [])) {
                return ["statusCode" => 400, "body" => "'post_id' field REQUIERED"];
            }
            $post = $em->getRepository(Post::class)->findOneBy(["id" => $payload["post_id"]]);
            if (!$post) {
                return ["statusCode" => 400, "body" => "No post with ID:".$payload["post_id"]];
            }
            if (!$this->isGranted("ROLE_ADMIN") && $post->getUser() != $this->getUser()) {
                return ["statusCode" => 403, "body" => "Acces FORBIDDEN"];
            }
            unset($payload["id"]);
            foreach ($payload as $k => $v) {
                $setter = "set".ucfirst($k);
                if (method_exists($post, $setter)) {
                    $post->$setter($v);
                }
            }
            $em->persist($post);
            $UserPostModifie = new UserPostModifie();
            $UserPostModifie->setPost($post);
            $UserPostModifie->setUser($this->getUser());
            $UserPostModifie->setUpdatedAt(new DateTimeImmutable());
            $em->persist($UserPostModifie);
            $em->flush();
            $body = [
                "id" => $post->getId(),
                "author" => $post->getUser()->getEmail(),
                "category" => $post->getCategory()->getTitle(),
                "title" => $post->getTitle(),
                "text" => $post->getText(),
                "created_at" => $post->getCreatedAt(),
            ];
            return ["statusCode" => 200, "body" => $body];
        };
        $POST_DELETE = function(string $payload, EntityManagerInterface $em) {
            if (!$this->getUser()){
                return ["statusCode" => 401, "body" => "Authentification REQUIERED"];
            }
            $payload = json_decode($payload, true);
            if (!array_key_exists("post_id", $payload ?? [])) {
                return ["statusCode" => 400, "body" => "'post_id' field REQUIERED"];
            }
            $post = $em->getRepository(Post::class)->findOneBy(["id" => $payload["post_id"]]);
            if (!$post) {
                return ["statusCode" => 400, "body" => "No post with ID:".$payload["post_id"]];
            }
            if (!$this->isGranted("ROLE_ADMIN") && $post->getUser() != $this->getUser()) {
                return ["statusCode" => 403, "body" => null];
            }
            $em->remove($post);
            $em->flush();
            $body = [
                "id" => $payload["post_id"],
                "author" => $post->getUser()->getEmail(),
                "category" => $post->getCategory()->getTitle(),
                "title" => $post->getTitle(),
                "text" => $post->getText(),
                "created_at" => $post->getCreatedAt(),
            ];
            return ["statusCode" => 200, "body" => $body];
        };
        $action = match($request->getMethod()) {
            "POST" => $POST_POST($request->getContent(), $em),
            "GET" => $POST_GET($request, $em),
            "PATCH" => $POST_PATCH($request->getContent(), $em),
            "DELETE" => $POST_DELETE($request->getContent(), $em),
            default => ["statusCode" => 405, "body" => null]
        };
        $res = new JsonResponse($action["body"]);
        $res->setStatusCode($action["statusCode"]);
        return $res;
    }

    #[Route('/category', name: 'app_api_category', methods: ["POST", "GET"])]
    public function apiCategory(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $CATEGORY_POST = function(string $payload, EntityManagerInterface $em) {
            if (!$this->getUser()){
                return ["statusCode" => 401, "body" => "Authentification REQUIERED"];
            } else if (!$this->isGranted("ROLE_ADMIN")) {
                return ["statusCode" => 403, "body" => "Access FORBIDDEN"];
            }
            $payload = json_decode($payload, true);
            $category = new Category();
            $category->setTitle(array_key_exists("title", $payload ?? []) ? $payload["title"] : "Pas de titre");
            $category->setUser($this->getUser());
            $category->setCreatedAt(new \DateTimeImmutable());
            $em->persist($category);
            $em->flush();
            $body = [
                "id" => $category->getId(),
                "title" => $category->getTitle(),
                "author" => $category->getUser()->getEmail(),
                "created_at" => $category->getCreatedAt(),
            ];
            return ["statusCode" => 201, "body" => $body];
        };
        $CATEGORY_GET = function(Request $request, EntityManagerInterface $em) {
            $queryCategoryId = $request->query->get("category_id");
            if ($queryCategoryId) {
                $categories = $em->getRepository(Category::class)->findBy(
                    ["id" => $queryCategoryId]
                );
            } else {
                $categories = $em->getRepository(Category::class)->findBy(
                    [],
                    [],
                    $request->query->get("offset"),
                    $request->query->get("start"),
                );
            }
            $body = [];
            foreach ($categories as $c) {
                $body[] = [
                    "id" => $c->getId(),
                    "title" => $c->getTitle(),
                    "author" => $c->getUser()->getEmail(),
                    "created_at" => $c->getCreatedAt(),
                ];
            }
            return ["statusCode" => 200, "body" => $body];
        };
        $action = match($request->getMethod()) {
            "POST" => $CATEGORY_POST($request->getContent(), $em),
            "GET" => $CATEGORY_GET($request, $em),
            default => ["statusCode" => 405, "body" => null]
        };
        $res = new JsonResponse($action["body"]);
        $res->setStatusCode($action["statusCode"]);
        return $res;
    }
}
