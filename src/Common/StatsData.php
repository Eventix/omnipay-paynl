<?php

namespace Omnipay\Paynl\Common;

use Omnipay\Common\Helper;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class StatsData
 *
 * This class defines a collection of stats data in the Pay system
 */
class StatsData implements StatsDataInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameters;

    /**
     * Create a new stats data container with the specified parameters
     *
     * @param array|null $parameters An array of parameters to set on the new object
     */
    public function __construct(array $parameters = null)
    {
        $this->initialize($parameters);
    }

    /**
     * Initialize this stats data container with the specified parameters
     *
     * @param array|null $parameters An array of parameters to set on this object
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function initialize(array $parameters = null)
    {
        $this->parameters = new ParameterBag;

        Helper::initialize($this, $parameters);

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters->all();
    }

    protected function getParameter($key)
    {
        return $this->parameters->get($key);
    }

    protected function setParameter($key, $value)
    {
        $this->parameters->set($key, $value);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPromotorId()
    {
        return $this->getParameter('promotorId');
    }

    /**
     * Set the promotor id
     *
     * @param integer $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setPromotorId(int $value)
    {
        return $this->setParameter('promotorId', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getInfo()
    {
        return $this->getParameter('info');
    }

    /**
     * Set the info
     *
     * @param string $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setInfo(string $value)
    {
        return $this->setParameter('info', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getTool()
    {
        return $this->getParameter('tool');
    }

    /**
     * Set the tool
     *
     * @param string $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setTool(string $value)
    {
        return $this->setParameter('tool', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtra1()
    {
        return $this->getParameter('extra1');
    }

    /**
     * Set the first free value
     *
     * @param string $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setExtra1(string $value)
    {
        return $this->setParameter('extra1', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtra2()
    {
        return $this->getParameter('extra2');
    }

    /**
     * Set the second free value
     *
     * @param string $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setExtra2(string $value)
    {
        return $this->setParameter('extra2', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getExtra3()
    {
        return $this->getParameter('extra3');
    }

    /**
     * Set the third free value
     *
     * @param string $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setExtra3(string $value)
    {
        return $this->setParameter('extra3', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getTransferData()
    {
        return $this->getParameter('transferData');
    }

    /**
     * Set the transfer data
     *
     * @param array $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setTransferData(array $value)
    {
        return $this->setParameter('transferData', $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getDomainId()
    {
        return $this->getParameter('domainId');
    }

    /**
     * Set the ID of the duplicate content URL
     *
     * @param string $value
     * @return \Omnipay\Paynl\Common\StatsData
     */
    public function setDomainId(string $value)
    {
        return $this->setParameter('domainId', $value);
    }
}
