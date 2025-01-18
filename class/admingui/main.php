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


use Xaraya\Modules\ChangeLog\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog admin main function
 * @extends MethodClass<AdminGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the main administration function
     * Redirect to modifyconfig
     * @author mikespub
     * @access public
     * @return bool|void true on success of redirect or void on failure
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminChangeLog')) {
            return;
        }

        $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        // success
        return true;
    }
}
