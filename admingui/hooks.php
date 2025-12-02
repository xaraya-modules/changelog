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

/**
 * changelog admin hooks function
 * @extends MethodClass<AdminGui>
 */
class HooksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Hooks shows the configuration of hooks for other modules
     * @author the Changelog module development team
     * @return array|void $data containing template data
     * @since 4 March 2006
     * @see AdminGui::hooks()
     */
    public function __invoke(array $args = [])
    {
        /* Security Check */
        if (!$this->sec()->checkAccess('AdminChangelog', 0)) {
            return;
        }

        $data = [];

        return $data;
    }
}
