<?php
declare(strict_types=1);

namespace Iresults\M2Twig\Framework\View\TemplateEngine;

use Iresults\M2Twig\TwigEnvironmentFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEngine\Php;
use Twig\Environment;
use Twig\Error\Error;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use Twig\TwigFunction;

class Twig extends Php
{
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
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ObjectManagerInterface $helperFactory
     * @param DirectoryList          $directoryList
     * @param ScopeConfigInterface   $scopeConfig
     * @param UrlInterface           $urlBuilder
     * @param TwigEnvironmentFactory $environmentFactory
     * @throws FileSystemException
     */
    public function __construct(
        ObjectManagerInterface $helperFactory,
        DirectoryList $directoryList,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        TwigEnvironmentFactory $environmentFactory
    ) {
        parent::__construct($helperFactory);
        $this->directoryList = $directoryList;
        $this->scopeConfig = $scopeConfig;
        $this->twig = $environmentFactory->create();
        $this->urlBuilder = $urlBuilder;
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

    private function getTemplate(string $fileName): TemplateWrapper
    {
        $path = str_replace($this->directoryList->getPath(DirectoryList::ROOT) . DIRECTORY_SEPARATOR, '', $fileName);

        return $this->twig->load($path);
    }

    private function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('dev/twig/debug');
    }
}
