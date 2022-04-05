<?php
declare(strict_types=1);

namespace Iresults\M2Twig\Twig\Loader;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Element\Template\File\Resolver;

class FilesystemLoader extends \Twig\Loader\FilesystemLoader
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * Filesystem Loader constructor
     *
     * @param DirectoryList $directoryList
     * @param Resolver      $resolver
     * @param array         $paths
     */
    public function __construct(
        DirectoryList $directoryList,
        Resolver $resolver,
        $paths = []
    ) {
        $this->directoryList = $directoryList;
        $this->resolver = $resolver;
        $paths[] = './';
        parent::__construct($paths, $directoryList->getRoot());
    }

    protected function findTemplate(string $name, bool $throw = true)
    {
        if (stristr($name, '::') !== false) {
            $templateName = $this->resolver->getTemplateFileName($name);
            $templateName = str_replace(
                $this->directoryList->getPath(DirectoryList::ROOT) . DIRECTORY_SEPARATOR,
                '',
                $templateName
            );

            return parent::findTemplate($templateName, $throw);
        } else {
            return parent::findTemplate($name, $throw);
        }
    }
}
