<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/LICENSE
 */

namespace WirecardEE\Tests\Unit\Payments;

use InvalidArgumentException;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\Operation;
use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use Wirecard\PaymentSdk\TransactionService;
use WirecardEE\PaymentGateway\Data\OrderSummary;
use WirecardEE\PaymentGateway\Data\PaymentConfig;
use WirecardEE\PaymentGateway\Exception\InsufficientDataException;
use WirecardEE\PaymentGateway\Payments\PtwentyfourPayment;
use WirecardEE\Tests\Test\MagentoTestCase;

class PtwentyfourPaymentTest extends MagentoTestCase
{
    const CURRENCY = 'PLN';

    public function testPayment()
    {
        $payment     = new PtwentyfourPayment();
        $transaction = $payment->getTransaction();
        $this->assertInstanceOf(PtwentyfourTransaction::class, $transaction);
        $this->assertSame($transaction, $payment->getTransaction());

        $this->assertInstanceOf(Config::class, $payment->getTransactionConfig(self::CURRENCY));
        $this->assertInstanceOf(PaymentConfig::class, $payment->getPaymentConfig());

        $order       = $this->createMock(\Mage_Sales_Model_Order::class);
        $transaction = $this->createMock(\Mage_Sales_Model_Order_Payment_Transaction::class);
        $this->assertInstanceOf(
            PtwentyfourTransaction::class,
            $payment->getBackendTransaction($order, Operation::PAY, $transaction)
        );
        $this->assertInstanceOf(
            PtwentyfourTransaction::class,
            $payment->getBackendTransaction($order, Operation::CANCEL, $transaction)
        );
    }

    public function testProcessPaymentSuccess()
    {
        $payment = $this->processPaymentWrapper();
        $this->assertNull($payment);
    }

    public function testProcessPaymentWrongCurrency()
    {
        $this->expectException(InvalidArgumentException::class);
        $payment = $this->processPaymentWrapper('EUR');

        $this->assertNotNull($payment);
    }

    public function testProcessPaymentNoMail()
    {
        $this->expectException(InsufficientDataException::class);
        $payment = $this->processPaymentWrapper(self::CURRENCY, false);

        $this->assertNotNull($payment);
    }

    protected function processPaymentWrapper($currency = self::CURRENCY, $mailSet = true)
    {
        $payment     = $this->getMockBuilder(PtwentyfourPayment::class)
            ->setMethods(['fetchBillingAddressMail'])
            ->getMock();
        $transaction = $payment->getTransaction();
        $transaction->setOperation(Operation::PAY);
        $transaction->setLocale('en_US');

        $orderSummary       = $this->createMock(OrderSummary::class);
        $transactionService = $this->createMock(TransactionService::class);
        $redirect           = $this->createMock(Redirect::class);
        $order              = $this->createMock(\Mage_Sales_Model_Order::class);

        $orderSummary->method('getOrder')->willReturn($order);
        $order->method('getRealOrderId')->willReturn('ABC123');
        $order->method('__call')->willReturnMap([
            ['getBaseGrandTotal', [], '10.0'],
            ['getBaseCurrencyCode', [], $currency],
        ]);

        if ($mailSet) {
            $payment->method('fetchBillingAddressMail')->willReturn('test@mail.com');
        } else {
            $payment->method('fetchBillingAddressMail')->willReturn(null);
        }

        return $payment->processPayment($orderSummary, $transactionService, $redirect);
    }
}
