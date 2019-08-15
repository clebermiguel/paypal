<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from SARL 202 ecommence
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL 202 ecommence is strictly forbidden.
 * In order to obtain a license, please contact us: tech@202-ecommerce.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe 202 ecommence
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la SARL 202 ecommence est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter 202-ecommerce <tech@202-ecommerce.com>
 * ...........................................................................
 *
 * @author    202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license   Commercial license
 */

namespace PayPalTest;

$pathConfig = dirname(__FILE__) . '/../../../../config/config.inc.php';
$pathInit = dirname(__FILE__) . '/../../../../init.php';
if (file_exists($pathConfig)) {
    require_once $pathConfig;
}
if (file_exists($pathInit)) {
    require_once $pathInit;
}
require_once _PS_MODULE_DIR_.'paypal/vendor/autoload.php';
require_once _PS_MODULE_DIR_.'paypal/classes/MethodPPP.php';

use PHPUnit\Framework\TestCase;
use PayPal\Rest\ApiContext;
use PayPal\Api\CreateProfileResponse;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PayPalTest\MethodPPPMock;

class MethodPPPTest extends TestCase
{
    /* @var MethodPPPMock*/
    protected $method;

    public $moduleManagerBuilder;

    public $moduleManager;

    public $moduleNames;

    protected function setUp()
    {
        $methodMock = new MethodPPPMock();
        $this->method = $methodMock->getInstance();
        $this->moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager = $this->moduleManagerBuilder->build();
        $this->moduleNames = 'paypal';
    }

    public function testInstall()
    {
        $employees = \Employee::getEmployeesByProfile(_PS_ADMIN_PROFILE_);
        $contextTest = \Context::getContext();
        $contextTest->employee = new \Employee((int)$employees[0]['id_employee']);
        $contextTest->cookie->update();
        \Context::setInstanceForTesting($contextTest);
        $this->assertTrue((bool)$this->moduleManager->install($this->moduleNames), "Could not install $this->moduleNames");
    }

    /**
     * @dataProvider getDataForGetCredentialsInfo
     */
    public function testGetCredentialsInfo($mode)
    {
        $this->assertInstanceOf(ApiContext::class, $this->method->_getCredentialsInfo($mode));
    }

    public function testCreateWebExperience()
    {
        $webExp = $this->method->createWebExperience();
        $this->assertTrue($webExp instanceof CreateProfileResponse || $webExp === false);
    }

    /**
     * @dataProvider getDataForCredentialsSetted
     */
    public function testCredentialsSetted($mode)
    {
        $this->assertTrue(is_bool($this->method->credentialsSetted($mode)));
    }

    /**
     * @dataProvider getDataForFormatPrice
     */
    public function testFormatPrice($price)
    {
        $priceFormated = $this->method->formatPrice($price);
        $this->assertTrue(is_string($priceFormated));
    }

    /**
     * @dataProvider getDataForGetInstructionInfo
     */
    public function testGetInstructionInfo($id_payment)
    {
        $instructionInfo = $this->method->getInstructionInfo($id_payment);
        $this->assertTrue(is_object($instructionInfo) || $instructionInfo === false);
    }

    /**
     * @dataProvider getDataForGetLinkToTransaction
     */
    public function testGetLinkToTransaction($id_transaction, $sandbox)
    {
        $this->assertTrue(is_string($this->method->getLinkToTransaction($id_transaction, $sandbox)));
    }

    /**
     * @dataProvider getDataForIsConfigured
     */
    public function testIsConfigured()
    {
        $this->assertTrue(is_bool($this->method->isConfigured()));
    }

    public function testInit()
    {
        $this->assertTrue(is_string($this->method->init()));
    }

    /**
     * @dataProvider getDataForRenderExpressCheckoutShortCut
     */
    public function testRenderExpressCheckoutShortCut($context, $type, $page_source)
    {
        $this->assertTrue(is_string($this->method->renderExpressCheckoutShortCut($context, $type, $page_source)));
    }

    public function getDataForGetCredentialsInfo()
    {
        $data = array(
            array(1),
            array(0),
            array('string'),
            array(00),
            array(null),
        );
        return $data;
    }

    public function getDataForCredentialsSetted()
    {
        $data = $this->getDataForGetCredentialsInfo();
        return$data;
    }

    public function getDataForFormatPrice()
    {
        $data = array(
            array(1),
            array(023),
            array('123'),
            array(00),
            array(null),
        );
        return $data;
    }

    public function getDataForGetInstructionInfo()
    {
        $data = $this->getDataForGetCredentialsInfo();
        return $data;
    }

    public function getDataForGetLinkToTransaction()
    {
        $data = array(
            array(1, 1),
            array(0, 0),
            array('string', 1),
            array(00, 'string'),
            array(null, null),
        );
        return $data;
    }

    public function getDataForIsConfigured()
    {
        $data = $this->getDataForGetCredentialsInfo();
        return $data;
    }

    public function getDataForRenderExpressCheckoutShortCut()
    {
        $context = \Context::getContext();
        $data = array(
            array($context, 'EC', 'product'),
            array($context, 'PPP', 'product'),
            array($context, 'BT', 'order'),
            array($context, 'EC', 'order'),
            array($context, 'PPP', 'order'),
            array($context, 'string', 'string'),
            array($context, null, null)
        );
        return $data;
    }
}
