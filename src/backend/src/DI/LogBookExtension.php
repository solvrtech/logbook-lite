<?php

namespace App\DI;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LogBookExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));

        // load logbook configuration
        $loader->load('logbook_services.yaml');

        // load configuration based on storage
        $database = $_ENV['DATABASE_TYPE'] ?? "postgresql";

        if ("postgresql" === strtolower($database)) {
            $loader->load('postgres_storage.yaml');
        } else {
            $loader->load('mysql_storage.yaml');
        }
    }
}