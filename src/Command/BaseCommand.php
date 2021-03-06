<?php

/*
 * This file is part of the Yosymfony\Spress.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\Spress\Command;

use Yosymfony\EmbeddedComposer\EmbeddedComposerBuilder;
use Symfony\Component\Console\Command\Command;
use Yosymfony\Spress\Core\IO\IOInterface;
use Yosymfony\Spress\PackageManager\PackageManager;

/**
 * Base for commands.
 *
 * @author Victor Puertas <vpgugr@gmail.com>
 */
class BaseCommand extends Command
{
    /**
     * A shortcut for $this->getApplication->getSpress().
     *
     * @param string $siteDir Root directory of an Spress site. "./" if this
     *                        value is null
     *
     * @return Spress A Spress instance
     */
    public function getSpress($siteDir = null)
    {
        return $this->getApplication()->getSpress($siteDir);
    }

    /**
     * Returns the skeletons directory.
     *
     * @return string Path to skeletons directory
     */
    public function getSkeletonsDir()
    {
        return __DIR__.'/../../app/skeletons';
    }

    /**
     * Returns an instance of PackageManager.
     * It is configured to read a composer.json file.
     *
     * @param string $siteDir Root directory of an Spress site
     *
     * @return PackageManager
     */
    public function getPackageManager($siteDir, IOInterface $io)
    {
        $embeddedComposer = $this->getEmbeddedComposer($siteDir, 'composer.json', 'vendor');
        $embeddedComposer->processAdditionalAutoloads();

        return new PackageManager($embeddedComposer, $io);
    }

    /**
     * Returns an EmbeddedComposer instance.
     *
     * @return Dflydev\EmbeddedComposer\Core\EmbeddedComposer
     */
    protected function getEmbeddedComposer($siteDir, $composerFilename, $vendorDir)
    {
        $classloader = $this->getApplication()->getClassloader();
        $builder = new EmbeddedComposerBuilder($classloader, $siteDir);

        return $builder
            ->setComposerFilename($composerFilename)
            ->setVendorDirectory($vendorDir)
            ->build();
    }
}
