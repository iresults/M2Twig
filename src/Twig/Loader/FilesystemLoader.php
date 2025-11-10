<?php

declare(strict_types=1);

namespace Iresults\M2Twig\Twig\Loader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template\File\Resolver;

class FilesystemLoader extends \Twig\Loader\FilesystemLoader
{
    /**
     * @param string|string[] $paths
     */
    public function __construct(
        protected readonly DirectoryList $directoryList,
        protected readonly Resolver $resolver,
        $paths = [],
    ) {
        $paths[] = './';
        parent::__construct($paths, $directoryList->getRoot());
    }

    protected function findTemplate(string $name, bool $throw = true): ?string
    {
        if (false !== stristr($name, '::')) {
            $rootPath = $this->directoryList->getPath(DirectoryList::ROOT)
                . DIRECTORY_SEPARATOR;
            $templateName = $this->resolver->getTemplateFileName($name);
            $templateName = str_replace(
                $rootPath,
                '',
                $templateName
            );

            return parent::findTemplate($templateName, $throw);
        } else {
            return parent::findTemplate($name, $throw);
        }
    }
}
