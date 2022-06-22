<?php
include_once './vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public $panel;
    public function __construct()
    {
        parent::__construct();
        $this->panel = new cPanel\API();
    }

    public function testEstabilishConnection()
    {
        $this->assertTrue($this->panel->checkConnection());
    }

    public function testCreateAPackage()
    {
        $payload = [
            'name' => 'plano',
            'disk' => 1024,
            'bwlimit' => 'unlimited'
        ];
        $result = $this->panel->addPackage($payload);
        $this->assertTrue($result->status);
    }

    public function testGetAPackage()
    {
        $result = $this->panel->getPackage('plano');
        $this->assertTrue($result->status);
    }

    public function testUpdateAPackage()
    {
        $payload = [
            'name' => 'plano',
            'disk' => 2048,
            'bwlimit' => 80000
        ];
        $result = $this->panel->editPackage($payload);
        $this->assertTrue($result->status);
    }

    public function testCreateAAccount()
    {
        $payload = [
            'domain' => 'codemax.com.br',
            'user' => 'codemax',
            'plan' => 'plano'
        ];

        $result = $this->panel->createAccount($payload);
        $this->assertTrue($result->status);
    }

    public function testListAllAccounts()
    {
        $result = $this->panel->listAccounts();
        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('domain', $result[0]);
    }

    public function testListAllPackages()
    {
        $result = $this->panel->listPackages();
        $this->assertIsArray($result);
        $this->assertObjectHasAttribute('name', $result[0]);
    }

    public function testUpdatePasswordAccount()
    {
        $result = $this->panel->updatePassword(['user' => 'codemax', 'password' => 'Himura08@']);
        $this->assertTrue($result->status);
    }

    public function testUpgradeBrandwithOfAccount()
    {
        $result = $this->panel->limitBand('codemax', 40000);
        $this->assertTrue($result->status);
    }

    public function testUpgradeQuotaOfAccount()
    {
        $result = $this->panel->limitDisk('codemax', 2000);
        $this->assertTrue($result->status);
    }

    public function testChangePlanOfAccount()
    {
        $result = $this->panel->changePackage('codemax', 'teste');
        $this->assertTrue($result->status);
    }

    public function testSummaryOfAccount()
    {
        $result = $this->panel->summaryAccount('codemax');
        $this->assertTrue($result->status);
    }

    public function testModifyAAccount()
    {
        $payload = [
            'user' => 'codemax',
            'disk' => 2048,
            'bwlimit' => 80000
        ];
        $result = $this->panel->modifyAccount($payload);
        $this->assertIsBool($result->status);
    }

    public function testSuspendAAccount()
    {
        $result = $this->panel->suspendAccount('codemax');
        $this->assertTrue($result->status);
    }

    public function testUnsuspendAAccount()
    {
        $result = $this->panel->unsuspendAccount('codemax');
        $this->assertTrue($result->status);
    }

    public function testTerminateAAccount()
    {
        $result = $this->panel->terminateAccount('codemax');
        $this->assertTrue($result->status);
    }

    public function testDeleteAPackage()
    {
        $result = $this->panel->deletePackage('plano');
        $this->assertTrue($result->status);
    }
}