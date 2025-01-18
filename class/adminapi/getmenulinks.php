<?php

/**
 * @package modules\changelog
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\ChangeLog\AdminApi;


use Xaraya\Modules\ChangeLog\AdminApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog adminapi getmenulinks function
 * @extends MethodClass<AdminApi>
 */
class GetmenulinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function pass individual menu items to the main menu
     * @author mikespub
     * @return array containing the menulinks for the main menu items.
     */
    public function __invoke(array $args = [])
    {
        $menulinks = [];
        // Security Check
        if ($this->sec()->checkAccess('AdminChangeLog')) {
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'view'),
                'title' => $this->ml('View changelog entries per module'),
                'label' => $this->ml('View Changes')];
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'hooks'),
                'title' => $this->ml('Configure changelog hooks for other modules'),
                'label' => $this->ml('Enable Hooks')];
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'modifyconfig'),
                'title' => $this->ml('Modify the changelog configuration'),
                'label' => $this->ml('Modify Config')];
        }

        return $menulinks;
    }
}
