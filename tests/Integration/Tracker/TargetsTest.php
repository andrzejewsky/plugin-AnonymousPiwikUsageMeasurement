<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AnonymousPiwikUsageMeasurement\tests\Integration\Tracker;

use Piwik\Plugins\AnonymousPiwikUsageMeasurement\Settings;
use Piwik\Plugins\AnonymousPiwikUsageMeasurement\Tracker\Targets;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group AnonymousPiwikUsageMeasurement
 * @group Targets
 * @group TargetsTest
 * @group Plugins
 */
class TargetsTest extends IntegrationTestCase
{
    private $idSite = 1;

    public function setUp()
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2014-01-01 00:00:00');
        }
    }

    public function test_defaultSettings_ShouldOnlyReturnDemoPiwik()
    {
        $settings = $this->makeDefaultSettings();

        $piwikInstance = $this->getPiwikOrgTarget();

        $this->assertTargets(array($piwikInstance), $settings);
    }

    public function test_NoTargetEnabled_ShouldReturnNothing()
    {
        $settings = $this->makeSettingsWithNoInstanceEnabled();

        $this->assertTargets(array(), $settings);
    }

    public function test_TrackToOwnPiwikEnabled_ShouldReturnOwnInstanceTarget()
    {
        $settings = $this->makeSettingsWithNoInstanceEnabled();
        $settings->ownPiwikSiteId->setValue($this->idSite);

        $ownInstance = $this->getOwnPiwikTarget();

        $this->assertTargets(array($ownInstance), $settings);
    }

    public function test_TrackToCustomPiwikEnabled_ShouldReturnCustomInstanceTarget()
    {
        $settings = $this->makeSettingsWithNoInstanceEnabled();
        $settings->customPiwikSiteId->setValue(72);
        $settings->customPiwikSiteUrl->setValue('http://example.com/piwik');

        $customInstance = $this->getCustomPiwikTarget('http://example.com/piwik/piwik.php', 72);

        $this->assertTargets(array($customInstance), $settings);
    }

    public function test_TrackToCustomPiwikEnabled_ShoulNotReturnAnythingIfNotSiteAndUrlAreSet()
    {
        $settings = $this->makeSettingsWithNoInstanceEnabled();
        $settings->customPiwikSiteUrl->setValue('http://example.com/piwik');
        $this->assertTargets(array(), $settings);

        $settings = $this->makeSettingsWithNoInstanceEnabled();
        $settings->customPiwikSiteId->setValue(72);
        $this->assertTargets(array(), $settings);
    }

    public function test_AllEnabled_shouldReturnAll()
    {
        $settings = $this->makeSettingsWithNoInstanceEnabled();
        $settings->trackToPiwik->setValue(true);
        $settings->ownPiwikSiteId->setValue($this->idSite);
        $settings->customPiwikSiteUrl->setValue('http://example.com/piwik');
        $settings->customPiwikSiteId->setValue(73);

        $targets = array(
            $this->getPiwikOrgTarget(),
            $this->getOwnPiwikTarget(),
            $this->getCustomPiwikTarget('http://example.com/piwik/piwik.php', 73)
        );

        $this->assertTargets($targets, $settings);
    }

    private function makeDefaultSettings()
    {
        return new Settings();
    }

    private function makeSettingsWithNoInstanceEnabled()
    {
        $settings = $this->makeDefaultSettings();
        $settings->trackToPiwik->setValue(false);
        return $settings;
    }

    private function getPiwikOrgTarget()
    {
        return $this->getCustomPiwikTarget('http://demo-anonymous.piwik.org/piwik.php', 1);
    }

    private function getOwnPiwikTarget()
    {
        return $this->getCustomPiwikTarget(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/piwik.php', $this->idSite);
    }

    private function getCustomPiwikTarget($url, $idSite)
    {
        return array(
            'url' => $url,
            'idSite' => $idSite,
            'useAnonymization' => true
        );
    }

    private function assertTargets($expectedTargets, $settings)
    {
        $targets = new Targets($settings);

        $this->assertSame($expectedTargets, $targets->getTargets());
    }

}
