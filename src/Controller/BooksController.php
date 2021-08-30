<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Services\PayloadValidator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BooksController extends AbstractController
{
    /**
     * @Route("/books", name="allooks", methods={"GET"})
     */
    public function allBooks(Request $request, EntityManagerInterface $manager): Response
    {
        $page = $request->query->get("page") ?? 1;
        $searchTitleFraze = $request->query->get("searchTitleFraze") ?? "";
        $searchDescriptionFraze = $request->query->get("searchDescriptionFraze") ?? "";

        $booksRepo = $manager->getRepository(Book::class);

        $booksCount = $booksRepo->count([]);
        $maxPages = ceil($booksCount/10);
        $booksList = $booksRepo->findAllPaginatedWithSearchTerms($page, $searchTitleFraze, $searchDescriptionFraze);
        return $this->json([
            "meta"=>["page"=>$page, "limit"=>10, "total_pages"=>$maxPages, "total_books"=>$booksCount],
            "books"=>$booksList,
        ],
        200,
        [],
        [
            "groups"=>"bookList"
        ]);
    }
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
     * @Route("/books", name="addbook", methods={"POST"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function addBook(Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(BookType::class );
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $newBook = $form->getData();
            $newBook->setCreated(new \DateTime('now'));
            $manager->persist($newBook);
            $manager->flush();
            return $this->json([
                'status' => 'created',
            ])->setStatusCode(203);
        }
        return $this->json([
            (string) $form->getErrors(true,false),
        ])->setStatusCode(400);
    }
    /**
     * @Route("/books/{isbn}", name="editbook", methods={"PATCH"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function editBook(Book $book, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): Response
    {
        $this->denyAccessUnlessGranted('OWNS', $book);
        $payloadValidator = new PayloadValidator();
        $user = $this->getUser();
        if(!$payloadValidator->isRequestValidJson($request->getContent())){
            return $this->json([
                'error' => 'bad payload',
            ])->setStatusCode(400);
        }
        $requiredFields = ["title","description"];

        if(!$payloadValidator->allRequiredFieldsPassed($requiredFields))
            return $this->json([
                "errors" => $payloadValidator->getErrors()
            ]);
        $payload = $payloadValidator->getRequestContent();
        if(!$book)
            return $this->json([
                "errors" => "no such book exists"
            ],404);

        $book->setTitle($payload['title'])
            ->setDescription($payload['description']);
        $errors = $validator->validate($book);
        if (count($errors)>0) {
            foreach ($errors as $error)
                $errorsList[] = $error->getMessage();
            return $this->json([
                "errors" => $errorsList
            ],400);
        }
        $manager->flush();
        return $this->json([
            "message" => "edited"
        ]);
    }
    /**
     * @Route("/books/{isbn}", name="editbook", methods={"DELETE"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function deleteBook(Book $book, Request $request, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('OWNS', $book);
        $comments = $book->getOpinions();
        if($comments)
            return $this->json([
                "error"=>"You cant remove opinionated book"
            ])->setStatusCode(400);
        try {
            $manager->remove($book);
            $manager->flush();
            return $this->json([
                "message"=>"deleted"
            ])->setStatusCode(203);
        }
        catch (\Exception $e)
        {
            return $this->json([
                "error"=>$e->getMessage()
            ])->setStatusCode(500);
        }
    }

}
