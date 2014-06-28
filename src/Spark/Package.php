<?php

namespace Spark;

use Symfony\Component\Console\Input\InputInterface;

class Package
{
    protected $plugins = array();
    protected $pluginObjects = array();

    protected $files;
    protected $directories;

    protected $tags;

    public function __construct($packageName)
    {
        $config = $this->getConfig($packageName);
        $this->plugins = $config['plugins'];
        foreach ($this->plugins as $plugin) {
           $this->pluginObjects[$plugin] = PluginManager::getPluginObject($plugin);
        }

        $files = $this->getTemplateFiles();
        $this->files = $files['files'];
        $this->directories = $files['directories'];
    }

    public function setTags($tags, InputInterface $input)
    {
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);
            $pluginObject->setTags($tags, $input);
        }

        return $tags;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getTemplateFiles()
    {
        $templates = array('files' => array(), 'directories' => array());
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);
            $templatesItems = $pluginObject->getTemplateFiles();
            $templates = array_merge_recursive($templates, $templatesItems);
        }

        return $templates;
    }

    public function getTemplateSources()
    {
        $sources = array();
        foreach ($this->plugins as $plugin) {
            $pluginObject = $this->getPluginObject($plugin);
            $sources[] = $pluginObject->getPath('Templates');
        }

        return $sources;
    }

    protected function getPluginObject($plugin)
    {
        return $this->pluginObjects[$plugin];
    }

    protected function getConfig($package)
    {
        $resources = new Resources();
        $configPath = $resources->getPath('config');

        $packagesConfig = json_decode(file_get_contents($configPath . 'packages.json'), true);

        if (!isset($packagesConfig[$package])) {
            throw new \RuntimeException($package . ' is not a supported type.');
        }

        $config = $packagesConfig[$package];

        // Loop through parent packages and merge their config in.
        while (isset($config['extends'])) {
            $extends = $config['extends'];
            unset($config['extends']);
            $config = array_merge_recursive($config, $packagesConfig[$extends]);
        }

        return $config;
    }
}
