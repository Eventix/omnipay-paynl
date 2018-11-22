<?php

namespace Omnipay\Paynl\Common;

/**
 * Interface StatsDataInterface
 *
 * This interface defines the functionality that all stats data in
 * the Pay system are to have.
 */
interface StatsDataInterface
{
    /**
     * The id of a promotor / affiliate
     *
     * @return integer
     */
    public function getPromotorId();

    /**
     * The used info code which can be tracked in the stats
     *
     * @return string
     */
    public function getInfo();

    /**
     * The used tool code which can be tracked in the stats
     *
     * @return string
     */
    public function getTool();

    /**
     * The first free value which can be tracked in the stats
     *
     * @return string
     */
    public function getExtra1();

    /**
     * The second free value which can be tracked in the stats
     *
     * @return string
     */
    public function getExtra2();

    /**
     * The third free value which can be tracked in the stats
     *
     * @return string
     */
    public function getExtra3();

    /**
     * Option to send multiple values via an array which can be tracked in the stats
     *
     * @return array
     */
    public function getTransferData();

    /**
     * The ID of the duplicate content URL
     *
     * @see https://admin.pay.nl/docpanel/api/Service/getDuplicateUrls/3 API_Service_v3::getDuplicateUrls()
     *
     * @return string
     */
    public function getDomainId();
}
