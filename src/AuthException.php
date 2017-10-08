<?php namespace Iesod;

class AuthException extends \Exception {
    const E_OTHER = 0;
    const E_USER_NOT_FOUND = 1;
    const E_USER_UNVERIFICATION = 2;
    const E_PASSWORD_INVALID = 3;    
}