<?php

namespace Mygento\Payment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mygento_payment_registration')]
#[ORM\Index(columns: ['code', 'order'])]
class Registration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column]
    private string $code;

    #[ORM\Column(name: '`order`')]
    private string $order;

    #[ORM\Column(nullable: true)]
    private ?string $paymentIdentifier;

    #[ORM\Column(nullable: true)]
    private ?string $paymentUrl;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $try = 1;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $paymentType;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $code, string $order, string $paymentIdentifier, string $paymentUrl, int $paymentType, int $try = 0)
    {
        $this->code = $code;
        $this->order = $order;
        $this->paymentIdentifier = $paymentIdentifier;
        $this->paymentUrl = $paymentUrl;
        $this->paymentType = $paymentType;
        $this->try = $try;
        $this->createdAt = new \DateTimeImmutable();
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

    public function getPaymentUrl(): ?string
    {
        return $this->paymentUrl;
    }

    public function getPaymentIdentifier(): ?string
    {
        return $this->paymentIdentifier;
    }

    public function getTry(): int
    {
        return $this->try;
    }

    public function getPaymentType(): int
    {
        return $this->paymentType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function setPaymentIdentifier(?string $paymentIdentifier): self
    {
        $this->paymentIdentifier = $paymentIdentifier;

        return $this;
    }

    public function setPaymentUrl(?string $paymentUrl): self
    {
        $this->paymentUrl = $paymentUrl;

        return $this;
    }

    public function setTry(int $try): self
    {
        $this->try = $try;

        return $this;
    }
}
