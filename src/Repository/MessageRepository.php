<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findForConversation(Conversation $conversation, int $limit = 25, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('message');

        $qb
            ->where('message.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->leftJoin('message.conversation', 'conversation')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('message.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
