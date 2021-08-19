<?php

namespace App\Tests\Service;

use App\Services\JWTService;
use PHPUnit\Framework\TestCase;

class JWTServiceTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
    public function testItCreatesValidToken()
    {
        $jwt = new JWTService();
        $token = $jwt->generateToken("Testowanko");

        $this->assertSame("Testowanko", $jwt->getToken($token));
    }
    public function testTokenExpiresAfter6000Seconds()
    {
        $jwt = new JWTService();
        $token = $jwt->generateToken("Testowanko");
        $jwt->addTime(6000);
        $jwt->verifyToken($token);
        $this->assertSame(false, $jwt->verifyToken($token));
    }
}
