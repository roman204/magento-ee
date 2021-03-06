<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/LICENSE
 */

namespace WirecardEE\PaymentGateway\Payments\Contracts;

/**
 * @since 1.2.0
 */
interface DisplayRestrictionInterface
{
    /**
     * @param \Mage_Checkout_Model_Session $checkoutSession
     *
     * @return bool
     *
     * @since 1.2.0
     */
    public function checkDisplayRestrictions(\Mage_Checkout_Model_Session $checkoutSession);
}
