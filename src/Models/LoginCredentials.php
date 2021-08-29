<?php

namespace App\Models;

use Symfony\Component\Validator\Constraints as Assert;

class LoginCredentials
{
    /**
     * @Assert\NotNull
     */
    private $email;
    /**
     * @Assert\NotNull
     */
    private $password;

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }
    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }
}