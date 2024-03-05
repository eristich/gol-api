<?php

namespace App\Controller;

use App\Entity\Simulation;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/simulation')]
class SimulationController extends AbstractController
{
    #[Route('', name: 'api.v1.simulation.create', methods: ['POST'])]
    public function create(
        Request                 $request,
        SerializerInterface     $serializer,
        EntityManagerInterface  $em,
        ValidatorInterface      $validator
    ): JsonResponse
    {
        $simulation = $serializer->deserialize(
            $request->getContent(),
            Simulation::class,
            'json',
            DeserializationContext::create()->setGroups(['simulation:create'])
        );

        $errors = $validator->validate($simulation, null, ['simulation:create']);
        if (count($errors) > 0) {
            throw new BadRequestException($serializer->serialize($errors, 'json'));
        }

        $simulation->setOwner($this->getUser());
        $em->persist($simulation);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($simulation, 'json', SerializationContext::create()->setGroups(['simulation:get'])),
            Response::HTTP_CREATED,
            [],
            true
        );
    }
}
