<?php

namespace point\core;

/**
 * Class PlatformClassLoader
 *
 * @author sj
 */
class PlatformClassLoader
{
    /**
     * @Autowired
     * @var \point\core\Runtime
     */
    private $_runtime;

    /**
     * Class auto load
     *
     * @param String $fullClassName
     * @return bool   load class success or class not found
     * @throws \Exception
     */
    public function loadClass($fullClassName)
    {

        // make class real path (PEAR style)
        $currentPluginId = $this->_runtime->getCurrentPluginId();
        $pluginInfo = $this->_runtime->getPluginConfig($currentPluginId);
        $arr = explode('\\', $fullClassName);
        $className = array_pop($arr);
        $parentPluginId = implode('.', $arr);

        if ($parentPluginId === $currentPluginId) {
            // search in current plugin (nothing to do)
        } elseif (array_key_exists('Depends', $pluginInfo) && in_array($parentPluginId, $pluginInfo['Depends'])) {
            // search in depend plugins of current plugin
            $pluginInfo = $this->_runtime->getPluginConfig($parentPluginId);
        } elseif ($parentPluginId !== '') {
            throw new \Exception(
                'Try to load a class in non-dependency plugin \'' .
                $parentPluginId . '\'. (' . $fullClassName . ') current: ' . $currentPluginId
            );
        }

        // search classes
        foreach ($pluginInfo['Class-Path'] as $classPath) {
            $filename = $pluginInfo['Path'] . $classPath . '/' . implode('/', explode('_', $className)) . '.php';
            if (is_file($filename)) {
                $this->_runtime->setCurrentPluginId($parentPluginId);
                require_once $filename;
                $this->_runtime->restoreCurrentPluginId();
                return class_exists($fullClassName, false);
            }
        }

        return false;
    }
}
