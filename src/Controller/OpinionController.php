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

class OpinionController extends AbstractController
{
    /**
     * @Route("/books/{isbn}", name="details")
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
    public function addOpinion(Request $request, Book $book,EntityManagerInterface $entityManager): Response
    {
        $payloadValidator = new PayloadValidator($request->getContent());
        if(!$payloadValidator->isRequestValidJson()){
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        }
        $payloadValidator->validateField("rating",[
            "greaterThanOrEqual"=>['value'=>1],
            "smallerThanOrEqual"=>['value'=>10],
        ]);
        $payloadValidator->validateField("description",[
            "longerThanOrEqual"=>['value'=>2],
            "shorterThanOrEqual"=>['value'=>500],
        ]);
        $payloadValidator->validateField("author",[
            "longerThanOrEqual"=>['value'=>2],
            "shorterThanOrEqual"=>['value'=>100],
        ]);
        $payloadValidator->validateField("author",[
            "longerThanOrEqual"=>['value'=>2],
            "shorterThanOrEqual"=>['value'=>100],
        ]);
        $payloadValidator->validateField("email",[
            "none"=>[],
        ]);
        if (!$payloadValidator->allIsGood())
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ]);
        $payload = $payloadValidator->getRequestContent();
        try {
            $opinion = new Opinion();
            $opinion->setDescription($payload["description"])
                ->setAuthor($payload["author"])
                ->setRating($payload["rating"])
                ->setCreated(new \DateTime("now"))
                ->setBook($book);
            if(key_exists("email",$payload))
                $opinion->setEmail($payload["email"]);
            $entityManager->persist($opinion);
            $entityManager->flush();
            return $this->json([
                "message" => "opinion added"
            ],203);
        }
        catch (\Exception $e){
            return $this->json([
                "error" => $e->getMessage()
            ],500);
        }
    }
}
