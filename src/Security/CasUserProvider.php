<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Siding;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CasUserProvider implements UserProviderInterface
{
    private UserRepository $userRepository;

    private RequestStack $requestStack;

    private EntityManagerInterface $entityManager;

    private TokenStorageInterface $tokenStorage;

    private Siding $siding;

    public function __construct(UserRepository $userRepository, RequestStack $requestStack, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, Siding $siding)
    {
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->siding = $siding;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $userAttributes = \phpCAS::getAttributes();
        if (empty($userAttributes) || !isset($userAttributes['carlicense'])) {
            $token = $this->tokenStorage->getToken();
            if (!$token) {
                throw new UserNotFoundException();
            }
            $userAttributes = $token->getAttributes();
            if (empty($userAttributes) || !isset($userAttributes['carlicense'])) {
                throw new UserNotFoundException();
            }
        }

        $userCarLicense = $userAttributes['carlicense'];
        $rut = substr($userCarLicense, 0, -1);
        $rut = str_replace('-', '', $rut);
        $rut = (int) $rut;
        
        $user = $this->entityManager->getRepository(User::class)->findOneByRut($rut);

        if(is_null($user)) {
            $data = $this->siding->getUserData($rut);

            if ($data) {
                $user = $this->entityManager->getRepository(User::class)->createFromSidingData($data, $this->entityManager);
            } else {
                $user = $this->entityManager->getRepository(User::class)->createUserFromCasData($userAttributes, $this->entityManager);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $session = $this->requestStack->getCurrentRequest()->getSession();

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        return $this->userRepository->find($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
