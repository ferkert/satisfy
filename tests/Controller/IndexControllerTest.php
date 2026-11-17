<?php

namespace Playbloom\Tests\Controller;

use org\bovigo\vfs\vfsStream;
use Playbloom\Controller\IndexController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class IndexControllerTest extends KernelTestCase
{
    public function testUnavailablePageWithoutBuiltIndex(): void
    {
        $response = $this->createController(vfsStream::setup()->url())->indexAction();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('<!DOCTYPE html>', ltrim((string) $response->getContent()));
        self::assertStringContainsString('Composer Repository currently not available', (string) $response->getContent());
    }

    public function testBuiltIndexIsServed(): void
    {
        $root = vfsStream::setup('root', null, ['public' => ['index.html' => '<html>packages</html>']]);

        $response = $this->createController($root->url())->indexAction();

        self::assertSame('<html>packages</html>', $response->getContent());
    }

    private function createController(string $projectDir): IndexController
    {
        $container = self::getContainer();
        $controller = new IndexController($projectDir);
        $controller->setContainer(new ServiceLocator(['twig' => static fn () => $container->get('twig')]));

        return $controller;
    }
}
