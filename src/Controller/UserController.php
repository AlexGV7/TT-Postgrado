<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\DataTableService;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/server-side', name: 'app_user_server_side_index', methods: ['GET'])]
    public function indexServerSide(): Response
    {
        return $this->render('user/index-server-side.html.twig');
    }

    #[Route('/searcher', name: 'app_user_searcher', methods: ['GET'])]
    public function searcher(): Response
    {
        return $this->render('user/searcher.html.twig');
    }

    #[Route('/ajax', name: 'user_ajax', methods: ['GET', 'POST'])]
    public function table(DataTableService $service, Request $request, UserRepository $userRepository) {
        
        $response = $service->getData($request, $userRepository);
 
        $returnResponse = new JsonResponse();
        $returnResponse->setJson($response);
         
        return $returnResponse;
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    
    #[Route('/search/ajax', name: 'app_user_search_ajax', methods: ['GET'])]
    public function searchUser(Request $request, UserRepository $userRepository)
    {
        $searchTerm = $request->query->get('search', '');
        $page = max(1, $request->query->getInt('page', 1));
        $pageSize = 10;
        $searchTerms = explode(' ', trim($searchTerm));

        // Query for user
        $queryBuilder = $this->buildSearchQuery($userRepository, $searchTerms);

        $queryBuilder->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize);
        $users = $queryBuilder->getQuery()->getResult();

        //Count with the query
        $countQueryBuilder = $this->buildSearchQuery($userRepository, $searchTerms, true);
        $countFiltered = (int) $countQueryBuilder->getQuery()->getSingleScalarResult();

        // Map results for Select2
        $items = array_map(
            fn ($user) => [
                'id' => $user->getId(),
                'text' => sprintf(
                    '%s | ID: %d | Roles: %s',
                    $user->getFullName(),
                    $user->getId(),
                    $user->getRolesText()
                )
            ],
            $users
        );

        return new JsonResponse([
            'results' => $items,
            'count_filtered' => $countFiltered
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $isOwner = $user && $currentUser && $currentUser->getId() === $user->getId();

        if (!$this->isGranted('ROLE_ADMIN') && !$isOwner) {
            throw $this->createAccessDeniedException('Acceso restringido.');
        }

        if (!$user) {
            throw $this->createNotFoundException('No se ha encontrado el usuario.');
        }

        return $this->render('user/show.html.twig', [
            'isOwner' => $isOwner,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/impersonate', name: 'app_user_impersonate', methods: ['GET'])]
    public function impersonate(User $user, Request $request): Response
    {
        // Ensure only admins or authorized users can impersonate
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to impersonate users.');
        }

        // Redirect to a user dashboard or another page
        return $this->redirectToRoute('homepage', [
            'ditto' => $user->getEmail()
        ]);
        
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function buildSearchQuery($userRepository, array $searchTerms, bool $isCount = false) {
        $queryBuilder = $userRepository->createQueryBuilder('u');

        if ($isCount) {
            $queryBuilder->select('COUNT(u.id)');
        }

        foreach ($searchTerms as $index => $term) {
            $orX = $queryBuilder->expr()->orX(
                $queryBuilder->expr()->like(
                    $queryBuilder->expr()->concat('u.name', $queryBuilder->expr()->literal(' '), 'u.lastName'),
                    ":term$index"
                ),
                $queryBuilder->expr()->like('u.name', ":term$index"),
                $queryBuilder->expr()->like('u.lastName', ":term$index")
            );

            // Add the ID condition only if the term is numeric
            if (is_numeric($term)) {
                $orX->add($queryBuilder->expr()->eq('u.id', ":id$index"));
                $queryBuilder->setParameter("id$index", (int) $term);
            }

            $queryBuilder->andWhere($orX)
                ->setParameter("term$index", '%' . $term . '%');
        }

        return $queryBuilder;
    }
}
