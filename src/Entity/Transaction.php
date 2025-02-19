<?php

namespace Mygento\Payment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mygento_payment_transaction')]
#[ORM\Index(columns: ['code'])]
#[ORM\UniqueConstraint(columns: ['code', 'transaction_id', 'transaction_type'])]
class Transaction
{
    public const TRANSACTION_TYPE = [
        self::AUTH_TYPE => self::AUTH,
        self::CAPTURE_TYPE => self::CAPTURE,
        self::VOID_TYPE => self::VOID,
        self::REFUND_TYPE => self::REFUND,
    ];
    public const AUTH_TYPE = 'auth';
    public const CAPTURE_TYPE = 'capture';
    public const VOID_TYPE = 'void';
    public const REFUND_TYPE = 'refund';
    public const AUTH = 1;
    public const CAPTURE = 2;
    public const VOID = 3;
    public const REFUND = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column]
    private string $code;

    #[ORM\Column(name: '`order`')]
    private string $order;

    #[ORM\Column]
    private string $paymentIdentifier;

    #[ORM\Column]
    private string $transactionId;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $transactionType;

    #[ORM\OneToOne(targetEntity: Transaction::class)]
    private ?Transaction $parentTransaction = null;

    #[ORM\Column]
    private string $amount;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column(nullable: true)]
    private ?string $taxAmount = null;

    #[ORM\Column(nullable: true)]
    private ?string $savedPaymentIdentity = null;

    /**
     * @var mixed[]
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $data = null;

    /**
     * @var mixed[]
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $rawData = null;

    public function __construct(
        string $code,
        string $order,
        string $paymentIdentifier,
        string $transactionId,
        int $transactionType,
        string $amount,
        string $currency,
    ) {
        $this->code = $code;
        $this->order = $order;
        $this->paymentIdentifier = $paymentIdentifier;
        $this->transactionId = $transactionId;
        $this->transactionType = $transactionType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPaymentIdentifier(): string
    {
        return $this->paymentIdentifier;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getTransactionType(): int
    {
        return $this->transactionType;
    }

    public function getParentTransaction(): ?Transaction
    {
        return $this->parentTransaction;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTaxAmount(): ?string
    {
        return $this->taxAmount;
    }

    /**
     * @return mixed[]
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    public function setParentTransaction(Transaction $parentTransaction): self
    {
        $this->parentTransaction = $parentTransaction;

        return $this;
    }

    public function setTaxAmount(string $taxAmount): self
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    public function getSavedPaymentIdentity(): ?string
    {
        return $this->savedPaymentIdentity;
    }

    public function setSavedPaymentIdentity(?string $savedPaymentIdentity): self
    {
        $this->savedPaymentIdentity = $savedPaymentIdentity;

        return $this;
    }

    /**
     * @param mixed[] $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * @param mixed[] $rawData
     */
    public function setRawData(array $rawData): self
    {
        $this->rawData = $rawData;

        return $this;
    }
}
