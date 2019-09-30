<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xm\SymfonyBundle\Model\User\Token;

/**
 * @ORM\Entity(repositoryClass="Xm\SymfonyBundle\Projection\User\UserTokenFinder")
 */
class UserToken
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=180)
     */
    private $token;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="tokens")
     * @ORM\JoinColumn(referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $generatedAt;

    public function token(): Token
    {
        return Token::fromString($this->token);
    }

    public function user(): User
    {
        return $this->user;
    }

    public function generatedAt(): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->generatedAt);
    }
}
