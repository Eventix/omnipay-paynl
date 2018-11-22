<?php

namespace Omnipay\Paynl\Message\Request;

use Omnipay\Common\Item;
use Omnipay\Paynl\Common\StatsData;
use Omnipay\Paynl\Common\StatsDataInterface;
use Omnipay\Paynl\Message\Response\PurchaseResponse;

/**
 * Class PurchaseRequest
 * @package Omnipay\Paynl\Message\Request
 *
 * @method PurchaseResponse send()
 */
class PurchaseRequest extends AbstractPaynlRequest
{
    /**
     * Regex to find streetname, housenumber and suffix out of a street string
     * @var string
     */
    private $addressRegex = '#^([a-z0-9 [:punct:]\']*) ([0-9]{1,5})([a-z0-9 \-/]{0,})$#i';

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('tokenCode', 'apiToken', 'serviceId', 'amount', 'clientIp', 'returnUrl');

        // Mandatory fields
        $data = [
            'serviceId' => $this->getServiceId(),
            'amount' => $this->getAmountInteger(),
            'ipAddress' => $this->getClientIp(),
            'finishUrl' => $this->getReturnUrl(),
        ];

        $data['transaction'] = [];
        $data['transaction']['description'] = $this->getDescription() ?: null;
        $data['transaction']['currency'] = !empty($this->getCurrency()) ? $this->getCurrency() : 'EUR';
        $data['transaction']['orderExchangeUrl'] = !empty($this->getNotifyUrl()) ? $this->getNotifyUrl() : null;

        $data['testMode'] = $this->getTestMode() ? 1 : 0;
        $data['paymentOptionId'] = !empty($this->getPaymentMethod()) ? $this->getPaymentMethod() : null;
        $data['paymentOptionSubId'] = !empty($this->getIssuer()) ? $this->getIssuer() : null;

        if ($card = $this->getCard()) {
            $billingAddressParts = $this->getAddressParts($card->getBillingAddress1() . ' ' . $card->getBillingAddress2());
            $shippingAddressParts = ($card->getShippingAddress1() ? $this->getAddressParts($card->getShippingAddress1() . ' ' . $card->getShippingAddress2()) : $billingAddressParts);

            $data['enduser'] = [
                'initials' => $card->getFirstName(), //Pay has no support for firstName, but some methods require full name. Conversion to initials is handled by Pay.nl based on the payment method.
                'lastName' => $card->getLastName(),
                'gender' => $card->getGender(), //Should be inserted in the CreditCard as M/F
                'dob' => $card->getBirthday('d-m-Y'),
                'phoneNumber' => $card->getPhone(),
                'emailAddress' => $card->getEmail(),
                'language' => substr($card->getCountry(), 0, 2),
                'customerReference' => $this->getCustomerReference(),
                'address' => array(
                    'streetName' => isset($shippingAddressParts[1]) ? $shippingAddressParts[1] : null,
                    'streetNumber' => isset($shippingAddressParts[2]) ? $shippingAddressParts[2] : null,
                    'streetNumberExtension' => isset($shippingAddressParts[3]) ? $shippingAddressParts[3] : null,
                    'zipCode' => $card->getShippingPostcode(),
                    'city' => $card->getShippingCity(),
                    'countryCode' => $card->getShippingCountry(),
                    'regionCode' => $card->getShippingState()
                ),
                'invoiceAddress' => array(
                    'initials' => $card->getBillingFirstName(),
                    'lastName' => $card->getBillingLastName(),
                    'streetName' => isset($billingAddressParts[1]) ? $billingAddressParts[1] : null,
                    'streetNumber' => isset($billingAddressParts[2]) ? $billingAddressParts[2] : null,
                    'streetNumberExtension' => isset($billingAddressParts[3]) ? $billingAddressParts[3] : null,
                    'zipCode' => $card->getBillingPostcode(),
                    'city' => $card->getBillingCity(),
                    'countryCode' => $card->getBillingCountry(),
                    'regionCode' => $card->getBillingState()
                )
            ];
        }

