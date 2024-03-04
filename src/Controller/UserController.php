<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/api/v1/user')]
class UserController extends AbstractController
{
    #[Route('', name: 'api.v1.user.create', methods: ['POST'])]
    public function create(
        Request                     $request,
        SerializerInterface         $serializer,
        EntityManagerInterface      $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', DeserializationContext::create()->setGroups(['user:create']));
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['user:get'])),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api.v1.user.get-id', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getById(
        User $user,
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($user, 'json', SerializationContext::create()->setGroups(['user:get'])),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
