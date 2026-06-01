<?php

namespace App;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use UnexpectedValueException;

class JwtValidator
{
    private static ?array $jwksCache = null;

    public static function verify(string $token): object
    {
        $domain   = $_ENV['AUTHACTION_DOMAIN'];
        $audience = $_ENV['AUTHACTION_AUDIENCE'];
        $issuer   = "https://{$domain}";

        try {
            $keys    = JWK::parseKeySet(self::getJwks($domain));
            $payload = JWT::decode($token, $keys);
        } catch (ExpiredException) {
            throw new \RuntimeException('Token has expired');
        } catch (UnexpectedValueException $e) {
            // kid not found — possible key rotation; bust cache and retry once
            if (str_contains($e->getMessage(), 'kid')) {
                self::$jwksCache = null;
                $keys    = JWK::parseKeySet(self::getJwks($domain));
                $payload = JWT::decode($token, $keys);
            } else {
                throw new \RuntimeException($e->getMessage());
            }
        }

        if (($payload->iss ?? '') !== $issuer) {
            throw new \RuntimeException('Invalid issuer');
        }

        $aud = isset($payload->aud) ? (array) $payload->aud : [];
        if (!in_array($audience, $aud, strict: true)) {
            throw new \RuntimeException('Invalid audience');
        }

        return $payload;
    }

    private static function getJwks(string $domain): array
    {
        if (self::$jwksCache !== null) {
            return self::$jwksCache;
        }

        $uri      = "https://{$domain}/.well-known/jwks.json";
        $response = file_get_contents($uri);

        if ($response === false) {
            throw new \RuntimeException('Failed to fetch JWKS');
        }

        self::$jwksCache = json_decode($response, associative: true);
        return self::$jwksCache;
    }
}
