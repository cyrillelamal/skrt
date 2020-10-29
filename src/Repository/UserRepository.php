<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param string $username
     * @return array
     */
    public function findWhereUsernameLike(string $username): array
    {
        $qb = $this->createQueryBuilder('user');

        $qb->where('user.username LIKE :username')->setParameter('username', "%$username%");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string[] $usernames
     * @return User[]
     */
    public function findWhereUsernameIn(array $usernames): array
    {
        $qb = $this->createQueryBuilder('user');

        $qb->where('user.username IN (:usernames)')->setParameter('usernames', $usernames);

        return $qb->getQuery()->getResult();
    }
}
