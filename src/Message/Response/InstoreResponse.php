<?php

namespace Omnipay\Paynl\Message\Response;


class InstoreResponse extends AbstractPaynlResponse
{
    /**
     * When you do a `purchase` the request is never successful because
     * you need to finish the payment on the terminal
     *
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        return isset($this->data['transaction']['issuerUrl']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl()
    {
        return isset($this->data['transaction']['issuerUrl']) ? $this->data['transaction']['issuerUrl'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectData()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTransactionReference()
    {
        return isset($this->data['transaction']['transactionId']) ? $this->data['transaction']['transactionId'] : null;
    }
}