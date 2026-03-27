<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventParticipant;
use App\Repository\EventParticipantRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/event', name: 'api_event_participant_')]
final class EventParticipantController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventParticipantRepository $participantRepository
    ) {
    }

    // Rejoindre un event
    #[Route('/{id}/join', name: 'join', methods: ['POST'])]
    public function join(int $id): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si déjà inscrit
        $existingParticipant = $this->participantRepository->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if ($existingParticipant) {
            return $this->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit à cet événement',
            ], Response::HTTP_CONFLICT);
        }

        $participant = new EventParticipant();
        $participant->setEvent($event);
        $participant->setUser($user);

        if ($event->getVisibility() === 'public') {
            $participant->setStatus(EventParticipant::STATUS_CONFIRMED);
        } else {
            $participant->setStatus(EventParticipant::STATUS_PENDING);
        }

        $this->participantRepository->save($participant, true);

        return $this->json([
            'success' => true,
            'message' => $event->getVisibility() === 'public' 
                ? 'Inscription confirmée' 
                : 'Inscription en attente de validation',
            'data' => $participant->toArray(),
        ], Response::HTTP_CREATED);
    }

    // Quitter un event
    #[Route('/{id}/leave', name: 'leave', methods: ['DELETE'])]
    public function leave(int $id): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $participant = $this->participantRepository->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if (!$participant) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas inscrit à cet événement',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->participantRepository->remove($participant, true);

        return $this->json([
            'success' => true,
            'message' => 'Désinscription réussie',
        ]);
    }

    // Liste des participants d'un event
    #[Route('/{id}/participants', name: 'list', methods: ['GET'])]
    public function listParticipants(int $id): JsonResponse
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $participants = $this->participantRepository->findBy(['event' => $event]);
        $data = array_map(fn(EventParticipant $p) => $p->toArray(), $participants);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }
}