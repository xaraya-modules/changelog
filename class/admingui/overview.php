<?php

/**
 * @package modules\changelog
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\ChangeLog\AdminGui;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarTpl;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog admin overview function
 */
class OverviewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Overview function that displays the standard Overview page
     */
    public function __invoke(array $args = [])
    {
        /* Security Check */
        if (!xarSecurity::check('AdminChangeLog', 0)) {
            return;
        }

        $data = [];

        /* if there is a separate overview function return data to it
         * else just call the main function that displays the overview
         */

        $data['context'] ??= $this->getContext();
        return xarTpl::module('changelog', 'admin', 'main', $data, 'main');
    }
}
