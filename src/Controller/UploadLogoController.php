<?php

namespace App\Controller;

use App\Form\LogoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
class UploadLogoController extends AbstractController
{
    #[Route('/upload/logo', name: 'app_upload_logo')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(LogoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get('logo')->getData();
            if ($logo) {
                try {
                    $logo->move(
                        $this->getParameter('kernel.project_dir')."/public/upload/",
                        "logo.jpg"
                    );
                } catch (FileException $e) {
                    return new JsonResponse([
                        "success" => false,
                        "mess" => "Erreur lors de l'upload: ".$e,
                    ]);
                }
                return new JsonResponse([
                    "success" => true,
                    "mess" => "Le logo à bien été uploadés.",
                ]);
            }
        }
        $isLogoExist = is_file($this->getParameter('kernel.project_dir')."/public/upload/logo.jpg");
        return $this->render('upload_logo/index.html.twig', [
            "form" => $form->createView(),
            "isLogoExist" => $isLogoExist,
        ]);
    }
}
