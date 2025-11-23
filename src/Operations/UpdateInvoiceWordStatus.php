<?php

declare(strict_types=1);

namespace ecPay\eInvoice\Operations;

use ecPay\eInvoice\Content;
use ecPay\eInvoice\InvoiceInterface;
use Exception;

class UpdateInvoiceWordStatus extends Content
{
    /**
     * API endpoint path.
     *
     * @var string
     */
    protected $requestPath = '/B2CInvoice/UpdateInvoiceWordStatus';

    /**
     * Initialize request payload.
     *
     * @return void
     */
    protected function initContent()
    {
        $this->content['Data'] = [
            'MerchantID' => $this->merchantID,
            'TrackID' => '',
            'InvoiceStatus' => null,
        ];
    }

    /**
     * Set the track identifier.
     *
     * @param string $trackId
     * @return InvoiceInterface
     */
    public function setTrackID(string $trackId): self
    {
        $trackId = trim($trackId);

        if ($trackId === '') {
            throw new Exception('TrackID cannot be empty.');
        }

        if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $trackId)) {
            throw new Exception('TrackID must be 1-10 alphanumeric characters.');
        }

        $this->content['Data']['TrackID'] = $trackId;

        return $this;
    }

    /**
     * Set the track status.
     *
     * @param int $status
     * @return InvoiceInterface
     */
    public function setInvoiceStatus(int $status): self
    {
        if (!in_array($status, [0, 1, 2], true)) {
            throw new Exception('InvoiceStatus only supports 0(disable), 1(pause), or 2(enable).');
        }

        $this->content['Data']['InvoiceStatus'] = $status;

        return $this;
    }

    /**
     * Validate payload.
     *
     * @return void
     */
    public function validation()
    {
        $this->validatorBaseParam();

        if (empty($this->content['Data']['TrackID'])) {
            throw new Exception('TrackID cannot be empty.');
        }

        if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $this->content['Data']['TrackID'])) {
            throw new Exception('TrackID must be 1-10 alphanumeric characters.');
        }

        if (!in_array($this->content['Data']['InvoiceStatus'], [0, 1, 2], true)) {
            throw new Exception('InvoiceStatus only supports 0(disable), 1(pause), or 2(enable).');
        }
    }
}

