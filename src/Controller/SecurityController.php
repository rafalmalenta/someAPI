<?php

namespace App\Controller;


use App\Entity\Author;
use App\Form\AuthorType;
use App\Form\LoginType;
use App\Services\JWTService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @return JsonResponse
     * @Route("login", name="login", methods={"POST"})
     */
    public function getToken(Request $request,EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordEncoder,JWTService $JWTservice): Response
    {
        $form = $this->createForm(LoginType::class );
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $credentials = $form->getData();
            $user = $entityManager->getRepository(Author::class)->findOneBy(['email' => $credentials->getEmail()]);

            if ($user and $passwordEncoder->isPasswordValid($user, $credentials->getPassword())) {
                $token = $JWTservice->generateToken($user->getUserIdentifier());
                return $this->json([
                    'token' => $token,
                ])->setStatusCode(200);
            }
            return $this->json([
                'error' => 'invalid credentials',
            ])->setStatusCode(401);
        }

        return $this->json([
            (string)$form->getErrors(true,false),
        ])->setStatusCode(401);
    }
    /**
     * @return JsonResponse
     * @Route("register", name="register", methods="POST")
     */
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $form = $this->createForm(AuthorType::class);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $author = $form->getData();
            $author->setPassword($passwordEncoder->hashPassword($author, $author->getPassword()));
            $entityManager->persist($author);
            $entityManager->flush();
            return $this->json([
                'status' => 'created',
            ])->setStatusCode(201);
        }

        return $this->json( $form->getErrors(true, true));
    }
}