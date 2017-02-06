<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Core\Plugin;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Yosymfony\Spress\Core\Support\AttributesResolver;

/**
 * Plugin manager builder.
 *
 * @author Victor Puertas <vpgugr@gmail.com>
 */
class PluginManagerBuilder
{
    protected $path;
    protected $excludeDirs;
    protected $resolver;
    protected $eventDispatcher;
    protected $composerFilename;

    /**
     * Constructor.
     *
     * @param string          $path            Path to plugin folder
     * @param EventDispatcher $eventDispatcher
     * @param array           $excludeDirs     List of directories excluded of the scan
     *                                         for discovering class. Tests directories are a good example
     */
    public function __construct(
        $path,
        EventDispatcher $eventDispatcher,
        $excludeDirs = [])
    {
        $this->path = $path;
        $this->excludeDirs = $excludeDirs;
        $this->eventDispatcher = $eventDispatcher;
        $this->resolver = $this->getResolver();
        $this->setComposerFilename('composer.json');
    }

    /**
     * Sets the composer filename. "composer.json" by default.
     *
     * @param string $name The filename
     */
    public function setComposerFilename($name)
    {
        $this->composerFilename = $name;
    }

    /**
     * Builds the PluginManager with the plugins of a site.
     *
     * Each plugin is added to PluginManager using "name"
     * meta or its classname if that meta do not exists.
     *
     * @return PluginManager
     */
    public function build()
    {
        $pm = new PluginManager($this->eventDispatcher);
        $pluginCollection = $pm->getPluginCollection();

        if (empty($this->path) === true || file_exists($this->path) === false) {
            return $pm;
        }

        $composerClassname = [];

        $finder = $this->buildFinder();

        foreach ($finder as $file) {
            $classname = $this->getClassname($file);

            if (empty($classname) === true) {
                continue;
            }

            if ($file->getFilename() === $this->composerFilename) {
                $composerClassname[] = $classname;

                continue;
            }

            include_once $file->getRealPath();

            if ($this->isValidClassName($classname) === false) {
                continue;
            }

            $plugin = new $classname();

            $metas = $this->getPluginMetas($plugin);

            $pluginCollection->add($metas['name'], $plugin);
        }

        foreach ($composerClassname as $classname) {
            if ($this->isValidClassName($classname) === true) {
                $plugin = new $classname();

                $metas = $this->getPluginMetas($plugin);

                $pluginCollection->add($metas['name'], $plugin);
            }
        }

        return $pm;
    }

    /**
     * Extracts the class name from the filename.
     *
     * @param SplFileInfo $file The file. This could be a PHP file or a Composer.json file
     *
     * @return string
     */
    protected function getClassname(SplFileInfo $file)
    {
        if ($file->getExtension() === 'php') {
            return $file->getBasename('.php');
        }

        if ($file->getFilename() === $this->composerFilename) {
            $composerData = $this->readComposerFile($file);

            if (isset($composerData['extra']['spress_class']) === true && is_string($composerData['extra']['spress_class'])) {
                return $composerData['extra']['spress_class'];
            }
        }

        return '';
    }

    /**
     * Gets metas of a plugin.
     *
     * @param string          $filename The plugin filename
     * @param PluginInterface $plugin   The plugin
     *
     * @return array
     *
     * @throws RuntimeException If bad metas
     */
    protected function getPluginMetas(PluginInterface $plugin)
    {
        $metas = $plugin->getMetas();

        if (is_array($metas) === false) {
            $classname = get_class($plugin);

            throw new \RuntimeException(sprintf('Expected an array at method "getMetas" of the plugin: "%s".', $classname));
        }

        $metas = $this->resolver->resolve($metas);

        if (empty($metas['name']) === true) {
            $metas['name'] = get_class($plugin);
        }

        return $metas;
    }

    /**
     * Checks if the class implements the PluginInterface.
     *
     * @param string $name Class's name
     *
     * @return bool
     */
    protected function isValidClassName($name)
    {
        $result = false;

        if (class_exists($name)) {
            $implements = class_implements($name);

            if (isset($implements['Yosymfony\\Spress\\Core\\Plugin\\PluginInterface'])) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Reads a "composer.json" file.
     *
     * @param SplFileInfo $file The file
     *
     * @return array The parsed JSON filename
     */
    protected function readComposerFile(SplFileInfo $file)
    {
        $json = $file->getContents();
        $data = json_decode($json, true);

        return $data;
    }

    /**
     * Gets the attribute resolver.
     *
     * @return AttributesResolver
     */
    protected function getResolver()
    {
        $resolver = new AttributesResolver();
        $resolver->setDefault('name', '', 'string')
            ->setDefault('description', '', 'string')
            ->setDefault('author', '', 'string')
            ->setDefault('license', '', 'string');

        return $resolver;
    }

    /**
     * Returns a Finder set up for finding both composer.json and php files.
     *
     * @return Finder A Symfony Finder instance
     */
    protected function buildFinder()
    {
        $finder = new Finder();
        $finder->files()
            ->name('/(\.php|composer\.json)$/')
            ->in($this->path)
            ->exclude($this->excludeDirs);

        return $finder;
    }
}
