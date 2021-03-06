<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/LICENSE
 */

namespace WirecardEE\PaymentGateway\Payments;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\PaymentGateway\Data\OrderSummary;
use WirecardEE\PaymentGateway\Data\PaymentConfig;
use WirecardEE\PaymentGateway\Payments\Contracts\ProcessPaymentInterface;

class AlipayPayment extends Payment implements ProcessPaymentInterface
{
    const NAME = AlipayCrossborderTransaction::NAME;

    /**
     * @var AlipayCrossborderTransaction
     */
    private $transactionInstance;

    /**
     * @return string
     *
     * @since 1.2.0
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @return AlipayCrossborderTransaction
     *
     * @since 1.2.0
     */
    public function getTransaction()
    {
        if (! $this->transactionInstance) {
            $this->transactionInstance = new AlipayCrossborderTransaction();
        }
        return $this->transactionInstance;
    }

    /**
     * @param string $selectedCurrency
     *
     * @return Config
     *
     * @since 1.2.0
     */
    public function getTransactionConfig($selectedCurrency)
    {
        $config = parent::getTransactionConfig($selectedCurrency);
        $config->add(new PaymentMethodConfig(
            AlipayCrossborderTransaction::NAME,
            $this->getPaymentConfig()->getTransactionMAID(),
            $this->getPaymentConfig()->getTransactionSecret()
        ));

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentConfig()
    {
        $paymentConfig = new PaymentConfig(
            $this->getPluginConfig('api_url'),
            $this->getPluginConfig('api_user'),
            $this->getPluginConfig('api_password')
        );

        $paymentConfig->setTransactionMAID($this->getPluginConfig('api_maid'));
        $paymentConfig->setTransactionSecret($this->getPluginConfig('api_secret'));
        $paymentConfig->setTransactionOperation(Operation::PAY);
        $paymentConfig->setFraudPrevention($this->getPluginConfig('fraud_prevention'));
        $paymentConfig->setOrderIdentification($this->getPluginConfig('order_identification'));

        return $paymentConfig;
    }

    /**
     * @param OrderSummary       $orderSummary
     * @param TransactionService $transactionService
     * @param Redirect           $redirect
     *
     * @return null
     *
     * @since 1.2.0
     */
    public function processPayment(
        OrderSummary $orderSummary,
        TransactionService $transactionService,
        Redirect $redirect
    ) {
        if (! $this->getPaymentConfig()->hasFraudPrevention()) {
            $billingAddress = $orderSummary->getOrder()->getBillingAddress();
            $accountHolder  = new AccountHolder();
            $accountHolder->setLastName($billingAddress->getFirstname());
            $accountHolder->setFirstName($billingAddress->getLastname());
            $this->getTransaction()->setAccountHolder($accountHolder);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefundOperation()
    {
        return Operation::CANCEL;
    }
}
