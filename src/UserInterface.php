<?php namespace Iesod;

use Iesod\Database\ModelInterface;
use Iesod\Database\BuildInterface;

interface UserInterface extends ModelInterface
{
    /**
     * 
     * @param string $login User login, username or email
     * @return BuildInterface
     */
    public function whereLogin($login);
}