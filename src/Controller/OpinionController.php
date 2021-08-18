<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Opinion;
use App\Services\PayloadValidator;
use App\Services\Strategies\GreaterThanStrategy;
use App\Services\Strategies\LongerOrEqualStrategy;
use App\Services\Strategies\RegExStrategy;
use App\Services\Strategies\ShorterOrEqualStrategy;
use App\Services\Strategies\SmallerThanOrEqualStrategy;
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
        $payloadValidator = new PayloadValidator();
        if(!$payloadValidator->isRequestValidJson($request->getContent())){
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        }
        $authorStrategies = [new ShorterOrEqualStrategy(100), new LongerOrEqualStrategy(2)];
        $payloadValidator->validate("author",true, $authorStrategies);
        $ratingStrategies = [new GreaterThanStrategy(1), new SmallerThanOrEqualStrategy(10)];
        $payloadValidator->validate("rating",true, $ratingStrategies);
        $descriptionStrategies = [new LongerOrEqualStrategy(2), new ShorterOrEqualStrategy(500)];
        $payloadValidator->validate("description",true, $descriptionStrategies);
        $emailStrategies = [new RegExStrategy("/^\S+@\S+$/")];
        $payloadValidator->validate("email",false, $emailStrategies);

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
            ],$e->getCode());
        }
    }
}
