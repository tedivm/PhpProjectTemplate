<?php

namespace Spark;

use Symfony\Component\Console\Input\InputInterface;

use Spark\Plugins\Core\Plugin;

class Package
{
    protected $name;
    protected $plugins = array();
    protected $pluginObjects = array();

    public function __construct($packageName)
    {
        $this->name = $packageName;
        $packageInfo = new PackageInfo($this->name);
        $this->plugins = $packageInfo->getPlugins();
        foreach ($this->plugins as $plugin) {
           $this->pluginObjects[$plugin] = PluginManager::getPluginObject($plugin);
        }
    }

    public function setTags($tags, InputInterface $input)
    {
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);
            $pluginObject->setTags($tags, $input);
        }

        return $tags;
    }

    public function getConfig($tags, InputInterface $input)
    {
        $config = array();
        foreach ($this->plugins as $plugin) {
            /** @var $pluginObject Plugin */
            $pluginObject = $this->getPluginObject($plugin);
            $config = $pluginObject->getConfig($config, $tags, $input);
        }

        return $config;
    }

    public function getTemplateFiles(InputInterface $input)
    {
        $templates = array('files' => array(), 'directories' => array());
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);
            $templatesItems = $pluginObject->getTemplateFiles($input);
            $templates = array_merge_recursive($templates, $templatesItems);
        }

        return $templates;
    }

    public function getTemplateSources()
    {
        $sources = array();
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);

            $path = $pluginObject->getPath('Templates');
            if ($path !== false) {
                $sources[] = $path;
            }
        }

        return $sources;
    }

    public function getPermissions()
    {
        $permissions = array();
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);
            $permissions = array_merge_recursive($permissions, $pluginObject->getPermissions());
        }

        return $permissions;
    }

    protected function getPluginObject($plugin)
    {
        return $this->pluginObjects[$plugin];
    }
}
