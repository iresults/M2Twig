<?php
declare(strict_types=1);

namespace Iresults\M2Twig\Traits;

use Iresults\M2Twig\Framework\View\TemplateEngine\Twig as TwigTemplateEngine;
use Magento\Framework\Escaper;
use Twig\TwigFilter;

trait EscaperTwigFilterTrait
{
    public static function registerTwigFilters(TwigTemplateEngine $templateEngine)
    {
        $escaper = new Escaper();
        $templateEngine->addFilter(new TwigFilter('escapeHtml', [$escaper, 'escapeHtml']));
        $templateEngine->addFilter(new TwigFilter('escapeJs', [$escaper, 'escapeJs']));
        $templateEngine->addFilter(new TwigFilter('escapeHtmlAttr', [$escaper, 'escapeHtmlAttr']));
        $templateEngine->addFilter(new TwigFilter('escapeCss', [$escaper, 'escapeCss']));
        $templateEngine->addFilter(new TwigFilter('escapeUrl', [$escaper, 'escapeUrl']));
    }
}
