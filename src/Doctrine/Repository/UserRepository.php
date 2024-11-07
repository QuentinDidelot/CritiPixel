<?php

// src/Repository/UserRepository.php

namespace App\Repository;

use App\Model\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return Email<User>
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]); // Correction ici : tu utilises la variable $email, pas un email fixe
    }
}
