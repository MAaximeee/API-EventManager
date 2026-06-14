<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTime());
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // User standard
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setCreatedAt(new \DateTime());
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        $manager->flush();
    }
}