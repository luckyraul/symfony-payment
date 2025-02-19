<?php

namespace Mygento\Payment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mygento_payment_keys')]
#[ORM\Index(columns: ['code', 'order'])]
#[ORM\UniqueConstraint(columns: ['hkey'])]
class Key
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column]
    private string $code;

    #[ORM\Column(name: '`order`')]
    private string $order;

    #[ORM\Column]
    private string $hkey;

    public function __construct(string $code, string $order, string $hkey)
    {
        $this->code = $code;
        $this->order = $order;
        $this->hkey = $hkey;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function getHkey(): string
    {
        return $this->hkey;
    }
}
