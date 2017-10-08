<?php namespace Iesod\Request;

class RequestException extends \Exception {
    const ERROR_INVALID = 100;
    const ERROR_VALIDATE_ISREQUIRED = 101;
    const ERROR_VALIDATE_PATTERN = 102;
}