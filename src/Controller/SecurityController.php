<?php

namespace App\Controller;


use App\Entity\Author;
use App\Services\JWTService;
use App\Services\RequestValidator;
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
        /**
         * @var $user Author
         */
        $requestValidator = new RequestValidator($request->getContent());

        if(!$requestValidator->isRequestValidJson()){
            return $this->json([
                'error' => 'bad request',
            ])->setStatusCode(400);
        }
        $requestValidator->setValidValuesArrayUsingPattern(["email"=>'/^\S+@\S+$/',"password"=>'/^(?=.*[a-z])[a-zA-Z\d]{7,250}$/']);

        if($requestValidator->allValuesPassed()){
            $body = $requestValidator->getValidValues();
            $email = $body['email'];
            $password = $body['password'];
            $user = $entityManager->getRepository(Author::class)->findOneBy(['email' => $email]);

            if ($user and $passwordEncoder->isPasswordValid($user, $password)) {
                $token = $JWTservice->generateToken($user->getUserIdentifier());
                return $this->json([
                    'token' => $token,
                ])->setStatusCode(200);
            }
        }
        return $this->json([
            'error' => 'invalid credentials',
        ])->setStatusCode(401);
    }
    /**
     * @return JsonResponse
     * @Route("register", name="register", methods="POST")
     */
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder): Response
    {
        /**
         * @var $user Author
         */
        $requestValidator = new RequestValidator($request->getContent());

        if(!$requestValidator->isRequestValidJson())
            return $this->json([
                'error' => 'bad request',
            ])->setStatusCode(400);

        $requestValidator->setValidValuesArrayUsingPattern([
            "email"=>'/^\S+@\S+$/',
            "name"=>'/^[a-zA-Z\d]{2,50}$/',
            "surname"=>'/^[a-zA-Z\d]{2,100}$/',
            "password"=>'/^(?=.*[a-z])[a-zA-Z\d]{7,250}$/',
            "password2"=>'/^(?=.*[a-z])[a-zA-Z\d]{7,250}$/']);

        if($requestValidator->allValuesPassed()){
            $body = $requestValidator->getValidValues();
            if($entityManager->getRepository(Author::class)->findOneBy(['email'=>$body['email']]))
                return $this->json([
                    'error' => "name taken"
                ])->setStatusCode(406);
            if($body["password"] !== $body["password2"])
                return $this->json([
                    'error' => "passwords doesnt match"
                ])->setStatusCode(406);
            $user= new Author();
            $user->setName($body["name"])
                ->setSurname($body["surname"])
                ->setEmail($body["email"])
                ->setPassword($passwordEncoder->hashPassword($user, $body['email']));
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json([
                'message' => 'successfully created account',
            ])->setStatusCode(201);
        }

        return $this->json([
            'error' => 'incomplete body',
        ])->setStatusCode(406);
    }
}