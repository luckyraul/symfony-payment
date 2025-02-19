<?php

namespace Mygento\Payment\Api;

interface InvoiceInterface
{
    public function getAmount(): string;

    public function getCurrency(): string;

    public function getOrder(): string;

    public function getDescription(): ?string;

    public function getRecurrentPaymentIdentifier(): ?string;

    public function setDescription(?string $description): InvoiceInterface;
}
