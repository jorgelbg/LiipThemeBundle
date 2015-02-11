<?php

namespace Liip\ThemeBundle\Loader;


use Liip\ThemeBundle\ActiveTheme;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader as BaseFilesystemLoader;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

class FilesystemLoader extends BaseFilesystemLoader
{
    public function __construct(FileLocatorInterface $locator, TemplateNameParserInterface $parser, ActiveTheme $activeTheme)
    {
        $this->activeTheme = $activeTheme;
        parent::__construct($locator, $parser);
    }

    protected function findTemplate($template)
    {
        $logicalName = (string)$template;
        $cacheKey = $logicalName;
        if($theme = $this->activeTheme->getName()) {
            $cacheKey = $cacheKey . '|' . $theme;
        }

        if(isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $file = parent::findTemplate($template);

        unset($this->cache[$logicalName]);
        $this->cache[$cacheKey] = $file;

        return $file;
    }

}