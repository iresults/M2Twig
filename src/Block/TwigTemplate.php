<?php
declare(strict_types=1);

namespace Iresults\M2Twig\Block;

use Iresults\M2Twig\Framework\View\TemplateEngine\Twig as TwigTemplateEngine;
use Iresults\M2Twig\Traits\TwigTemplateTrait;
use Magento\Framework\View\Element\Template;

class TwigTemplate extends Template
{
    use TwigTemplateTrait;

    public function fetchView($fileName)
    {
        if (!$this->validator->isValid($fileName)) {
            return parent::fetchView($fileName);
        } else {
            return $this->fetchTwigView($fileName, $this->templateEnginePool, $this->templateContext, $this->getData());
        }
    }

    /**
     * Prepare the Twig Engine
     *
     * Overwrite this method to add additional Filters or Functions to Twig
     *
     * @param TwigTemplateEngine $templateEngine
     */
    protected function prepareEngine(TwigTemplateEngine $templateEngine): void
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
}
