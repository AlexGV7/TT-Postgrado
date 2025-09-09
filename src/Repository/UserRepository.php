<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use App\Service\DataTableService;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    private $service;

    public function __construct(ManagerRegistry $registry, DataTableService $service)
    {
        parent::__construct($registry, User::class);
        $this->service = $service;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function createFromSidingData($data, $em)
    {
        $user = new User();

        $user->setRut($data['rut']);
        $name = $data['name'] ?? '';
        $lastNameParts = [];

        if (!empty($data['lastname'])) {
            $lastNameParts[] = $data['lastname'];
        }

        if (!empty($data['secondLastname'])) {
            $lastNameParts[] = $data['secondLastname'];
        }

        $user->setName($name);
        $user->setLastName(implode(' ', $lastNameParts));
        
        $user->setEmail($data['mail']);
        $user->setPicture($data['photo']);
        $user->setSexFromSiding($data['sexo']);
        $user->setPhone($data['phoneNumber']);
        $user->setPassword(bin2hex(random_bytes(8)));
        $user->setDeactivatedNotifications(0);

        $roles = [];

        switch ($data['rol']) {
            case 'profesor':
                $roles[] = 'ROLE_PROFESSOR';
                break;
            case 'alumno':
                $roles[] = 'ROLE_STUDENT';
                break;
            default:
                $roles[] = 'ROLE_OTHER';
                break;
        }
        $user->setRoles($roles);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function createUserFromCasData($userAttributes, $em)
    {
        $user = new User();

        $userCarLicense = $userAttributes['carlicense'];
        $rut = substr($userCarLicense, 0, -1);
        $rut = str_replace('-', '', $rut);
        $rut = (int) $rut;

        $user->setRut($rut);
        
        // Descomentar cuando CAS entregue estos datos adicionales
        /*
        $firstName = !empty($userAttributes['givenName']) ? ucfirst(strtolower(trim($userAttributes['givenName']))) : '';
        $otherNames = !empty($userAttributes['otrosnombres']) ? ucfirst(strtolower(trim($userAttributes['otrosnombres']))) : '';
        $lastName = !empty($userAttributes['sn']) ? ucfirst(strtolower(trim($userAttributes['sn']))) : '';
        $secondLastName = !empty($userAttributes['apellidomaterno']) ? ucfirst(strtolower(trim($userAttributes['apellidomaterno']))) : '';

        $name = trim("$firstName $otherNames");
        $fullLastName = trim("$lastName $secondLastName");

        $user->setName($name);
        $user->setLastName($fullLastName);
        */

        $user->setEmail($userAttributes['mail'] ? $userAttributes['mail'] : $userAttributes['uid']);
        $user->setPicture("https://intrawww.ing.puc.cl/siding/datos/fotos/publicas/");
        $user->setSexFromSiding('M'); // SSO no entrega el género
        $user->setPassword(bin2hex(random_bytes(8)));
        $user->setDeactivatedNotifications(0);
        $user->setRoles(["ROLE_OTHER"]);

        // Descomentar cuando CAS entregue estos datos adicionales
        /*
        // Generamos descripción con información provista
        $title = $userAttributes['title'] ?? null;
        $ou = $userAttributes['ou'] ?? null;
        $categories = $userAttributes['businessCategory'] ?? [];

        $parts = [];

        if (is_string($title)) {
            $parts[] = ucfirst(strtolower($title));
        } elseif (is_array($title)) {
            $parts[] = ucfirst(strtolower(implode(' ', $title)));
        }

        if (is_string($ou)) {
            $parts[] = "pertenece a {$ou}";
        } elseif (is_array($ou)) {
            $parts[] = "pertenece a " . implode(', ', $ou);
        }

        if (is_array($categories) && !empty($categories)) {
            $parts[] = "con categorías como " . implode(', ', $categories);
        } elseif (is_string($categories)) {
            $parts[] = "con categoría " . $categories;
        }

        $description = !empty($parts)
            ? implode(', ', $parts) . '.'
            : 'Información de usuario no disponible.';
        */

        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function countObjects() {
        return $this
            ->createQueryBuilder('object')
            ->select("count(object.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTableData($start, $length, $orders, $search, $columns, $joins, $conditions, $searchBy = []) {

        $table = 'table';
 
        $query = $this->createQueryBuilder($table);

        $countQuery = $this->createQueryBuilder($table);

        $this->service->addJoins($query, $countQuery, $joins);

        $this->service->addConditions($query, $countQuery, $conditions);
 
        $this->service->countObjectsInTable($countQuery,$table);

        $this->service->setLength($countQuery, $length);

        if ($search['value'] != "") {
            $this->service->performSearch($query,$countQuery,$table, $columns, $search, $searchBy);
        }

        $this->service->addLimits($query, $start, $length);
         
        $this->service->performOrdering($query, $orders, $table, $searchBy);

        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();       
         
        return array(
            "results"       => $results,
            "countResult"   => $countResult
        );
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
