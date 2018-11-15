<?php


namespace Omnipay\Paynl\Message\Request;


use Omnipay\Paynl\Message\Response\InstoreResponse;

/**
 * Class RefundRequest
 * @package Omnipay\Paynl\Message\Request
 *
 * @method InstoreResponse send()
 */
class InstoreRequest extends AbstractPaynlRequest
{
    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('tokenCode', 'apiToken', 'transactionReference', 'terminalId');

        $data = [
            'transactionId' => $this->getTransactionReference() ?: null,
            'terminalId' => $this->getTerminalId() ?: null,
        ];

        return $data;
    }

    /**
     * @param array $data
     * @return \Omnipay\Common\Message\ResponseInterface|InstoreResponse
     */
    public function sendData($data)
    {
        $responseData = $this->sendRequest('instore/payment', $data);
        return $this->response = new InstoreResponse($this, $responseData);
    }
}