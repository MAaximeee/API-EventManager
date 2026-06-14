<?php

namespace App\Controller;

use App\Entity\Requests;
use App\Repository\RequestsRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/requests', name: 'api_requests_')]
final class RequestsController extends AbstractController
{
    public function __construct(
        private RequestsRepository $requestsRepository,
        private UserRepository $userRepository
    ) {
    }
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $requests = $this->requestsRepository->findAll();
        $data = array_map(fn(Requests $request) => [
            'id' => $request->getId(),
            'objet' => $request->getObjet(),
            'message' => $request->getMessage(),
            'status' => $request->getStatus(),
            'creator' => [
                'id' => $request->getCreator()->getId(),
                'username' => $request->getCreator()->getUsername(),
            ],
        ], $requests);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Vous devez être connecté',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['objet'], $data['message'])) {
            return $this->json([
                'success' => false,
                'message' => 'Les champs "objet" et "message" sont requis',
            ], Response::HTTP_BAD_REQUEST);
        }

        $requestEntity = new Requests();
        $requestEntity->setObjet($data['objet']);
        $requestEntity->setMessage($data['message']);
        $requestEntity->setCreator($user);
        $requestEntity->setStatus('pending');

        $this->requestsRepository->save($requestEntity, true);

        return $this->json([
            'success' => true,
            'message' => 'Votre demande a été envoyée avec succès',
            'data' => [
                'id' => $requestEntity->getId(),
                'objet' => $requestEntity->getObjet(),
                'message' => $requestEntity->getMessage(),
                'status' => $requestEntity->getStatus(),
            ],
        ], Response::HTTP_CREATED);
    }
        #[Route('/{id}', name: 'show', methods: ['GET'])]
        public function show(int $id): JsonResponse
        {
            $requestEntity = $this->requestsRepository->find($id);

            if (!$requestEntity) {
                return $this->json([
                    'success' => false,
                    'message' => 'Demande non trouvée',
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $requestEntity->getId(),
                    'objet' => $requestEntity->getObjet(),
                    'message' => $requestEntity->getMessage(),
                    'status' => $requestEntity->getStatus(),
                ],
            ]);
        }
        #[Route('/{id}/accept', name: 'accept', methods: ['PUT'])]
        public function accept(int $id): JsonResponse
        {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Accès refusé',
                ], Response::HTTP_FORBIDDEN);
            }
            $requestEntity = $this->requestsRepository->find($id);

            if (!$requestEntity) {
                return $this->json([
                    'success' => false,
                    'message' => 'Demande non trouvée',
                ], Response::HTTP_NOT_FOUND);
            }

            $requestEntity->setStatus('accepted');
            $this->requestsRepository->save($requestEntity, true);

            // Mettre à jour le rôle du créateur
            $creator = $requestEntity->getCreator();
            if (!in_array('ROLE_ORGANISATEUR', $creator->getRoles())) {
                $creator->setRoles(['ROLE_ORGANISATEUR']);
                $this->userRepository->save($creator, true);
            }

            return $this->json([
                'success' => true,
                'message' => 'Demande acceptée',
                'data' => [
                    'id' => $requestEntity->getId(),
                    'objet' => $requestEntity->getObjet(),
                    'message' => $requestEntity->getMessage(),
                    'status' => $requestEntity->getStatus(),
                ],

            ]);
        }
        #[Route('/{id}/reject', name: 'reject', methods: ['PUT'])]
        public function reject(int $id): JsonResponse
        {
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->json([
                    'success' => false,
                    'message' => 'Accès refusé',
                ], Response::HTTP_FORBIDDEN);
            }
            $requestEntity = $this->requestsRepository->find($id);

            if (!$requestEntity) {
                return $this->json([
                    'success' => false,
                    'message' => 'Demande non trouvée',
                ], Response::HTTP_NOT_FOUND);
            }

            $requestEntity->setStatus('rejected');
            $this->requestsRepository->save($requestEntity, true);

            return $this->json([
                'success' => true,
                'message' => 'Demande rejetée',
                'data' => [
                    'id' => $requestEntity->getId(),
                    'objet' => $requestEntity->getObjet(),
                    'message' => $requestEntity->getMessage(),
                    'status' => $requestEntity->getStatus(),
                ],

            ]);
}

}