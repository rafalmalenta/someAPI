<?php

namespace App\Tests\Controller;

use App\DataFixtures\AuthorFixtures;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;

    private $testClient = null;

    public function setUp(): void
    {
        $this->testClient = static::createClient([],[]);
        $container = self::$kernel->getContainer();
        $this->databaseTool = $container->get(DatabaseToolCollection::class)->get( null,'doctrine');
        $this->databaseTool->loadFixtures(
            [AuthorFixtures::class]
        );
    }
    public function testItGivesTokenWhenLogIn()
    {
        $crawler = $this->testClient->request('POST', '/login',[],[],[],
            "{\"email\": \"mark@twain.xx\",\"password\": \"12345Qwe\"}");

        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString("token",$this->testClient->getResponse()->getContent());
    }
    public function testItGivesMessageWhenFailedToLogIn()
    {
        $crawler = $this->testClient->request('POST', '/login',[],[],[],"{\"email\": \"mark@twain.xx\",\"password\": \"mistake\"}");
        $this->assertResponseStatusCodeSame(401);
        $this->assertStringContainsString("invalid credentials",$this->testClient->getResponse()->getContent());
    }

    public function testItRefuseEmailDuplication()
    {
        $crawler = $this->testClient->request('POST', '/register',[],[],[],
            "{\"email\": \"mark@twain.xx\",\"password\": \"12345Qwe\",\"password2\": \"12345Qwe\",
            \"name\": \"somename\",\"surname\": \"somesurname\"}");
        $this->assertResponseStatusCodeSame(400);
        $this->assertStringContainsString("email taken",$this->testClient->getResponse()->getContent());

    }

    public function testItRefusesBadBody()
    {
        $incorrectJSON = "{\"email\": \"mark@twain.xx\",\"password\": \"12345Qwe\",\"password2\": \"12345Qwe\",
            \"name\": \"somename\"\"surname\": \"somesurname\"}";
        $crawler = $this->testClient->request('POST', '/register',[],[],[],
            $incorrectJSON);
        $this->assertResponseStatusCodeSame(400);
        $this->assertStringContainsString("bad payload",$this->testClient->getResponse()->getContent());
    }
    public function testItRefusesNotMatchingPasswords()
    {
        $differentPasswords = "{\"email\": \"new@author.xx\",\"password\": \"12345Qwe\",\"password2\": \"!12345Qwe\",
            \"name\": \"somename\",\"surname\": \"somesurname\"}";
        $crawler = $this->testClient->request('POST', '/register',[],[],[], $differentPasswords);
        $this->assertResponseStatusCodeSame(400);
        $this->assertStringContainsString("passwords are different",$this->testClient->getResponse()->getContent());
    }
    public function testItRegisterUserIfAllConditionsMeet()
    {
        $correctJSON = "{\"email\": \"new@author.xx\",\"password\": \"12345Qwe\",\"password2\": \"12345Qwe\",
            \"name\": \"somename\",\"surname\": \"somesurname\"}";
        $crawler = $this->testClient->request('POST', '/register',[],[],[],$correctJSON);
        $this->assertResponseStatusCodeSame(201);
    }
    public function testItPerformsPasswordValidation()
    {
        $passwordTooShort = "{\"email\": \"new@author.xx\",\"password\": \"AAA\",\"password2\": \"12345Qwe\",
            \"name\": \"somename\",\"surname\": \"somesurname\"}";
        $crawler = $this->testClient->request('POST', '/register',[],[],[],$passwordTooShort);
        $this->assertResponseStatusCodeSame(400);

        $passwordTooLong = "{\"email\": \"new@author.xx\",\"password\": \"AAA\",\"password2\": \"12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe
        12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe
        12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe12345Qwe\",
            \"name\": \"somename\",\"surname\": \"somesurname\"}";
        $crawler = $this->testClient->request('POST', '/register',[],[],[],$passwordTooLong);
        $this->assertResponseStatusCodeSame(400);
    }

}
