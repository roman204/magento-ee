<?php
/**
 * Shop System Plugins:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/magento-ee/blob/master/LICENSE
 */

// See https://magento.stackexchange.com/a/143990

require_once __DIR__ . '/../../../app/Mage.php';

//Define global ip address for tests
$_SERVER['REMOTE_ADDR'] = '123.123.1.1';

function fix_error_handler()
{
    $mageErrorHandler = set_error_handler(function () {
        return false;
    });
    set_error_handler(function ($errno, $errstr, $errfile) use ($mageErrorHandler) {
        if (substr($errfile, -19) === 'Varien/Autoload.php') {
            return null;
        }
        return is_callable($mageErrorHandler) ?
            call_user_func_array($mageErrorHandler, func_get_args()) :
            false;
    });
}

fix_error_handler();

Mage::app('', 'store', [
    'config_model' => 'WirecardEE\Tests\Test\Stubs\Config',
]);
Mage::setIsDeveloperMode(true);

fix_error_handler();

$_SESSION = [];
$_SERVER['REMOTE_ADDR']  = '127.0.0.1';

if (! Mage::getConfig()->getModuleConfig('WirecardEE_PaymentGateway')->is('active', true)) {
    throw new \RuntimeException('The Wirecard Elastic Engine extension is not enabled!');
}
