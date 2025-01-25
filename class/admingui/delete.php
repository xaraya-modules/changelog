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
use Xaraya\Modules\ChangeLog\AdminApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarMod;
use xarSec;
use xarController;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * changelog admin delete function
 * @extends MethodClass<AdminGui>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Delete changelog entries of module items
     * @see AdminGui::delete()
     */
    public function __invoke(array $args = [])
    {
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminChangeLog')) {
            return;
        }

        if (!$this->var()->check('modid', $modid)) {
            return;
        }
        if (!$this->var()->check('itemtype', $itemtype)) {
            return;
        }
        if (!$this->var()->check('itemid', $itemid)) {
            return;
        }
        if (!$this->var()->find('confirm', $confirm, 'str:1:', '')) {
            return;
        }
        if (!$this->var()->check('editor', $editor)) {
            return;
        }

        // Check for confirmation.
        if (empty($confirm)) {
            $data = [];
            $data['modid'] = $modid;
            $data['itemtype'] = $itemtype;
            $data['itemid'] = $itemid;
            $data['editor'] = $editor;

            $what = '';
            if (!empty($modid)) {
                $modinfo = xarMod::getInfo($modid);
                if (empty($itemtype)) {
                    $data['modname'] = ucwords($modinfo['displayname']);
                } else {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
                    } catch (Exception $e) {
                        $mytypes = [];
                    }
                    if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                        $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    } else {
                        $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    }
                }
            }
            $data['confirmbutton'] = $this->ml('Confirm');
            // Generate a one-time authorisation code for this operation
            $data['authid'] = $this->sec()->genAuthKey();
            // Return the template variables defined in this function
            return $data;
        }

        if (!$this->sec()->confirmAuthKey()) {
            return;
        }

        // comment out the following line if you want this
        return $this->ml('This feature is currently disabled for security reasons');

        /**
        if (!$adminapi->delete(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'editor' => $editor,
                'confirm' => $confirm]
        )) {
            return;
        }
        $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        return true;
         */
    }
}
