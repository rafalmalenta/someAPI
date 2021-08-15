<?php

namespace App\Controller;


use App\Entity\Author;
use App\Services\JWTService;
use App\Services\PayloadValidator;
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
        $payloadValidator = new PayloadValidator($request->getContent());

        if(!$payloadValidator->isRequestValidJson()){
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
        $payloadValidator = new PayloadValidator($request->getContent());
        if (!$payloadValidator->isRequestValidJson())
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        $payloadValidator->validateField("password", [
            "longerThanOrEqual" =>['value'=> 7],
            "shorterThanOrEqual" => ['value'=>250],
            "regEx" =>['value'=>'/^(?=.*[a-z])(?=.*[A-Z]).*$/',
                'msg'=>"password must contain at least one upper case and lowercase letter"
                ],
            "passwordCheck"=>[]
        ]);
        $payloadValidator->validateField("name", [
            "longerThanOrEqual" =>['value'=> 2],
            "shorterThanOrEqual" => ['value'=>50]
        ]);
        $payloadValidator->validateField("surname", [
            "longerThanOrEqual" =>['value'=> 2],
            "shorterThanOrEqual" => ['value'=>100]
        ]);
        $payloadValidator->validateField("email", [
            "regEx" =>[
                'value'=>'/^\S+@\S+$/',
                'msg'=>"it doesnt looks like valid email"
            ]
        ]);
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