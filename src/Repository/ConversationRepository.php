<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * @param User $user Owner or participant.
     * @return Conversation[]
     */
    public function findForUser(User $user): array
    {
        $qb = $this->createQueryBuilder('conversation')
            ->innerJoin('conversation.participants', 'participants');

        return $qb->getQuery()->getResult();
    }
//select *
//from (
//select c.id                                                        as con_id
//, u.username
//, m.body
//, m.id
//, row_number() over (partition by c.id order by m.created_at) as con_rank
//from conversation as c
//inner join conversation_user cu on c.id = cu.conversation_id
//inner join user u on cu.user_id = u.id
//left join message m on c.id = m.conversation_id
//where u.id = 1
//) ranks
//where con_rank <= 1
}
