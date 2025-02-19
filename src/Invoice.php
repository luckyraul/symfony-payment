<?php

namespace Mygento\Payment;

class Invoice implements Api\InvoiceInterface
{
    private ?string $description = null;

    public function __construct(
        private string $amount,
        private string $currency,
        private string $order,
        private ?string $recurrentPaymentIdentifier = null,
    ) {}

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRecurrentPaymentIdentifier(): ?string
    {
        return $this->recurrentPaymentIdentifier;
    }
}
