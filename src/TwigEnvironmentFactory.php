<?php

declare(strict_types=1);

namespace Iresults\M2Twig;

use Iresults\M2Twig\Twig\Loader\FilesystemLoader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\UrlInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use const DIRECTORY_SEPARATOR;

class TwigEnvironmentFactory
{
    private const TWIG_CACHE_DIR = 'twig';

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ManagerInterface $eventManager,
        private readonly UrlInterface $urlBuilder,
        private readonly FilesystemLoader $filesystemLoader,
    ) {
    }

    /**
     * Initialize Twig with all Magento 2 necessary functions
     */
    public function create(): Environment
    {
        $environment = new Environment($this->filesystemLoader);
        $environment->setCache($this->getCachePath());
        if ($this->isDebugEnabled()) {
            $environment->enableDebug();
        } else {
            $environment->disableDebug();
        }
        if ($this->scopeConfig->isSetFlag('dev/twig/auto_reload')
            || $this->isDebugEnabled()) {
            $environment->enableAutoReload();
        } else {
            $environment->disableAutoReload();
        }
        if ($this->scopeConfig->isSetFlag('dev/twig/strict_variables')) {
            $environment->enableStrictVariables();
        } else {
            $environment->disableStrictVariables();
        }
        $charset = $this->scopeConfig->getValue('dev/twig/charset');
        if ($charset) {
            $environment->setCharset($charset);
        }

        $this->registerDefaultFiltersAndFunctions($environment);
        $environment->addExtension(new DebugExtension());

        $this->eventManager->dispatch('twig_init', ['twig' => $environment]);

        return $environment;
    }

    /**
     * @throws FileSystemException
     */
    private function getCachePath(): string|false
    {
        if (false === $this->scopeConfig->isSetFlag('dev/twig/cache')) {
            return false;
        }

        return $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . DIRECTORY_SEPARATOR
            . self::TWIG_CACHE_DIR;
    }

    private function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('dev/twig/debug');
    }

    private function registerDefaultFiltersAndFunctions(
        Environment $environment,
    ): void {
        $environment->addFilter(new TwigFilter('translate', __(...)));
        $environment->addFunction(new TwigFunction('translate', __(...)));

        $environment->addFilter(new TwigFilter(
            'url',
            $this->urlBuilder->getUrl(...)
        ));
        $environment->addFunction(new TwigFunction(
            'url',
            $this->urlBuilder->getUrl(...)
        ));
    }
}
