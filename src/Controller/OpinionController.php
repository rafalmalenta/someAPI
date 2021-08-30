<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\OpinionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


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
    public function addOpinion(Request $request, Book $book,EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OpinionType::class);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $opinion=$form->getData();
            $opinion->setBook($book)
                ->setCreated(new \DateTime('now'));
            $entityManager->persist($opinion);
            $entityManager->flush();
            return $this->json([
                'status' => 'created',
            ])->setStatusCode(201);
        }
        return $this->json([
            (string) $form->getErrors(true,false)
        ])->setStatusCode(400);
    }

}
