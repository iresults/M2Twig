<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
declare(strict_types=1);

namespace Iresults\M2Twig\Framework\View\TemplateEngine;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockInterface;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\DebugExtension;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Twig extends \Magento\Framework\View\TemplateEngine\Php
{
    private const TWIG_CACHE_DIR = 'twig';

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * Event manager
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ObjectManagerInterface $helperFactory
     * @param DirectoryList          $directoryList
     * @param ScopeConfigInterface   $scopeConfig
     * @param ManagerInterface       $eventManager
     * @param UrlInterface           $urlBuilder
     * @param Environment            $twig
     * @throws FileSystemException
     */
    public function __construct(
        ObjectManagerInterface $helperFactory,
        DirectoryList $directoryList,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $eventManager,
        UrlInterface $urlBuilder,
        Environment $twig
    ) {
        parent::__construct($helperFactory);
        $this->directoryList = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->twig = $twig;
        $this->urlBuilder = $urlBuilder;
        $this->initTwig();
    }

    public function render(BlockInterface $block, $fileName, array $dictionary = [])
    {
        $tmpBlock = $this->_currentBlock;
        $this->_currentBlock = $block;
        $this->twig->addGlobal('block', $block);

        try {
            $result = $this->getTemplate($fileName)->render($dictionary);
        } catch (Error $error) {
            if ($this->isDebugEnabled()) {
                return "<pre>$error</pre>";
            }
            throw $error;
        }
        $this->_currentBlock = $tmpBlock;

        return $result;
    }

    /**
     * Initialises Twig with all Magento 2 necessary functions
     *
     * @throws FileSystemException
     */
    private function initTwig()
    {
        $this->twig->setCache($this->getCachePath());
        if ($this->isDebugEnabled()) {
            $this->twig->enableDebug();
        } else {
            $this->twig->disableDebug();
        }
        if ($this->scopeConfig->isSetFlag('dev/twig/auto_reload') || $this->isDebugEnabled()) {
            $this->twig->enableAutoReload();
        } else {
            $this->twig->disableAutoReload();
        }
        if ($this->scopeConfig->isSetFlag('dev/twig/strict_variables')) {
            $this->twig->enableStrictVariables();
        } else {
            $this->twig->disableStrictVariables();
        }
        $this->twig->setCharset($this->scopeConfig->getValue('dev/twig/charset'));

        $this->registerDefaultFiltersAndFunctions();
        $this->twig->addExtension(new DebugExtension());

        $this->eventManager->dispatch('twig_init', ['twig' => $this->twig]);
    }

    public function addFunction(TwigFunction $function): self
    {
        $this->twig->addFunction($function);

        return $this;
    }

    public function addFilter(TwigFilter $filter): self
    {
        $this->twig->addFilter($filter);

        return $this;
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getCachePath()
    {
        if (false === $this->scopeConfig->isSetFlag('dev/twig/cache')) {
            return false;
        }

        return $this->directoryList->getPath(DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . self::TWIG_CACHE_DIR;
    }

    private function getTemplate(string $fileName): TemplateWrapper
    {
        $path = str_replace($this->directoryList->getPath(DirectoryList::ROOT) . DIRECTORY_SEPARATOR, '', $fileName);

        return $this->twig->load($path);
        //return $this->twig->loadTemplate($path);
    }

    /**
     * @return bool
     */
    private function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('dev/twig/debug');
    }

    private function registerDefaultFiltersAndFunctions(): void
    {
        $this->twig->addFunction(new TwigFunction('helper', [$this, 'helper']));
        $this->twig->addFilter(new TwigFilter('translate', '__'));
        $this->twig->addFunction(new TwigFunction('translate', '__'));

        $this->twig->addFilter(new TwigFilter('url', [$this->urlBuilder, 'getUrl']));
        $this->twig->addFunction(new TwigFunction('url', [$this->urlBuilder, 'getUrl']));
    }
}
