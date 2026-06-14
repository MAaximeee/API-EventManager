<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/event', name: 'api_event')]
final class EventController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository
    )
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $event = $this->eventRepository->findAllOrderedByDate();
        $data = array_map(fn(Event $event) => $event->toArray(), $event);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/my-events', name: 'my_events', methods: ['GET'])]
    public function myEvents(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ORGANISATEUR') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être organisateur ou admin pour accéder à cette ressource',
            ], Response::HTTP_FORBIDDEN);
        }
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $events = $this->eventRepository->findBy(['creator' => $user], ['createdAt' => 'DESC']);
        $data = array_map(fn(Event $event) => $event->toArray(), $events);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if(!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Event non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'success' => true,
            'data' => $event->toArray(),
        ]);
    }
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_ORGANISATEUR') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être organisateur ou admin pour créer un événement',
            ], Response::HTTP_FORBIDDEN);
        }

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return $this->json([
                'success' => false,
                'message' => 'Le titre est obligatoire',
            ], Response::HTTP_BAD_REQUEST);
        }

        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description'] ?? null);
        $event->setStatus($data['status'] ?? Event::STATUS_PENDING);
        $event->setType($data['type'] ?? null);
        $event->setVisibility($data['visibility'] ?? 'public');
        $event->setDueDate(($data['dueDate'] ?? null) ? new \DateTime($data['dueDate']) : null);
        $event->setCreator($user); 
        $event->setHasTeams($data['hasTeams'] ?? false);
        $event->setAllowParticipantCreateTeam($data['allowParticipantCreateTeam'] ?? false);

        $this->eventRepository->save($event, true);

        return $this->json([
            'success' => true,
            'message' => 'Event creee',
            'data' => $event->toArray(),
        ], Response::HTTP_CREATED);
    }
        #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Evenement non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $event->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $event->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $event->setStatus($data['status']);
        }
        if (isset($data['type'])) {
            $event->setType($data['type']);
        }
        if (isset($data['visibility'])) {
            $event->setVisibility($data['visibility']);
        }        
        if (isset($data['hasTeams'])) {
            $event->setHasTeams($data['hasTeams']);
        }
        if (isset($data['allowParticipantCreateTeam'])) {
            $event->setAllowParticipantCreateTeam($data['allowParticipantCreateTeam']);
        }

        $this->eventRepository->save($event, true);

        return $this->json([
            'success' => true,
            'message' => 'Evenement mis a jour',
            'data' => $event->toArray(),
        ]);
    }
        #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Event non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->eventRepository->remove($event, true);

        return $this->json([
            'success' => true,
            'message' => 'Event supprimee',
        ]);
    }

}
