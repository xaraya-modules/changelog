<?php
/**
 * Change Log Module version information
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage changelog
 * @link http://xaraya.com/index.php/release/185.html
 * @author mikespub
 */
/**
 * Update the configuration parameters of the module based on data from the modification form
 *
 * @author mikespub
 * @access public
 * @return array|true|void on success or void on failure
 */
function changelog_admin_modifyconfig(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('AdminChangeLog')) {
        return;
    }

    $data = [];
    $data['settings'] = [];

    $changelog = xarModVars::get('changelog', 'default');
    $data['settings']['default'] = ['label' => xarML('Default configuration'),
                                         'changelog' => $changelog,
                                         'includedd' => 0];
    $withdd = xarModVars::get('changelog', 'withdd');
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
                    $changelog = xarModVars::get('changelog', "$modname.$itemtype");
                    if (empty($changelog)) {
                        $changelog = '';
                    }
                    if (isset($mytypes[$itemtype])) {
                        $type = $mytypes[$itemtype]['label'];
                        $link = $mytypes[$itemtype]['url'];
                    } else {
                        $type = xarML('type #(1)', $itemtype);
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
                    $data['settings']["$modname.$itemtype"] = ['label' => xarML('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                                                                    'changelog' => $changelog,
                                                                    'includedd' => $includedd];
                }
            } else {
                $changelog = xarModVars::get('changelog', $modname);
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
                $data['settings'][$modname] = ['label' => xarML('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                                                    'changelog' => $changelog,
                                                    'includedd' => $includedd];
            }
        }
    }

    $data['numstats'] = xarModVars::get('changelog', 'numstats');
    if (empty($data['numstats'])) {
        $data['numstats'] = 100;
    }
    $data['showtitle'] = xarModVars::get('changelog', 'showtitle');
    if (!empty($data['showtitle'])) {
        $data['showtitle'] = 1;
    }

    $data['authid'] = xarSec::genAuthKey();
    return $data;
}
