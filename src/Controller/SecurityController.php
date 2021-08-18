<?php

namespace App\Controller;


use App\Entity\Author;
use App\Services\JWTService;
use App\Services\PayloadValidator;
use App\Services\RequestValidator;
use App\Services\Strategies\LongerOrEqualStrategy;
use App\Services\Strategies\RegExStrategy;
use App\Services\Strategies\ShorterOrEqualStrategy;
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
        $payloadValidator = new PayloadValidator();

        if(!$payloadValidator->isRequestValidJson($request->getContent())){
            return $this->json([
                'error' => 'bad request',
            ])->setStatusCode(400);
        }
        $payload = $payloadValidator->getRequestContent();

        $email = $payload['email'];
        $password = $payload['password'];
        $user = $entityManager->getRepository(Author::class)->findOneBy(['email' => $email]);
        if ($user and $passwordEncoder->isPasswordValid($user, $password)) {
            $token = $JWTservice->generateToken($user->getUserIdentifier());
            return $this->json([
                'token' => $token,
            ])->setStatusCode(200);
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
        $payloadValidator = new PayloadValidator();
        if (!$payloadValidator->isRequestValidJson($request->getContent()))
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        $passwordStrategy = [new LongerOrEqualStrategy(7), new ShorterOrEqualStrategy(250), new RegExStrategy('/^(?=.*[a-z])(?=.*[A-Z]).*$/')];
        $payloadValidator->validate("password",true,$passwordStrategy);
        $payloadValidator->passwordsMatch();
        $nameStrategy = [new LongerOrEqualStrategy(2),new ShorterOrEqualStrategy(50)];
        $payloadValidator->validate("name",true,$nameStrategy);
        $surnameStrategy = [new LongerOrEqualStrategy(2), new ShorterOrEqualStrategy(100)];
        $payloadValidator->validate("surname",true,$surnameStrategy);
        $emailStrategies = [new RegExStrategy("/^\S+@\S+$/")];
        $payloadValidator->validate("email",true, $emailStrategies);

        if(!$payloadValidator->allIsGood())
            return $this->json([
                'errors'=>$payloadValidator->getErrors()
            ])->setStatusCode(400);

        $payLoad = $payloadValidator->getRequestContent();
        $emailDuplicate = $entityManager->getRepository(Author::class)->findBy(['email'=>$payLoad['email']]);
        if ($emailDuplicate)
            return $this->json([
                'errors'=>'email taken'
            ])->setStatusCode(400);

        $user = new Author();
            $user->setName($payLoad["name"])
                ->setSurname($payLoad["surname"])
                ->setEmail($payLoad["email"])
                ->setPassword($passwordEncoder->hashPassword($user, $payLoad['password']));
        try {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json([
                'message' => 'successfully created account',
            ])->setStatusCode(201);
        }
        catch (\Exception $e){
            return $this->json([
                'error' => 'internal server error',
            ])->setStatusCode(500);
        }
    }

}