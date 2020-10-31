<?php

namespace App\Repository;

use App\DataTransferObject\ConversationDataTransfer;
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
    /**
     * @var ConversationDataTransfer
     */
    private $dataTransfer;

    public function __construct(ManagerRegistry $registry, ConversationDataTransfer $dataTransfer)
    {
        parent::__construct($registry, Conversation::class);
        $this->dataTransfer = $dataTransfer;
    }

    public function findWithParticipants(int $id): ?Conversation
    {
        $qb = $this->createQueryBuilder('conversation');

        $qb->where('conversation.id = :id')->setParameter('id', $id);
        $qb->leftJoin('conversation.participants', 'participants');

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * N per group: Select the user's conversations and their last messages.
     * @param User $user Owner or participant.
     * @param int $nbLatestMessages Number of last messages per group.
     * @return Conversation[]
     */
    public function findForUserWithLastMessages(User $user, int $nbLatestMessages = 1): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = $this->getSqlFindForUserWithLatestMessages();
        $params = [
            'userId' => $user->getId(),
            'nbLastMessages' => $nbLatestMessages,
        ];

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $messageRows = $stmt->fetchAllAssociative();

        $dataTransfers = $this->dataTransfer::hydrateWindowFunctionOverMessages($messageRows);

        return Conversation::buildManyFromDataTransfers($dataTransfers);
    }

    protected function getSqlFindForUserWithLatestMessages(): string
    {
        return <<<SQL
SELECT 
    *
    , `creator`.`id`                                                                         as `creator_id`
    , `creator`.`username`                                                                   as `creator_username`
    FROM (SELECT
                    `c`.`id`                                                                 as `conversation_id`
                  , `c`.`updated_at`                                                         as `conversation_updated_at`
                  , `c`.`created_at`                                                         as `conversation_created_at`
                  , `c`.`is_empty`                                                           as `conversation_is_empty`
                  , `c`.`title`                                                              as `conversation_title`
                  , `m`.`id`                                                                 as `message_id`
                  , `m`.`body`                                                               as `message_body`
                  , `m`.`created_at`                                                         as `message_created_at`
                  , `m`.`creator_id`                                                         as `message_creator_id`
                  , row_number() OVER (PARTITION BY `c`.`id` ORDER BY `m`.`created_at` DESC) as `con_rank`
        FROM `conversation` AS `c`
            INNER JOIN `conversation_user` AS `cu` ON `c`.`id` = `cu`.`conversation_id`
            INNER JOIN `user` AS `u` ON `cu`.`user_id` = `u`.`id` AND `cu`.`user_id` = :userId
            LEFT JOIN `message` AS `m` ON `c`.`id` = `m`.`conversation_id`
    ) AS `ranks`
LEFT JOIN `user` AS `creator` ON `creator`.`id` = `ranks`.`message_creator_id`
WHERE `ranks`.`con_rank` <= :nbLastMessages
ORDER BY `ranks`.`conversation_updated_at` DESC
SQL;
    }
}
