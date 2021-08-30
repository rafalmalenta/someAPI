<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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
    public function addBook(Request $request, EntityManagerInterface $manager): Response
    {
        $author = $this->getUser();
        $form = $this->createForm(BookType::class );
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $newBook = $form->getData();
            $newBook->setCreated(new \DateTime('now'));
            $newBook->setAuthor($author);

            $manager->persist($newBook);
            $manager->flush();
            return $this->json([
                'status' => 'created',
            ])->setStatusCode(201);
        }
        return $this->json([
            (string) $form->getErrors(true,false),
        ])->setStatusCode(400);
    }
    /**
     * @Route("/books/{isbn}", name="editbook", methods={"PATCH"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function editBook(Book $book, Request $request, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('OWNS', $book);
        $form = $this->createForm(BookType::class, $book, ['is_edit'=>true] );
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $newBook = $form->getData();
            $manager->persist($newBook);
            $manager->flush();
            return $this->json([
                'status' => 'edited',
            ])->setStatusCode(200);
        }
        return $this->json([
            (string) $form->getErrors(true,false),
        ])->setStatusCode(400);
    }
    /**
     * @Route("/books/{isbn}", name="removebook", methods={"DELETE"})
     * @IsGranted("ROLE_AUTHOR")
     */
    public function deleteBook(Book $book, EntityManagerInterface $manager): Response
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
                "status"=>"deleted"
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
