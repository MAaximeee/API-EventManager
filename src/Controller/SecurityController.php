<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/auth/home', name: 'auth_home', methods: ['GET'])]
    public function home(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'success' => true,
            'user' => $user->toArray(),
        ]);
    }

    #[Route('/auth/profile', name: 'auth_profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['username']) && !empty(trim($data['username']))) {
            $user->setUsername(trim($data['username']));
        }

        if (isset($data['email']) && !empty(trim($data['email']))) {
            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $existingUser = $this->userRepository->findOneBy(['email' => trim($data['email'])]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Cet email est déjà utilisé',
                ], Response::HTTP_CONFLICT);
            }
            $user->setEmail(trim($data['email']));
        }

        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir au moins 6 caractères',
                ], Response::HTTP_BAD_REQUEST);
            }
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $this->userRepository->save($user, true);

        return $this->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $user->toArray(),
        ]);
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Nom d\'utilisateur, email et mot de passe obligatoires',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verifier si l'email existe deja
        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'success' => false,
                'message' => 'Cet email est deja utilise',
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setCreatedAt(new \DateTimeImmutable());

        // IMPORTANT : Toujours hasher le mot de passe !
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur cree',
        ], Response::HTTP_CREATED);
    }
    #[Route('/{id}', name: 'update', methods: ['POST'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if(!$user) {
            return $this->json([
                'success' => false,
                'message' => 'utilisateur non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);

        if(isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if(isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if(isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $this->userRepository->save($user, true);

        return $this->json([
            'success' => true,
            'message' => 'Compte mis a jour',
            'data' => $user->toArray(),
        ]);
        
    }

    #[Route('/admin/register', name: 'admin_register', methods: ['POST'])]
    public function registerAdmin(Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur connecté est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Nom d\'utilisateur, email et mot de passe obligatoires',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verifier si l'email existe deja
        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'success' => false,
                'message' => 'Cet email est deja utilise',
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        return $this->json([
            'success' => true,
            'message' => 'Admin cree avec succes',
            'user' => $user->toArray(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/admin/user/{id}/role', name: 'admin_update_role', methods: ['PUT'])]
    public function updateUserRole(int $id, Request $request): JsonResponse
    {
        // Vérifier que l'utilisateur connecté est admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->userRepository->find($id);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non trouve',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Liste des rôles autorisés
        $allowedRoles = ['ROLE_USER', 'ROLE_ORGANISATEUR', 'ROLE_ADMIN'];

        if (empty($data['role']) || !in_array($data['role'], $allowedRoles)) {
            return $this->json([
                'success' => false,
                'message' => 'Role invalide. Valeurs autorisees: ' . implode(', ', $allowedRoles),
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setRoles([$data['role']]);
        $this->userRepository->save($user, true);

        return $this->json([
            'success' => true,
            'message' => 'Role mis a jour avec succes',
            'user' => $user->toArray(),
        ]);
    }

    #[Route('/admin/users', name: 'admin_users', methods: ['GET'])]
    public function adminUsers(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $this->userRepository->findBy([], ['createdAt' => 'DESC']);
        $data = array_map(
            static fn(User $user): array => $user->toArray(),
            $users
        );

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Cette methode est geree par le firewall json_login
        return $this->json(['message' => 'Route geree par le firewall']);
    }

    #[Route('/find/{id}', name: 'find', methods: ['GET'])]
    public function find(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);

        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN') && (!$currentUser || $currentUser->getId() !== $id)) {
            return $this->json([
                'success' => false,
                'message' => 'Acces refuse',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non trouve',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $user->toArray(),
        ]);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me (): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
            ], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}