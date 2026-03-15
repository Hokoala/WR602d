<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que toutes les routes sécurisées redirigent vers /login
 * lorsqu'un utilisateur n'est pas authentifié.
 */
class SecuredRoutesTest extends WebTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('securedRoutesProvider')]
    public function testUnauthenticatedRedirectsToLogin(string $route): void
    {
        $client = static::createClient();
        $client->request('GET', $route);

        self::assertResponseRedirects('/login', null, "La route {$route} devrait rediriger vers /login");
    }

    public static function securedRoutesProvider(): array
    {
        return [
            ['/account/history'],
            ['/convert/url'],
            ['/convert/html'],
            ['/convert/merge'],
            ['/convert/markdown'],
            ['/convert/office'],
            ['/convert/screenshot'],
            ['/convert/wysiwyg'],
            ['/convert/split'],
            ['/convert/compress'],
            ['/convert/image'],
            ['/profile'],
            ['/account/contacts'],
        ];
    }

    public function testPublicRoutesAreAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');
        self::assertResponseIsSuccessful('La page d\'accueil doit être accessible sans authentification');

        $client->request('GET', '/login');
        self::assertResponseIsSuccessful('La page de connexion doit être accessible sans authentification');

        $client->request('GET', '/register');
        self::assertResponseIsSuccessful('La page d\'inscription doit être accessible sans authentification');

        $client->request('GET', '/reset-password');
        self::assertResponseIsSuccessful('La page de reset password doit être accessible sans authentification');
    }
}
