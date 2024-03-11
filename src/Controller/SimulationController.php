<?php

namespace App\Controller;

use App\Entity\Simulation;
use App\Repository\SimulationRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
        $payload = $serializer->deserialize(
            $request->getContent(),
            Simulation::class,
            'json',
            DeserializationContext::create()->setGroups(['simulation:create'])
        );

        $errors = $validator->validate($payload, null, ['simulation:create']);
        if (count($errors) > 0) {
            throw new BadRequestException($serializer->serialize($errors, 'json'));
        }

        if (empty($payload->getContent())) {
            $matrix = [];
            for ($y = 0; $y < 50; $y++) {
                for ($x = 0; $x < 80; $x++) {
                    $matrix[$y][] = rand(1, 3) === 1 ? 1 : 0;
                }
            }
            $payload->setContent($matrix);
        }

        $payload->setOwner($this->getUser());
        $em->persist($payload);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($payload, 'json', SerializationContext::create()->setGroups(['simulation:get'])),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}/share', name: 'api.v1.simulation.share', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    #[IsGranted(
        attribute: new Expression('subject["owner"] === user and subject["sharedAt"] === null'),
        subject: [
            'owner' => new Expression('args["simulation"].getOwner()'),
            'sharedAt' => new Expression('args["simulation"].getSharedAt()')
        ]
    )]
    public function share(
        #[MapEntity] Simulation $simulation,
        EntityManagerInterface  $em
    ): JsonResponse
    {
        $simulation->setSharedAt(new \DateTimeImmutable());
        $em->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    #[Route('/{id}', name: 'api.v1.simulation.get-id', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function getById(
        Simulation          $simulation,
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($simulation, 'json', SerializationContext::create()->setGroups(['simulation:get'])),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'api.v1.simulation.get-all', methods: ['GET'])]
    public function getAll(
        Request                 $request,
        SerializerInterface     $serializer,
        SimulationRepository    $repository
    ): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $mode = $request->query->getString('mode', 'all');
        $offset = ($page - 1) * $limit;

        if ($mode === 'shared') {
            $simulations = $repository->getSharedSimulationsByPagination($offset, $limit);
        } else {
            $simulations = $repository->findBy([], [], $limit, $offset);
        }

        return new JsonResponse(
            $serializer->serialize($simulations, 'json', SerializationContext::create()->setGroups(['simulation:get'])),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
