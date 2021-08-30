<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Opinion;
use App\Services\PayloadValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OpinionController extends AbstractController
{
    /**
     * @Route("/books/{isbn}", name="details", methods={"GET"})
     */
    public function details(Book $book): Response
    {
        $opinions = $book->getOpinions();

        return $this->json([
            'book' => $book,
            'opinions' => $opinions,
        ],
        200,
        [],
        [
            "groups"=>"details"
        ]);
    }
    /**
     * @Route("/books/{isbn}/opinions", name="addOpinion", methods={"POST"})
     */
    public function addOpinion(Request $request, Book $book,EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $payloadValidator = new PayloadValidator();
        if(!$payloadValidator->isRequestValidJson($request->getContent())){
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        }
        $requiredFields = ["rating","description","author"];
        if (!$payloadValidator->allRequiredFieldsPassed($requiredFields))
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ],400);

        $payload = $payloadValidator->getRequestContent();
        $opinion = new Opinion();
        $opinion->setDescription($payload["description"])
            ->setAuthor($payload["author"])
            ->setRating($payload["rating"])
            ->setCreated(new \DateTime("now"))
            ->setBook($book);
        if(key_exists("email",$payload))
                $opinion->setEmail($payload["email"]);;
        $errors = $validator->validate($opinion);
        if (count($errors)>0) {
            foreach ($errors as $error)
                $errorsList[] = $error->getMessage();
            return $this->json([
                "errors" => $errorsList
            ],400);
        }
        try {
            $entityManager->persist($opinion);
            $entityManager->flush();
            return $this->json([
                "message" => "opinion added"
            ],203);
        }
        catch (\Exception $e){
            return $this->json([
                "error" => "internal server error"
            ],500);
        }
    }

}
