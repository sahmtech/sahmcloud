<?php

namespace Bl\FatooraZatca\Invoices;

use Bl\FatooraZatca\Actions\GetQrFromInvoice;

class Invoiceable
{
    private $result;

    public function setResult($result): void
    {
        $this->result = $result;
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function getClearedInvoice(): string
    {
        return $this->getResult()['clearedInvoice'];
    }

    public function getXmlInvoice(): string
    {
        return base64_decode($this->getClearedInvoice());
    }

    public function getInvoiceHash(): string
    {
        return $this->getResult()['invoiceHash'];
    }

    public function getReportingStatus(): string
    {
        return $this->getResult()['reportingStatus'];
    }

    public function getValidationResults(): array
    {
        return $this->getResult()['validationResults'];
    }

    public function getInfoMessages(): array
    {
        return $this->getValidationResults()['infoMessages'];
    }

    public function getWarningMessages(): array
    {
        return $this->getValidationResults()['warningMessages'];
    }

    public function getErrorMessages(): array
    {
        return $this->getValidationResults()['errorMessages'];
    }

    public function getValidationResultStatus(): string
    {
        return $this->getValidationResults()['status'];
    }

    public function getQr(): string
    {
        return (new GetQrFromInvoice)->handle($this->getClearedInvoice());
    }

    public function getQrImage(): string
    {
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($this->getQr())->toHtml();
    }
}
