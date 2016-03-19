<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 16/3/18
 * Time: 14:47
 */
namespace Kdt\Iron\Nova;

use Kdt\Iron\Nova\Service\Initator;
use Kdt\Iron\Nova\Service\Registry;

class Nova {
    public function init($config)
    {
        Initator::newInstance()->init($config);
    }

    public function getAvailableService()
    {
         return Registry::getInstance()->getAll();
    }
}