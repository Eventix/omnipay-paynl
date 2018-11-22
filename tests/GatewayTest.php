<?php

namespace Omnipay\Paynl\Test;

use Omnipay\Common\Message\RequestInterface;
use Omnipay\Paynl\Gateway;
use Omnipay\Paynl\Message\Request\FetchIssuersRequest;
use Omnipay\Paynl\Message\Request\FetchPaymentMethodsRequest;
use Omnipay\Paynl\Message\Request\FetchTransactionRequest;
use Omnipay\Paynl\Message\Request\PurchaseRequest;
use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var Gateway
     */
    protected $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway();
    }

    public function testSupportsInstore()
    {
        $supportsInstore = $this->gateway->supportsInstore();
        $this->assertInternalType('boolean', $supportsInstore);

        if ($supportsInstore) {
            $this->assertInstanceOf(RequestInterface::class, $this->gateway->instore());
        } else {
            $this->assertFalse(method_exists($this->gateway, 'instore'));
        }
    }

    public function testInstoreParameters()
    {
        if ($this->gateway->supportsInstore()) {
            foreach ($this->gateway->getDefaultParameters() as $key => $default) {
                // set property on gateway
                $getter = 'get'.ucfirst($this->camelCase($key));
                $setter = 'set'.ucfirst($this->camelCase($key));
                $value = uniqid();
                $this->gateway->$setter($value);

                // request should have matching property, with correct value
                $request = $this->gateway->instore();
                $this->assertSame($value, $request->$getter());
            }
        }
    }

    public function testFetchIssuers()
    {
        $request = $this->gateway->fetchIssuers();
        $this->assertInstanceOf(FetchIssuersRequest::class, $request);
    }

    public function testFetchPaymentMethods()
    {
        $request = $this->gateway->fetchPaymentMethods();
        $this->assertInstanceOf(FetchPaymentMethodsRequest::class, $request);
    }

    public function testFetchTransaction()
    {
        $request = $this->gateway->fetchTransaction();
        $this->assertInstanceOf(FetchTransactionRequest::class, $request);
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase();
        $this->assertInstanceOf(PurchaseRequest::class, $request);
    }
}
