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
        if ($this->checkAccess('AdminChangeLog')) {
            $menulinks[] = ['url'   => $this->getUrl('admin', 'view'),
                'title' => $this->translate('View changelog entries per module'),
                'label' => $this->translate('View Changes')];
            $menulinks[] = ['url'   => $this->getUrl('admin', 'hooks'),
                'title' => $this->translate('Configure changelog hooks for other modules'),
                'label' => $this->translate('Enable Hooks')];
            $menulinks[] = ['url'   => $this->getUrl('admin', 'modifyconfig'),
                'title' => $this->translate('Modify the changelog configuration'),
                'label' => $this->translate('Modify Config')];
        }

        return $menulinks;
    }
}
