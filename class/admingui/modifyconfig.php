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
use xarModVars;
use xarMod;
use xarController;
use xarModHooks;
use xarSec;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * changelog admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update the configuration parameters of the module based on data from the modification form
     * @author mikespub
     * @access public
     * @return array|true|void on success or void on failure
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('AdminChangeLog')) {
            return;
        }

        $data = [];
        $data['settings'] = [];

        $changelog = $this->getModVar('default');
        $data['settings']['default'] = ['label' => $this->translate('Default configuration'),
            'changelog' => $changelog,
            'includedd' => 0];
        $withdd = $this->getModVar('withdd');
        if (empty($withdd)) {
            $withdd = '';
        }
        $withdd = explode(';', $withdd);

        $hookedmodules = xarMod::apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'changelog']
        );
        if (isset($hookedmodules) && is_array($hookedmodules)) {
            foreach ($hookedmodules as $modname => $value) {
                // we have hooks for individual item types here
                if (!isset($value[0])) {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = xarMod::apiFunc($modname, 'user', 'getitemtypes');
                    } catch (Exception $e) {
                        $mytypes = [];
                    }
                    foreach ($value as $itemtype => $val) {
                        $changelog = $this->getModVar("$modname.$itemtype");
                        if (empty($changelog)) {
                            $changelog = '';
                        }
                        if (isset($mytypes[$itemtype])) {
                            $type = $mytypes[$itemtype]['label'];
                            $link = $mytypes[$itemtype]['url'];
                        } else {
                            $type = $this->translate('type #(1)', $itemtype);
                            $link = xarController::URL($modname, 'user', 'view', ['itemtype' => $itemtype]);
                        }
                        if (xarModHooks::isHooked('dynamicdata', $modname, $itemtype)) {
                            if (!empty($withdd) && in_array("$modname.$itemtype", $withdd)) {
                                $includedd = 2;
                            } else {
                                $includedd = 1;
                            }
                        } else {
                            $includedd = 0;
                        }
                        $data['settings']["$modname.$itemtype"] = ['label' => $this->translate('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                            'changelog' => $changelog,
                            'includedd' => $includedd];
                    }
                } else {
                    $changelog = $this->getModVar($modname);
                    if (empty($changelog)) {
                        $changelog = '';
                    }
                    if (xarModHooks::isHooked('dynamicdata', $modname)) {
                        if (!empty($withdd) && in_array($modname, $withdd)) {
                            $includedd = 2;
                        } else {
                            $includedd = 1;
                        }
                    } else {
                        $includedd = 0;
                    }
                    $link = xarController::URL($modname, 'user', 'main');
                    $data['settings'][$modname] = ['label' => $this->translate('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                        'changelog' => $changelog,
                        'includedd' => $includedd];
                }
            }
        }

        $data['numstats'] = $this->getModVar('numstats');
        if (empty($data['numstats'])) {
            $data['numstats'] = 100;
        }
        $data['showtitle'] = $this->getModVar('showtitle');
        if (!empty($data['showtitle'])) {
            $data['showtitle'] = 1;
        }

        $data['authid'] = $this->genAuthKey();
        return $data;
    }
}
