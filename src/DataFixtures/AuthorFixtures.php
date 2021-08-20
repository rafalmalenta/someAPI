<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Author;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthorFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordEncoder;
    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder=$passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $author = new Author();
        $author->setName("Mark")
            ->setSurname("Twain")
            ->setEmail("mark@twain.xx")
            ->setPassword($this->passwordEncoder->hashPassword($author, "12345Qwe"));
        $manager->persist($author);
        $manager->flush();
    }
}