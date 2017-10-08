<?php namespace Iesod\Database;

class DatabaseException extends \Exception {
    const ERROR_INVALID = 100;
    const ERROR_VALIDATE_ISREQUIRED = 101;
    const ERROR_VALIDATE_PATTERN = 102;
}