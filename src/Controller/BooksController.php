<?php

namespace App\Controller;

use App\Entity\Book;
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
     * @Route("/addbook", name="addbook", methods={"POST"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function addBook(Request $request, EntityManagerInterface $manager): Response
    {
        $requestValidator = new RequestValidator($request->getContent());
        $user = $this->getUser();
        if(!$requestValidator->isRequestValidJson()){
            return $this->json([
                'error' => 'bad request',
            ])->setStatusCode(400);
        }
        $requestValidator->setValidValuesArrayUsingPattern(
            [
                "title"=>'/^\w{1,200}$/',
                "description"=>'/^\w{1,}$/',
                "isbn"=>'/^\S{4,13}$/'
            ]);

        if($requestValidator->allValuesPassed()){
            $body = $requestValidator->getValidValues();
            $duplicateISBN = $manager->getRepository(Book::class)->findBy(['isbn'=>$body['isbn']]);
            if($duplicateISBN)
                return $this->json(["error"=>"duplicated isbn"],400);
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
            }
           catch (\Exception $exception){
               return $this->json([
                   'error' => 'failed to save',
               ])->setStatusCode(500);
           }
        }
        return $this->json([
            'error' => 'incomplete or bad formatted payload',
        ])->setStatusCode(406);
    }
}
