<?php

namespace Mygento\Payment\Api;

interface OrderInterface
{
    public function getAmountAuthorized(): string;

    public function getAmountPaid(): string;

    public function getAmountOrdered(): string;

    public function getAmountRefunded(): string;

    public function getCurrency(): string;

    public function getCustomerName(): string;

    public function getEmail(): string;

    public function getTelephone(): ?string;

    public function getVatId(): ?string;

    public function getId(): string;

    public function setAmountAuthorized(string $amount): OrderInterface;

    public function setAmountPaid(string $amount): OrderInterface;

    public function setAmountRefunded(string $amount): OrderInterface;
}
