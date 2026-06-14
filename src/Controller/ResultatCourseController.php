<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ResultatCourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/event', name: 'api_resultat_course_')]
final class ResultatCourseController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ResultatCourseRepository $resultatCourseRepository
    ) {}

    #[Route('/{eventId}/resultat-course', name: 'get', methods: ['GET'])]
    public function getResultatCourse(int $eventId): JsonResponse
    {
        $event = $this->eventRepository->find($eventId);
        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }
        $results = $this->resultatCourseRepository->findAllByEvent($eventId);
        if (!$results) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun résultat trouvé pour cet événement',
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'success' => true,
            'data' => $results,
        ]);
    }
    #[Route('/{eventId}/create-resultat-course', name: 'create', methods: ['POST'])]
    public function createResultatCourse(int $eventId): JsonResponse
    {
        $event = $this->eventRepository->find($eventId);
        if (!$event) {
            return $this->json([
                'success' => false,
                'message' => 'Événement non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }
        $result = $this->resultatCourseRepository->createResultatCourse($event);
        if (!$result) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la création du résultat de course',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->json([
            'success' => true,
            'data' => $result,
        ], Response::HTTP_CREATED);
    }
}
