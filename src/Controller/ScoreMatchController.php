<?php

namespace App\Controller;

use App\Entity\ScoreMatch;
use App\Repository\EventRepository;
use App\Repository\TeamRepository;
use App\Repository\ScoreMatchRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/event', name: 'api_score_match_')]
final class ScoreMatchController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private TeamRepository $teamRepository,
        private ScoreMatchRepository $scoreMatchRepository
    ) {
    }

    #[Route('/{eventId}/score-match', name: 'get', methods: ['GET'])]
    public function getScoreMatch(int $eventId): JsonResponse
    {
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $scoreMatch = $this->scoreMatchRepository->findOneBy(['event' => $event]);

        if (!$scoreMatch) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun score trouvé pour cet événement',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $scoreMatch->toArray(),
        ]);
    }

    #[Route('/{eventId}/score-match/create', name: 'create', methods: ['POST'])]
    public function createScoreMatch(int $eventId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }
        if ($event->getCreator() !== $user) {
            return $this->json([
                'success' => false,
                'message' => 'Seul le créateur peut gérer le score',
            ], Response::HTTP_FORBIDDEN);
        }
        $existing = $this->scoreMatchRepository->findOneBy(['event' => $event]);
        if ($existing) {
            return $this->json([
                'success' => false,
                'message' => 'Un score existe déjà pour cet événement',
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        $teamA = isset($data['teamAId']) ? $this->teamRepository->find($data['teamAId']) : null;
        $teamB = isset($data['teamBId']) ? $this->teamRepository->find($data['teamBId']) : null;

        $scoreMatch = new ScoreMatch();
        $scoreMatch->setEvent($event);
        $scoreMatch->setTeamA($teamA);
        $scoreMatch->setTeamB($teamB);
        $scoreMatch->setScoreTeamA($data['scoreTeamA'] ?? null);
        $scoreMatch->setScoreTeamB($data['scoreTeamB'] ?? null);
        $scoreMatch->setStatus($data['status'] ?? 'scheduled');

        $this->scoreMatchRepository->save($scoreMatch, true);

        return $this->json([
            'success' => true,
            'message' => 'Score créé',
            'data' => $scoreMatch->toArray(),
        ], Response::HTTP_CREATED);
    }
    #[Route('/{eventId}/score-match/update', name: 'update', methods: ['PUT'])]
    public function updateScoreMatch(int $eventId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($event->getCreator() !== $user) {
            return $this->json([
                'success' => false,
                'message' => 'Seul le créateur peut modifier le score',
            ], Response::HTTP_FORBIDDEN);
        }

        $scoreMatch = $this->scoreMatchRepository->findOneBy(['event' => $event]);

        if (!$scoreMatch) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun score trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['teamAId'])) {
            $scoreMatch->setTeamA($this->teamRepository->find($data['teamAId']));
        }
        if (isset($data['teamBId'])) {
            $scoreMatch->setTeamB($this->teamRepository->find($data['teamBId']));
        }
        if (array_key_exists('scoreTeamA', $data)) {
            $scoreMatch->setScoreTeamA($data['scoreTeamA']);
        }
        if (array_key_exists('scoreTeamB', $data)) {
            $scoreMatch->setScoreTeamB($data['scoreTeamB']);
        }
        if (isset($data['status'])) {
            $scoreMatch->setStatus($data['status']);
        }

        $this->scoreMatchRepository->save($scoreMatch, true);

        return $this->json([
            'success' => true,
            'message' => 'Score mis à jour',
            'data' => $scoreMatch->toArray(),
        ]);
    }

    #[Route('/{eventId}/score-match/delete', name: 'delete', methods: ['DELETE'])]
    public function deleteScoreMatch(int $eventId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($event->getCreator() !== $user) {
            return $this->json([
                'success' => false,
                'message' => 'Seul le créateur peut supprimer le score',
            ], Response::HTTP_FORBIDDEN);
        }

        $scoreMatch = $this->scoreMatchRepository->findOneBy(['event' => $event]);

        if (!$scoreMatch) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun score trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->scoreMatchRepository->remove($scoreMatch, true);

        return $this->json([
            'success' => true,
            'message' => 'Score supprimé',
        ]);
    }
}