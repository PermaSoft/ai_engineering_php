<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for SecurityController.
 */
final class SecurityControllerTest extends WebTestCase
{
    public function testLoginPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'jane_admin',
            '_password' => 'kitten',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects();
        $client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'jane_admin',
            '_password' => 'wrongpassword',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/login');
        $client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }
}
