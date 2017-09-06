<?php

return [
    \ZanPHP\Nova\Exception\FrameworkException::class => \Kdt\Iron\Nova\Exception\FrameworkException::class,

    \ZanPHP\Nova\Service\Initator::class => \Kdt\Iron\Nova\Service\Initator::class,
    \ZanPHP\Nova\Service\NovaConfig::class => \Kdt\Iron\Nova\Service\NovaConfig::class,
    \ZanPHP\Nova\Service\Registry::class => \Kdt\Iron\Nova\Service\Registry::class,
    \ZanPHP\Nova\Service\Scanner::class => \Kdt\Iron\Nova\Service\Scanner::class,

    \ZanPHP\Nova\Utils\Arr::class => \Kdt\Iron\Nova\Utils\Arr::class,
    \ZanPHP\Nova\Utils\Dir::class => \Kdt\Iron\Nova\Utils\Dir::class,
    \ZanPHP\Nova\Utils\Entity::class => \Kdt\Iron\Nova\Utils\Entity::class,
    \ZanPHP\Nova\Utils\Str::class => \Kdt\Iron\Nova\Utils\Str::class,

    \ZanPHP\Nova\Nova::class => \Kdt\Iron\Nova\Nova::class,

];