<?php

namespace Omnipay\Paynl\Test\Message;


use Omnipay\Common\CreditCard;
use Omnipay\Paynl\Message\Request\PurchaseRequest;
use Omnipay\Paynl\Message\Response\PurchaseResponse;
use Omnipay\Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    /**
     * @var PurchaseRequest
     */
    protected $request;

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');

        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');

        $response = $this->request->send();
        $this->assertInstanceOf(PurchaseResponse::class, $response);

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());

        $this->assertInternalType('string', $response->getTransactionReference());
        $this->assertInternalType('string', $response->getRedirectUrl());
        $this->assertInternalType('string', $response->getAcceptCode());

        $this->assertEquals('GET', $response->getRedirectMethod());
        $this->assertNull($response->getRedirectData());
    }

    public function testCardEnduser()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');

        $card = $this->getValidCard();
        $objCard = new CreditCard($card);
        $this->request->setCard($objCard);

        $customerReference = uniqid();
        $this->request->setCustomerReference($customerReference);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['enduser']);
        $this->assertNotEmpty($data['enduser']['customerReference']);
        $enduser = $data['enduser'];

        $this->assertEquals($objCard->getFirstName(), $enduser['initials']);
        $this->assertEquals($objCard->getLastName(), $enduser['lastName']);
        $this->assertEquals($objCard->getBirthday('Y-m-d'), $enduser['dob']);
        $this->assertEquals($objCard->getPhone(), $enduser['phoneNumber']);
        $this->assertEquals($objCard->getEmail(), $enduser['emailAddress']);
        $this->assertEquals($customerReference, $enduser['customerReference']);
    }

    public function testCardAddress()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');

        $card = $this->getValidCard();
        $objCard = new CreditCard($card);
        $this->request->setCard($objCard);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['enduser']);
        $enduser = $data['enduser'];
        $this->assertNotEmpty($enduser['address']);
        $address = $enduser['address'];
        $this->assertNotEmpty($enduser['invoiceAddress']);

        $strAddress = $objCard->getShippingAddress1() . ' ' . $objCard->getShippingAddress2();
        $arrAddressParts = $this->request->getAddressParts($strAddress);

        if (isset($arrAddressParts[0])) $this->assertEquals($arrAddressParts[0], $address['streetName']);
        if (isset($arrAddressParts[1])) $this->assertEquals($arrAddressParts[1], $address['streetNumber']);
        if (isset($arrAddressParts[2])) $this->assertEquals($arrAddressParts[2], $address['streetNumberExtension']);

        $this->assertEquals($objCard->getShippingPostcode(), $address['zipCode']);
        $this->assertEquals($objCard->getShippingCity(), $address['city']);
        $this->assertEquals($objCard->getShippingCountry(), $address['countryCode']);
        $this->assertEquals($objCard->getShippingState(), $address['regionCode']);

    }

    public function testCardInvoiceAddress()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');

        $card = $this->getValidCard();
        $objCard = new CreditCard($card);
        $this->request->setCard($objCard);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['enduser']);
        $enduser = $data['enduser'];
        $this->assertNotEmpty($enduser['invoiceAddress']);
        $address = $enduser['invoiceAddress'];

        $strAddress = $objCard->getBillingAddress1() . ' ' . $objCard->getBillingAddress2();
        $arrAddressParts = $this->request->getAddressParts($strAddress);

        $this->assertEquals($objCard->getBillingFirstName(), $address['initials']);
        $this->assertEquals($objCard->getBillingLastName(), $address['lastName']);

        if (isset($arrAddressParts[0])) $this->assertEquals($arrAddressParts[0], $address['streetName']);
        if (isset($arrAddressParts[1])) $this->assertEquals($arrAddressParts[1], $address['streetNumber']);
        if (isset($arrAddressParts[2])) $this->assertEquals($arrAddressParts[2], $address['streetNumberExtension']);

        $this->assertEquals($objCard->getBillingPostcode(), $address['zipCode']);
        $this->assertEquals($objCard->getBillingCity(), $address['city']);
        $this->assertEquals($objCard->getBillingCountry(), $address['countryCode']);
        $this->assertEquals($objCard->getBillingState(), $address['regionCode']);

    }

    public function testPaynlSaleData()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');
        $this->request->setNotifyUrl('https://www.pay.nl/exchange');

        $invoiceDate = date("Y-m-d");
        $deliveryDate = date("Y-m-d");

        $this->request->setInvoiceDate($invoiceDate);
        $this->request->setDeliveryDate($deliveryDate);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['saleData']);
        $saleData = $data['saleData'];

        $this->assertEquals($invoiceDate, $saleData['invoiceDate']);
        $this->assertEquals($deliveryDate, $saleData['deliveryDate']);
    }

    public function testPaynlItem()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');
        $this->request->setNotifyUrl('https://www.pay.nl/exchange');

        $name = uniqid();
        $price = rand(1, 1000) / 100;
        $quantity = rand(1, 10);
        $productId = uniqid();
        $vatPercentage = rand(0, 21);

        $objItem = new \Omnipay\Paynl\Common\Item([
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'productId' => $productId,
            'productType' => \Omnipay\Paynl\Common\Item::PRODUCT_TYPE_ARTICLE,
            'vatPercentage' => $vatPercentage
        ]);

        $this->request->setItems([$objItem]);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['saleData']['orderData'][0]);
        $item = $data['saleData']['orderData'][0];

        $this->assertEquals($objItem->getProductId(), $item['productId']);
        $this->assertEquals($objItem->getName(), $item['description']);
        $this->assertEquals(round($objItem->getPrice() * 100), $item['price']);
        $this->assertEquals($objItem->getQuantity(), $item['quantity']);
        $this->assertEquals($objItem->getProductType(), $item['productType']);
        $this->assertEquals($objItem->getVatPercentage(), $item['vatPercentage']);
    }

    public function testStockItem()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');
        $this->request->setNotifyUrl('https://www.pay.nl/exchange');

        $name = uniqid();
        $price = rand(1, 1000) / 100;
        $quantity = rand(1, 10);

        $objItem = new \Omnipay\Common\Item([
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ]);

        $this->request->setItems([$objItem]);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['saleData']['orderData'][0]);
        $item = $data['saleData']['orderData'][0];

        $this->assertEquals($objItem->getName(), $item['description']);
        $this->assertEquals(round($objItem->getPrice() * 100), $item['price']);
        $this->assertEquals($objItem->getQuantity(), $item['quantity']);
    }

    public function testStatsDataObject()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');
        $this->request->setNotifyUrl('https://www.pay.nl/exchange');

        $promotorId = rand(1,100);
        $info = uniqid();
        $tool = uniqid();
        $extra1 = uniqid();
        $extra2 = uniqid();
        $extra3 = uniqid();
        $transferData = [
            'transferData1' => uniqid(),
            'transferData2' => rand(1,100),
            'transferData3' => true,
            'transferData4' => (-1) * 12.34,
        ];
        $domainId = uniqid();

        $objStatsData = new \Omnipay\Paynl\Common\StatsData([
            'promotorId' => $promotorId,
            'info' => $info,
            'tool' => $tool,
            'extra1' => $extra1,
            'extra2' => $extra2,
            'extra3' => $extra3,
            'transferData' => $transferData,
            'domainId' => $domainId,
        ]);

        $this->request->setStatsData($objStatsData);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['statsData']);
        $statsData = $data['statsData'];

        $this->assertArraySubset($objStatsData->getParameters(), $statsData);

        $this->assertEquals($objStatsData->getPromotorId(), $statsData['promotorId']);
        $this->assertEquals($objStatsData->getInfo(), $statsData['info']);
        $this->assertEquals($objStatsData->getTool(), $statsData['tool']);
        $this->assertEquals($objStatsData->getExtra1(), $statsData['extra1']);
        $this->assertEquals($objStatsData->getExtra2(), $statsData['extra2']);
        $this->assertEquals($objStatsData->getExtra3(), $statsData['extra3']);
        $this->assertArraySubset($objStatsData->getTransferData(), $statsData['transferData'], true);
        $this->assertEquals($objStatsData->getDomainId(), $statsData['domainId']);
    }

    public function testStatsDataArray()
    {
        $this->request->setAmount(1);
        $this->request->setClientIp('10.0.0.5');
        $this->request->setReturnUrl('https://www.pay.nl');
        $this->request->setNotifyUrl('https://www.pay.nl/exchange');

        $promotorId = rand(1,100);
        $info = uniqid();
        $tool = uniqid();
        $extra1 = uniqid();
        $extra2 = uniqid();
        $extra3 = uniqid();
        $transferData = [
            'transferData1' => uniqid(),
            'transferData2' => rand(1,100),
            'transferData3' => true,
            'transferData4' => (-1) * 12.34,
        ];
        $domainId = uniqid();

        $arrStatsData = [
            'promotorId' => $promotorId,
            'info' => $info,
            'tool' => $tool,
            'extra1' => $extra1,
            'extra2' => $extra2,
            'extra3' => $extra3,
            'transferData' => $transferData,
            'domainId' => $domainId,
        ];

        $this->request->setStatsData($arrStatsData);

        $data = $this->request->getData();

        $this->assertNotEmpty($data['statsData']);
        $statsData = $data['statsData'];

        $this->assertEquals($arrStatsData['promotorId'], $statsData['promotorId']);
        $this->assertEquals($arrStatsData['info'], $statsData['info']);
        $this->assertEquals($arrStatsData['tool'], $statsData['tool']);
        $this->assertEquals($arrStatsData['extra1'], $statsData['extra1']);
        $this->assertEquals($arrStatsData['extra2'], $statsData['extra2']);
        $this->assertEquals($arrStatsData['extra3'], $statsData['extra3']);
        $this->assertArraySubset($arrStatsData['transferData'], $statsData['transferData'], true);
        $this->assertEquals($arrStatsData['domainId'], $statsData['domainId']);
    }

    protected function setUp()
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize([
            'tokenCode' => 'AT-1234-5678',
            'apiToken' => 'some-token',
            'serviceId' => 'SL-1234-5678'
        ]);
    }
}
