<?php

namespace Playbloom\Tests\Validator;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Playbloom\Model\Configuration;
use Playbloom\Service\Manager;
use Playbloom\Validator\EnvValidator;

class EnvValidatorTest extends TestCase
{
    public function testRelativeComposerHomeIsResolvedAgainstProjectRoot(): void
    {
        $root = vfsStream::setup('root', null, [
            'satis.json' => '{}',
            'public' => [],
            'var' => ['composer' => []],
        ]);

        $validator = new EnvValidator($root->url(), $root->url() . '/satis.json', 'var/composer', $this->createManager());

        $this->expectNotToPerformAssertions();
        $validator->validate();
    }

    public function testMissingComposerHomeIsReported(): void
    {
        $root = vfsStream::setup('root', null, ['satis.json' => '{}', 'public' => []]);

        $validator = new EnvValidator($root->url(), $root->url() . '/satis.json', 'var/composer', $this->createManager());

        $this->expectExceptionMessage('Missing composer home directory');
        $validator->validate();
    }

    private function createManager(): Manager
    {
        $manager = $this->createStub(Manager::class);
        $manager->method('getConfig')->willReturn(new Configuration());

        return $manager;
    }
}
