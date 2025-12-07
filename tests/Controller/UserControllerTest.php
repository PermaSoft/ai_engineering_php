<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional tests for UserController.
 */
final class UserControllerTest extends WebTestCase
{
    public function testUserCanChangePassword(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);

        $client->loginUser($user);

        $crawler = $client->request('GET', '/en/profile/change-password');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Update password')->form([
            'change_password[currentPassword]' => 'kitten',
            'change_password[newPassword][first]' => 'newpassword',
            'change_password[newPassword][second]' => 'newpassword',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/profile/edit');
    }

    public function testUserCanEditProfile(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'john_user']);

        $client->loginUser($user);

        $crawler = $client->request('GET', '/en/profile/edit');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save changes')->form([
            'user[fullName]' => 'Updated Full Name',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/en/profile/edit');

        // Verify update - clear and refetch user
        $entityManager->clear();
        $updatedUser = $entityManager->getRepository(User::class)->find($user->getId());
        $this->assertNotNull($updatedUser);
        $this->assertSame('Updated Full Name', $updatedUser->getFullName());
    }

    public function testProfileRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/profile/edit');

        $this->assertResponseRedirects();
    }
}
