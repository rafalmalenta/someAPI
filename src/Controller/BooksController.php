<?php

namespace App\Controller;

use App\Entity\Book;
use App\Services\PayloadValidator;
use App\Services\RequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BooksController extends AbstractController
{
    /**
     * @Route("/mybooks", name="mybooks", methods={"GET"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function myBooks(EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $myBooks = $manager->getRepository(Book::class)->findBy(["author"=>$user]);

        return $this->json([
            $myBooks,
        ],
        200,
        [],
        [
            'groups'=> "mybooks"
        ]);
    }
    /**
     * @Route("/mybooks", name="addbook", methods={"POST"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function addBook(Request $request, EntityManagerInterface $manager): Response
    {
        $payloadValidator = new PayloadValidator($request->getContent());
        $user = $this->getUser();
        if(!$payloadValidator->isRequestValidJson()){
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        }
        $payloadValidator->validateField("title",[
            "longerThanOrEqual"=>['value'=>1],
            "shorterThanOrEqual"=>['value'=>200],
        ]);
        $payloadValidator->validateField("description",[
            "longerThanOrEqual"=>['value'=>1],
        ]);
        $payloadValidator->validateField("isbn",[
            "longerThanOrEqual"=>['value'=>4],
            "shorterThanOrEqual"=>['value'=>13],
        ]);

        if(!$payloadValidator->allIsGood()) {
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ]);
        }
        $body = $payloadValidator->getRequestContent();
        $duplicateISBN = $manager->getRepository(Book::class)->findBy(['isbn' => $body['isbn']]);
        if ($duplicateISBN)
            return $this->json(["error" => "duplicated isbn"], 400);

        $newBook = new Book();
        $newBook->setTitle($body['title'])
            ->setIsbn($body['isbn'])
            ->setDescription($body['description'])
            ->setAuthor($user)
            ->setCreated(new \DateTime('now'));
        try {
            $manager->persist($newBook);
            $manager->flush();
            return $this->json([
                'success' => 'book added',
            ])->setStatusCode(406);
        } catch (\Exception $exception) {
            return $this->json([
                'error' => 'failed to save',
            ])->setStatusCode(500);
        }
    }
    /**
     * @Route("/mybooks/{isbn}", name="addbook", methods={"PATCH"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function editBook(Book $book, Request $request, EntityManagerInterface $manager): Response
    {
        $payloadValidator = new PayloadValidator($request->getContent());
        $user = $this->getUser();
        if(!$payloadValidator->isRequestValidJson()){
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        }
        $payloadValidator->validateField("title",[
            "longerThanOrEqual"=>['value'=>1],
            "shorterThanOrEqual"=>['value'=>200],
        ]);
        $payloadValidator->validateField("description",[
            "longerThanOrEqual"=>['value'=>1],
        ]);
        if(!$payloadValidator->allIsGood())
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ]);
        $payload = $payloadValidator->getRequestContent();
        $book->setTitle($payload['title'])
            ->setDescription($payload['description']);
        $manager->flush();
        return $this->json([
            "message" => "edited"
        ]);
    }


}
