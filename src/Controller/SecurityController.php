<?php

namespace App\Controller;


use App\Entity\Author;
use App\Services\JWTService;
use App\Services\PayloadValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        $requiredFields = ["email","password"];

        if (!$payloadValidator->allRequiredFieldsPassed($requiredFields))
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ],400);

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
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordEncoder, ValidatorInterface $validator): Response
    {
        /**
         * @var $user Author
         */
        $payloadValidator = new PayloadValidator();
        if (!$payloadValidator->isRequestValidJson($request->getContent()))
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        $requiredFields = ["name","surname","email","password","password2"];

        if (!$payloadValidator->allRequiredFieldsPassed($requiredFields))
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ],400);


        $payload = $payloadValidator->getRequestContent();
        $emailDuplicate = $entityManager->getRepository(Author::class)->findBy(['email'=>$payload['email']]);
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])[a-zA-Z\d]{7,250}$/';

        if ($emailDuplicate)
            return $this->json([
                'errors'=>'email taken'
            ])->setStatusCode(400);

        if($payload['password'] !== $payload["password2"])
            return $this->json([
                'errors'=>'passwords are different'
            ])->setStatusCode(400);

        if(!preg_match($passwordRegex,$payload['password']) )
            return $this->json([
                'errors'=>'Password must contain at least one lowercase and one uppercase letter, lenght between 7 and 250characters'
            ])->setStatusCode(400);

        $user = new Author();
        $user->setName($payload["name"])
            ->setSurname($payload["surname"])
            ->setEmail($payload["email"])
            ->setPassword($passwordEncoder->hashPassword($user, $payload['password']));
        $errors = $validator->validate($user);
        if (count($errors)>0) {
            foreach ($errors as $error)
                $errorsList[] = $error->getMessage();
            return $this->json([
                "errors" => $errorsList
            ],400);
        }
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