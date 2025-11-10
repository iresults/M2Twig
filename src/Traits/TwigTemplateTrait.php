<?php

declare(strict_types=1);

namespace Iresults\M2Twig\Traits;

use Iresults\M2Twig\Framework\View\TemplateEngine\Twig as TwigTemplateEngine;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Profiler;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\TemplateEnginePool;
use Twig\TwigFilter;
use Twig\TwigFunction;

use function array_merge;
use function get_class;
use function method_exists;
use function pathinfo;
use function strpos;

use const PATHINFO_EXTENSION;

trait TwigTemplateTrait
{
    /**
     * Retrieve url of a view file
     *
     * @phpstan-return string
     */
    abstract public function getViewFileUrl(
        $fileId,
        array $params = [],
    );

    /**
     * Template context
     *
     * @var BlockInterface
     */
    protected $templateContext;

    public function fetchTwigView(
        string $fileName,
        TemplateEnginePool $templateEnginePool,
        BlockInterface $templateContext,
        array $data,
    ): ?string {
        $relativeFilePath = $this->detectRelativeFilePath($fileName);
        Profiler::start(
            'TEMPLATE:' . $fileName,
            ['group' => 'TEMPLATE', 'file_name' => $relativeFilePath]
        );

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        /** @var TwigTemplateEngine $templateEngine */
        $templateEngine = clone $templateEnginePool->get($extension);

        $this->addDefaultFunctionsAndFilters($templateEngine);
        $this->prepareEngine($templateEngine);

        $templateVariables = array_merge(
            $this->getAdditionalViewVars(),
            $data,
            $this->_viewVars ?? [],
            ['block' => $this]
        );

        $html = $templateEngine->render(
            $templateContext,
            $fileName,
            $templateVariables
        );
        Profiler::stop('TEMPLATE:' . $fileName);

        return $html;
    }

    public function getAssetUrl(string $asset, array $params = []): string
    {
        if (strpos($asset, '::')) {
            return $this->getViewFileUrl($asset, $params);
        } elseif (method_exists($this, 'getModuleName')) {
            return $this->getViewFileUrl(
                $this->getModuleName() . '::' . $asset,
                $params
            );
        } else {
            return $this->getViewFileUrl(
                AbstractBlock::extractModuleName(get_class($this)) . '::' . $asset,
                $params
            );
        }
    }

    /**
     * Prepare the Twig Engine
     *
     * Overwrite this method to add additional Filters or Functions to Twig
     */
    protected function prepareEngine(TwigTemplateEngine $templateEngine): void
    {
    }

    /**
     * Return a dictionary of additional view variables
     */
    protected function getAdditionalViewVars(): array
    {
        return [];
    }

    protected function registerTwigFunction(
        TwigTemplateEngine $templateEngine,
        string $name,
        callable $callback,
        array $options = [],
    ): void {
        $templateEngine->addFunction(new TwigFunction(
            $name,
            $callback,
            $options
        ));
    }

    protected function registerTwigFilter(
        TwigTemplateEngine $templateEngine,
        string $name,
        callable $callback,
        array $options = [],
    ): void {
        $templateEngine->addFilter(new TwigFilter(
            $name,
            $callback,
            $options
        ));
    }

    private function addDefaultFunctionsAndFilters(
        TwigTemplateEngine $templateEngine,
    ): void {
        $this->registerTwigFunction(
            $templateEngine,
            'viewFileUrl',
            $this->getViewFileUrl(...)
        );
        $this->registerTwigFunction(
            $templateEngine,
            'assetUrl',
            $this->getAssetUrl(...)
        );
    }

    private function detectRelativeFilePath(string $fileName): string
    {
        if (method_exists($this, 'getRootDirectory')) {
            return $this->getRootDirectory()->getRelativePath($fileName);
        } else {
            $objectManager = ObjectManager::getInstance();
            $filesystem = $objectManager->create(Filesystem::class);

            return $filesystem->getDirectoryRead(DirectoryList::ROOT)
                ->getRelativePath($fileName);
        }
    }
}
