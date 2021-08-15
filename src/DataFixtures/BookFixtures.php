<?php

namespace App\DataFixtures;


use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Author;


class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $books = [
            [
                'tittle'=>"Roughing It",
                'ISBN'=>"1243",
                'description'=>"Some book",
                'created'=>'10/16/2003',
                'author'=>"mark@twain.xx"
            ],
            [
                'tittle'=>"The Adventures of Huckelberry Finn",
                'ISBN'=>"11243",
                'description'=>"Some book",
                'created'=>'10/16/2003',
                'author'=>"mark@twain.xx"
            ],
        ];


        foreach ($books as $book) {
            $author = $manager->getRepository(Author::class)->findOneBy(["email" => $book['author']]);
            $dateTime = new \DateTime($book['created']);

            $fixture = new Book();
            $fixture->setAuthor($author)
                ->setIsbn($book['ISBN'])
                ->setDescription($book['description'])
                ->setTitle($book['tittle'])
                ->setCreated($dateTime);
            $manager->persist($fixture);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [AuthorFixtures::class];
    }

}