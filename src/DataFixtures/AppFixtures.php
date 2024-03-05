<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher
    ){}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('esteban');
        $user->setPassword($this->hasher->hashPassword($user, 'Test#123'));
        $user->setRoles(['ROLE_USER']);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($user);
        $manager->flush();
    }
}
