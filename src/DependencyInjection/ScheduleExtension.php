<?php

namespace Adamski\Symfony\ScheduleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ScheduleExtension extends Extension {

    /**
     * @var string
     */
    public static string $serviceName = "schedule_bundle.manager";

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (array_key_exists("manager", $config)) {
            $managerClass = $config["manager"];

            if ($managerClass && class_exists($managerClass)) {
                $container->register(self::$serviceName, $managerClass);
                $container->setAlias($managerClass, self::$serviceName);
            }
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../Resources/config"));
        $loader->load("services.yaml");
    }
}
