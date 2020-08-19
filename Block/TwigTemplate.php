<?php
declare(strict_types=1);

namespace Iresults\M2Twig\Block;

use Iresults\M2Twig\Framework\View\TemplateEngine\Twig as TwigTemplateEngine;
use Magento\Framework\Profiler;
use Magento\Framework\View\Element\Template;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function pathinfo;
use function strpos;
use const PATHINFO_EXTENSION;

class TwigTemplate extends Template
{
    public function fetchView($fileName)
    {
        if (!$this->validator->isValid($fileName)) {
            return parent::fetchView($fileName);
        }

        $relativeFilePath = $this->getRootDirectory()->getRelativePath($fileName);
        Profiler::start(
            'TEMPLATE:' . $fileName,
            ['group' => 'TEMPLATE', 'file_name' => $relativeFilePath]
        );

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        /** @var TwigTemplateEngine $templateEngine */
        $templateEngine = clone $this->templateEnginePool->get($extension);

        $this->addDefaultFunctionsAndFilters($templateEngine);
        $this->prepareEngine($templateEngine);

        $templateVariables = array_merge(
            $this->getAdditionalViewVars(),
            $this->getData(),
            $this->_viewVars,
            ['block' => $this]
        );

        $html = $templateEngine->render($this->templateContext, $fileName, $templateVariables);
        Profiler::stop('TEMPLATE:' . $fileName);

        return $html;
    }

    public function getAssetUrl($asset, array $params = [])
    {
        if (strpos($asset, '::')) {
            return $this->getViewFileUrl($asset, $params);
        } else {
            return $this->getViewFileUrl($this->getModuleName() . '::' . $asset, $params);
        }
    }

    /**
     * Prepare the Twig Engine
     *
     * Overwrite this method to add additional Filters or Functions to Twig
     *
     * @param TwigTemplateEngine $templateEngine
     */
    protected function prepareEngine(TwigTemplateEngine $templateEngine)
    {
    }

    /**
     * Return a dictionary of additional view variables
     *
     * @return array
     */
    protected function getAdditionalViewVars(): array
    {
        return [];
    }

    protected function registerTwigFunction(TwigTemplateEngine $templateEngine, string $name, callable $callback)
    {
        $templateEngine->addFunction(new TwigFunction($name, $callback));
    }

    protected function registerTwigFilter(TwigTemplateEngine $templateEngine, string $name, callable $callback)
    {
        $templateEngine->addFilter(new TwigFilter($name, $callback));
    }

    private function addDefaultFunctionsAndFilters(TwigTemplateEngine $templateEngine)
    {
        //$templateEngine->addFilter(new TwigFilter('url', [$this, 'getUrl']));
        //$templateEngine->addFunction(new TwigFunction('url', [$this, 'getUrl']));
        $this->registerTwigFunction($templateEngine, 'viewFileUrl', [$this, 'getViewFileUrl']);
        $this->registerTwigFunction($templateEngine, 'assetUrl', [$this, 'getAssetUrl']);
    }
}
