<?php

namespace Playbloom;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Same as MicroKernelTrait::configureContainer(), plus the optional config/parameters.yml
     * (created from parameters.yml.dist by "composer install"). It overrides the defaults
     * of services.yaml, while services_{env}.yaml (e.g. for tests) still takes precedence.
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/{packages}/*.{php,yaml}');
        $container->import($configDir . '/{packages}/' . $this->environment . '/*.{php,yaml}');
        $container->import($configDir . '/services.yaml');
        $container->import($configDir . '/{parameters}.yml');
        $container->import($configDir . '/{services}_' . $this->environment . '.yaml');
    }
}
