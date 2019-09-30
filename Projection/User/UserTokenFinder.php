<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Projection\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Xm\SymfonyBundle\Entity\UserToken;
use Xm\SymfonyBundle\Model\User\Token;

/**
 * @method UserToken|null find(Token|string $id, $lockMode = null, $lockVersion = null)
 * @method UserToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserToken[]    findAll()
 * @method UserToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTokenFinder extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserToken::class);
    }
}
