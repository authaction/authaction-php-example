<?php

namespace App;

use AuthAction\AuthAction;
use AuthAction\Exception\TokenExpiredException;
use AuthAction\Exception\TokenInvalidException;

class JwtValidator
{
    private static ?AuthAction $instance = null;

    private static function client(): AuthAction
    {
        if (self::$instance === null) {
            self::$instance = new AuthAction(
                $_ENV['AUTHACTION_DOMAIN'],
                $_ENV['AUTHACTION_AUDIENCE']
            );
        }
        return self::$instance;
    }

    public static function verify(string $token): object
    {
        try {
            return self::client()->verifyToken($token);
        } catch (TokenExpiredException $e) {
            throw new \RuntimeException('Token has expired');
        } catch (TokenInvalidException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