        if ($items = $this->getItems()) {
            $data['saleData']['orderData'] = array_map(function ($item) {
                    /** @var Item $item */
                    $data = [
                        'description' => $item->getName() ?: $item->getDescription(),
                        'price' => round($item->getPrice() * 100),
                        'quantity' => $item->getQuantity(),
                        'vatCode' => 0,
                    ];
                    if (method_exists($item, 'getProductId')) {
                        $data['productId'] = $item->getProductId();
                    } else {
                        $data['productId'] = substr($item->getName(), 0, 25);
                    }
                    if (method_exists($item, 'getProductType')) {
                        $data['productType'] = $item->getProductType();
                    }
                    if (method_exists($item, 'getVatPercentage')) {
                        $data['vatPercentage'] = $item->getVatPercentage();
                    }
                    return $data;
                }, $items->all());
        }
        if ($invoiceDate = $this->getInvoiceDate()) {
            $data['saleData']['invoiceDate'] = $invoiceDate; //dd-mm-yyyy
        }
        if ($deliveryDate = $this->getDeliveryDate()) {
            $data['saleData']['deliveryDate'] = $deliveryDate; //dd-mm-yyyy
        }

        if ($statsData = $this->getStatsData()) {
            $data['statsData'] = [
                "promotorId" => $statsData->getPromotorId(),
                "info" => $statsData->getInfo(),
                "tool" => $statsData->getTool(),
                "extra1" => $statsData->getExtra1(),
                "extra2" => $statsData->getExtra2(),
                "extra3" => $statsData->getExtra3(),
                "transferData" => $statsData->getTransferData(),
                "domainId" => $statsData->getDomainId(),
            ];
        }

        return $data;
    }

    /**
     * @param array $data
     * @return \Omnipay\Common\Message\ResponseInterface|PurchaseResponse
     */
    public function sendData($data)
    {
        $responseData = $this->sendRequest('transaction/start', $data);

        return $this->response = new PurchaseResponse($this, $responseData);
    }

    /**
     * Get the identifier for a customer
     *
     * @return string
     */
    public function getCustomerReference()
    {
        return $this->getParameter('customerReference');
    }

    /**
     * Set the customer reference
     *
     * @param string $value
     * @return \Omnipay\Paynl\Message\Request\PurchaseRequest
     */
    public function setCustomerReference(string $value)
    {
        return $this->setParameter('customerReference', $value);
    }

    /**
     * Get the parts of an address
     * @param string $address
     * @return array
     */
    public function getAddressParts($address)
    {
        $addressParts = [];
        preg_match($this->addressRegex, trim($address), $addressParts);
        return array_filter($addressParts, 'trim');
    }

    /**
     * Get the invoice date
     *
     * Pay accepts dd-mm-yyyy
     *
     * @return string
     */
    public function getInvoiceDate()
    {
        return $this->getParameter('invoiceDate');
    }

    /**
     * Set the invoice date
     *
     * Pay accepts dd-mm-yyyy
     *
     * @param string $value
     * @return \Omnipay\Paynl\Message\Request\PurchaseRequest
     */
    public function setInvoiceDate(string $value)
    {
        return $this->setParameter('invoiceDate', $value);
    }

    /**
     * Get the delivery date
     *
     * Pay accepts dd-mm-yyyy
     *
     * @return string
     */
    public function getDeliveryDate()
    {
        return $this->getParameter('deliveryDate');
    }

    /**
     * Set the delivery date
     *
     * Pay accepts dd-mm-yyyy
     *
     * @param string $value
     * @return \Omnipay\Paynl\Message\Request\PurchaseRequest
     */
    public function setDeliveryDate(string $value)
    {
        return $this->setParameter('deliveryDate', $value);
    }

    /**
     * Get the stats data
     *
     * @return \Omnipay\Paynl\Common\StatsDataInterface|null A bag containing the stats data
     */
    public function getStatsData()
    {
        return $this->getParameter('statsData');
    }

    /**
     * Set the stats data in this order
     *
     * @param \Omnipay\Paynl\Common\StatsDataInterface|array $statsData An array of stats data in this order
     * @return \Omnipay\Paynl\Message\Request\PurchaseRequest
     */
    public function setStatsData($statsData)
    {
        if ($statsData && !$statsData instanceof StatsDataInterface) {
            $statsData = new StatsData($statsData);
        }

        return $this->setParameter('statsData', $statsData);
    }
}
