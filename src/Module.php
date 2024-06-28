<?php

namespace DvsaDoctrineModule;

class Module
{
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getModuleDependencies(): array
    {
        return array();
    }
}
