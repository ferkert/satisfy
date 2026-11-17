<?php

namespace Playbloom\Tests\Controller;

use org\bovigo\vfs\vfsStream;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        vfsStream::setup();
    }

    public function testSubmitDefaultValues()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/admin/configuration');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $form = $crawler->filterXPath('//form');
        $client->submit($form->form());
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testAddStabilityPerPackage(): void
    {
        $values = $this->getMinimalValues();
        $values['configuration']['minimumStabilityPerPackage'] = [
            [
                'package' => $package = 'psr/log',
                'stability' => $stability = 'alpha',
            ],
        ];

        $client = self::createClient();
        $client->request('POST', '/admin/configuration', $values);
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $form = $client->getCrawler()->filterXPath('//form')->form();
        $values = $form->getValues();

        self::assertArrayHasKey('configuration[minimumStabilityPerPackage][0][package]', $values);
        self::assertArrayHasKey('configuration[minimumStabilityPerPackage][0][stability]', $values);
        self::assertEquals($package, $values['configuration[minimumStabilityPerPackage][0][package]']);
        self::assertEquals($stability, $values['configuration[minimumStabilityPerPackage][0][stability]']);
    }

    /**
     * Every new collection entry needs its own model object; a shared empty_data
     * instance made all entries added in one submit end up with the last entry's values.
     */
    public function testAddSeveralCollectionEntriesAtOnce(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/admin/configuration');
        $token = $crawler->filterXPath('//form')->form()->get('configuration[_token]')->getValue();

        $client->request('POST', '/admin/configuration', ['configuration' => [
            '_token' => $token,
            'name' => 'test/test',
            'homepage' => 'http://test.com',
            'outputDir' => 'public',
            'require' => [
                ['package' => 'acme/one', 'constraint' => '^1.0'],
                ['package' => 'acme/two', 'constraint' => '^2.0'],
            ],
            'minimumStabilityPerPackage' => [
                ['package' => 'acme/one', 'stability' => 'alpha'],
                ['package' => 'acme/two', 'stability' => 'beta'],
            ],
            'abandoned' => [
                ['package' => 'acme/old', 'replacement' => 'acme/new'],
                ['package' => 'acme/older', 'replacement' => ''],
            ],
        ]]);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSelectorExists('.alert.theme-success');

        $saved = json_decode(file_get_contents('vfs://root/satis.json'), true);
        self::assertSame(['acme/one' => '^1.0', 'acme/two' => '^2.0'], $saved['require']);
        self::assertSame(['acme/one' => 'alpha', 'acme/two' => 'beta'], $saved['minimum-stability-per-package']);
        self::assertSame(['acme/old' => 'acme/new', 'acme/older' => true], $saved['abandoned']);
    }

    protected function getMinimalValues(): array
    {
        $values = json_decode(file_get_contents(__DIR__ . '/../fixtures/satis-minimal.json'), true);

        return [
            'configuration' => $values,
        ];
    }
}
