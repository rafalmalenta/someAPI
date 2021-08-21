<?php

namespace App\Tests\Controller;

use App\DataFixtures\AuthorFixtures;
use App\Services\JWTService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;


class AuthTest extends WebTestCase
{
    private $databaseTool;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $testClient;

    public function testAuthenticateToken()
    {
        $jwt = new JWTService();
        $token = $jwt->generateToken("mark@twain.xx");

        $this->testClient = static::createClient([],[
            'HTTP_AUTHORIZATION' => "Bearer ".$token
        ]);
        $container = self::$kernel->getContainer();
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get( null,'doctrine');
        $this->databaseTool->loadFixtures(
            [AuthorFixtures::class]
        );
        $crawler = $this->testClient->request('GET', '/books',[],[],[] );

        $this->assertResponseStatusCodeSame(200);

    }
}