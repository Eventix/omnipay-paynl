<?php

namespace Omnipay\Paynl\Test\Message;


use Omnipay\Paynl\Message\Request\InstoreRequest;
use Omnipay\Paynl\Message\Response\InstoreResponse;
use Omnipay\Tests\TestCase;

class InstoreRequestTest extends TestCase
{
    /**
     * @var RefundRequest
     */
    protected $request;

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('InstoreSuccess.txt');

        $transactionId = uniqid();
        $terminalId = uniqid();

        $this->request->setTransactionReference($transactionId);
        $this->request->setTerminalId($terminalId);

        $response = $this->request->send();

        $this->assertInstanceOf(InstoreResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('InstoreError.txt');

        $transactionId = uniqid();
        $terminalId = uniqid();

        $this->request->setTransactionReference($transactionId);
        $this->request->setTerminalId($terminalId);

        $response = $this->request->send();

        $this->assertInstanceOf(InstoreResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertNotEmpty($response->getMessage());
    }

    public function testTerminal()
    {
        $transactionId = uniqid();
        $terminalId = uniqid();

        $this->request->setTransactionReference($transactionId);
        $this->request->setTerminalId($terminalId);

        $data = $this->request->getData();

        $this->assertEquals($terminalId, $data['terminalId']);
    }

    protected function setUp()
    {
        $this->request = new InstoreRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize([
            'tokenCode' => 'AT-1234-1234',
            'apiToken' => 'some-token'
        ]);
    }
}