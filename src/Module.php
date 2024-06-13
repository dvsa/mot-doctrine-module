<?php

namespace DvsaDoctrineModule;

class Module
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * @return array
     */
    public function getModuleDependencies()
    {
        return array();
    }
}
