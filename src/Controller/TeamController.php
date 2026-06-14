<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Team;
use App\Entity\TeamMember;
use App\Entity\EventParticipant;
use App\Repository\EventRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamMemberRepository;
use App\Repository\EventParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/event', name: 'api_team_')]
final class TeamController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private TeamRepository $teamRepository,
        private TeamMemberRepository $teamMemberRepository,
        private EventParticipantRepository $participantRepository
    ) {
    }

    #[Route('/{eventId}/team', name: 'create', methods: ['POST'])]
    public function createTeam(int $eventId, Request $request): JsonResponse
    {
        /** @var \App\Entity\User $user */
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

        if (!$event->hasTeams()) {
            return $this->json([
                'success' => false,
                'message' => 'Cet événement ne supporte pas les équipes',
            ], Response::HTTP_BAD_REQUEST);
        }

        $isCreator = ($event->getCreator() === $user);
        $isConfirmedParticipant = false;

        if (!$isCreator) {
            $participant = $this->participantRepository->findOneBy([
                'event' => $event,
                'user' => $user,
            ]);

            if (!$participant || $participant->getStatus() !== EventParticipant::STATUS_CONFIRMED) {
                return $this->json([
                    'success' => false,
                    'message' => 'Vous devez être participant confirmé pour créer une équipe',
                ], Response::HTTP_FORBIDDEN);
            }

            if (!$event->isAllowParticipantCreateTeam()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Seul l\'organisateur peut créer des équipes',
                ], Response::HTTP_FORBIDDEN);
            }

            $isConfirmedParticipant = true;
        }

        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;

        if (!$name) {
            return $this->json([
                'success' => false,
                'message' => 'Le nom de l\'équipe est requis',
            ], Response::HTTP_BAD_REQUEST);
        }

        $team = new Team();
        $team->setEvent($event);
        $team->setName($name);
        $team->setColor($data['color'] ?? null);
        $team->setMaxSize($data['maxSize'] ?? null);

        $this->teamRepository->save($team, true);

        if ($isConfirmedParticipant) {
            $teamMember = new TeamMember();
            $teamMember->setTeam($team);
            $teamMember->setUser($user);
            $teamMember->setRole(TeamMember::ROLE_CAPTAIN);

            $this->teamMemberRepository->save($teamMember, true);
        }

        return $this->json([
            'success' => true,
            'message' => 'Équipe créée avec succès',
            'data' => $team->toArray(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{eventId}/teams', name: 'list', methods: ['GET'])]
    public function listTeams(int $eventId): JsonResponse
    {
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $teams = $this->teamRepository->findByEvent($event);
        $data = array_map(fn(Team $t) => $t->toArray(), $teams);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/{eventId}/team/{teamId}/join', name: 'join', methods: ['POST'])]
    public function joinTeam(int $eventId, int $teamId): JsonResponse
    {
        /** @var \App\Entity\User $user */
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

        $team = $this->teamRepository->find($teamId);

        if (!$team || $team->getEvent() !== $event) {
            return $this->json([
                'success' => false,
                'message' => 'Équipe non trouvée',
            ], Response::HTTP_NOT_FOUND);
        }

        $participant = $this->participantRepository->findOneBy([
            'event' => $event,
            'user' => $user,
        ]);

        if (!$participant || $participant->getStatus() !== EventParticipant::STATUS_CONFIRMED) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être participant confirmé pour rejoindre une équipe',
            ], Response::HTTP_FORBIDDEN);
        }

        $existingTeamMember = $this->teamMemberRepository->findUserTeamInEvent($user, $event);

        if ($existingTeamMember) {
            return $this->json([
                'success' => false,
                'message' => 'Vous êtes déjà dans une équipe pour cet événement',
            ], Response::HTTP_CONFLICT);
        }

        if ($team->getMaxSize() !== null) {
            $memberCount = $this->teamMemberRepository->countByTeam($team);
            if ($memberCount >= $team->getMaxSize()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Cette équipe est complète',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $alreadyInTeam = $this->teamMemberRepository->findOneBy([
            'team' => $team,
            'user' => $user,
        ]);

        if ($alreadyInTeam) {
            return $this->json([
                'success' => false,
                'message' => 'Vous êtes déjà dans cette équipe',
            ], Response::HTTP_CONFLICT);
        }

        $teamMember = new TeamMember();
        $teamMember->setTeam($team);
        $teamMember->setUser($user);
        $teamMember->setRole(TeamMember::ROLE_MEMBER);

        $this->teamMemberRepository->save($teamMember, true);

        return $this->json([
            'success' => true,
            'message' => 'Vous avez rejoint l\'équipe',
            'data' => $teamMember->toArray(),
        ], Response::HTTP_CREATED);
    }


    #[Route('/{eventId}/team/{teamId}/leave', name: 'leave', methods: ['DELETE'])]
    public function leaveTeam(int $eventId, int $teamId): JsonResponse
    {
        /** @var \App\Entity\User $user */
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

        $team = $this->teamRepository->find($teamId);

        if (!$team || $team->getEvent() !== $event) {
            return $this->json([
                'success' => false,
                'message' => 'Équipe non trouvée',
            ], Response::HTTP_NOT_FOUND);
        }

        $teamMember = $this->teamMemberRepository->findOneBy([
            'team' => $team,
            'user' => $user,
        ]);

        if (!$teamMember) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas dans cette équipe',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->teamMemberRepository->remove($teamMember, true);

        return $this->json([
            'success' => true,
            'message' => 'Vous avez quitté l\'équipe',
        ]);
    }

    #[Route('/{eventId}/team/{teamId}/members', name: 'members', methods: ['GET'])]
    public function listMembers(int $eventId, int $teamId): JsonResponse
    {
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $team = $this->teamRepository->find($teamId);

        if (!$team || $team->getEvent() !== $event) {
            return $this->json([
                'success' => false,
                'message' => 'Équipe non trouvée',
            ], Response::HTTP_NOT_FOUND);
        }

        $members = $this->teamMemberRepository->findByTeam($team);
        $data = array_map(fn(TeamMember $m) => $m->toArray(), $members);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/{eventId}/team/{teamId}', name: 'show', methods: ['GET'])]
    public function showTeam(int $eventId, int $teamId): JsonResponse
    {
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }

        $team = $this->teamRepository->find($teamId);

        if (!$team || $team->getEvent() !== $event) {
            return $this->json([
                'success' => false,
                'message' => 'Équipe non trouvée',
            ], Response::HTTP_NOT_FOUND);
        }

        $members = $this->teamMemberRepository->findByTeam($team);
        $memberCount = count($members);

        $data = $team->toArray();
        $data['memberCount'] = $memberCount;
        $data['members'] = array_map(fn(TeamMember $m) => $m->toArray(), $members);

        return $this->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    #[Route('/{eventId}/team/{teamId}', name: 'delete', methods: ['DELETE'])]
    public function deleteTeam(int $eventId, int $teamId): JsonResponse
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

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());
        $isCreator = ($event->getCreator() === $user);

        if (!$isAdmin && !$isCreator) {
            return $this->json([
                'success' => false,
                'message' => 'Vous n\'avez pas les droits pour supprimer cette équipe',
            ], Response::HTTP_FORBIDDEN);
        }

        $team = $this->teamRepository->find($teamId);

        if (!$team || $team->getEvent() !== $event) {
            return $this->json([
                'success' => false,
                'message' => 'Équipe non trouvée',
            ], Response::HTTP_NOT_FOUND);
        }

        $members = $this->teamMemberRepository->findByTeam($team);
        foreach ($members as $member) {
            $this->teamMemberRepository->remove($member, false);
        }
        $this->teamRepository->remove($team, true);

        return $this->json([
            'success' => true,
            'message' => 'Équipe supprimée avec succès',
        ]);
    }
}