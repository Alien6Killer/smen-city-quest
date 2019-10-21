<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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

    public function logMessage(int $userId, string $messageText): void
    {
        $message = new Message();
        $message->setUserId($userId)
            ->setMessage($messageText)
            ->setDate(new \DateTime());

        $this->_em->persist($message);
        $this->_em->flush();
    }

    public function logSystemMessage(string $message): void
    {
        $this->logMessage(User::SYSTEM_USER, $message);
    }

    public function getUserAndAdminMessages(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.userId = :userId')
            ->orWhere('m.userId = :systemUser')
            ->setParameters([
                'userId' => $userId,
                'systemUser' => User::SYSTEM_USER
            ])
            ->getQuery()
            ->getResult();
    }
}
