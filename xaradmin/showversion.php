<?php
/**
 * Change Log Module version information/restore
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
 * show a particular version of a module item (or restore it if possible)
 */
function changelog_admin_showversion(array $args = [], $context = null)
{
    extract($args);

    // TODO: add more restore options
    // List of currently supported restore modules (see API calls below)
    $supported = ['articles', 'dynamicdata', 'xarpages'];

    if (!xarVar::fetch('modid', 'isset', $modid, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('itemtype', 'isset', $itemtype, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('itemid', 'isset', $itemid, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('logid', 'isset', $logid, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('restore', 'isset', $restore, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('confirm', 'isset', $confirm, null, xarVar::NOT_REQUIRED)) {
        return;
    }

    if (!xarSecurity::check('AdminChangeLog', 1, 'Item', "$modid:$itemtype:$itemid")) {
        return;
    }

    $data = xarMod::apiFunc(
        'changelog',
        'admin',
        'getversion',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid,
              'logid' => $logid]
    );
    if (empty($data) || !is_array($data)) {
        return;
    }

    if (xarSecurity::check('AdminChangeLog', 0)) {
        $data['showhost'] = 1;
    } else {
        $data['showhost'] = 0;
    }

    $data['profile'] = xarController::URL(
        'roles',
        'user',
        'display',
        ['id' => $data['editor']]
    );
    if (!$data['showhost']) {
        $data['hostname'] = '';
    }
    if (!empty($data['remark'])) {
        $data['remark'] = xarVar::prepForDisplay($data['remark']);
    }
    // 2template $data['date'] = xarLocale::formatDate($data['date']);

    $data['link'] = xarController::URL(
        'changelog',
        'admin',
        'showlog',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid]
    );

    $data['fields'] = [];

    $modinfo = xarMod::getInfo($modid);
    if (empty($modinfo['name'])) {
        return $data;
    }
    $data['modid'] = $modid;
    $data['itemtype'] = $itemtype;
    $data['itemid'] = $itemid;
    $data['modname'] = $modinfo['name'];

    if (empty($restore)) {
        $restore = null;
    } else {
        $restore = 1;
    }

    // Check for supported restore modules
    if (!empty($restore) && !in_array($modinfo['name'], $supported)) {
        $msg = 'Restoring items from module #(1) is currently not supported';
        $vars = [$modinfo['name']];
        throw new BadParameterException($vars, $msg);
    }

    // Check for confirmation
    if (!empty($confirm) && !xarSec::confirmAuthKey()) {
        return;
    }

    try {
        $itemlinks = xarMod::apiFunc(
            $modinfo['name'],
            'user',
            'getitemlinks',
            ['itemtype' => $itemtype,
            'itemids' => [$itemid]]
        );
    } catch (Exception $e) {
        $itemlinks = [];
    }
    if (isset($itemlinks[$itemid])) {
        $data['itemlink'] = $itemlinks[$itemid]['url'];
        $data['itemtitle'] = $itemlinks[$itemid]['title'];
        $data['itemlabel'] = $itemlinks[$itemid]['label'];
    }

    if (!empty($data['content'])) {
        $fields = unserialize($data['content']);
        $data['content'] = '';

        if (!empty($itemtype)) {
            $getlist = xarModVars::get('changelog', $modinfo['name'] . '.' . $itemtype);
        }
        if (!isset($getlist)) {
            $getlist = xarModVars::get('changelog', $modinfo['name']);
        }
        if (!empty($getlist)) {
            $fieldlist = explode(',', $getlist);
        }
        ksort($fields);
        foreach ($fields as $field => $value) {
            // skip some common uninteresting fields
            if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                continue;
            }
            // skip fields we don't want here
            if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                continue;
            }
            // Note: we'll do the formatting in the template now
            $data['fields'][$field] = $value;
        }
    }

    // Restore this version of the module item
    if (!empty($confirm)) {
        if (empty($data['fields'])) {
            $msg = 'Nothing to restore';
            $vars = [];
            throw new BadParameterException($vars, $msg);
        }
        switch ($modinfo['name']) {
            case 'articles':
                // Check mandatory fields (if necessary)
                if (empty($data['fields']['aid'])) {
                    $data['fields']['aid'] = $itemid;
                }
                /*
                                // Prepare optional fields (if necessary)
                                if (!isset($data['fields']['module'])) {
                                    $data['fields']['module'] = $modinfo['name'];
                                }
                                if (!isset($data['fields']['itemtype'])) {
                                    $data['fields']['itemtype'] = $itemtype;
                                }
                */
                // Call the update API function
                $result = xarMod::apiFunc(
                    'articles',
                    'admin',
                    'update',
                    $data['fields']
                );
                if (empty($result)) {
                    return;
                }
                break;

            case 'dynamicdata':
                // Call the update API function
                $result = xarMod::apiFunc(
                    'dynamicdata',
                    'admin',
                    'update',
                    ['module_id' => $modid,
                          'itemtype' => $itemtype,
                          'itemid' => $itemid,
                          'values' => $data['fields']]
                );
                if (empty($result)) {
                    return;
                }
                break;

            case 'xarpages':
                // Check mandatory fields (if necessary)
                if (empty($data['fields']['pid'])) {
                    $data['fields']['pid'] = $itemid;
                }
                // Call the update API function
                $result = xarMod::apiFunc(
                    'xarpages',
                    'admin',
                    'updatepage',
                    $data['fields']
                );
                if (empty($result)) {
                    return;
                }
                break;

                // TODO: add more restore options
            default:
                $msg = 'Restoring items from module #(1) is currently not supported';
                $vars = [$modinfo['name']];
                throw new BadParameterException($vars, $msg);
        }
        xarController::redirect(xarController::URL(
            'changelog',
            'admin',
            'showlog',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid]
        ), null, $context);
        return true;
    }

    // get all changes
    $changes = xarMod::apiFunc(
        'changelog',
        'admin',
        'getchanges',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid]
    );
    $numchanges = count($changes);
    $data['numversions'] = $numchanges;
    $nextid = 0;
    $previd = 0;
    $lastid = 0;
    $version = [];
    foreach (array_keys($changes) as $id) {
        $version[$id] = $numchanges;
        $numchanges--;
        if ($id == $logid) {
            $nextid = $lastid;
        } elseif ($lastid == $logid) {
            $previd = $id;
        }
        $lastid = $id;
    }

    $data['version'] = $version[$logid];
    if (!empty($nextid)) {
        $data['nextversion'] = xarController::URL(
            'changelog',
            'admin',
            'showversion',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logid' => $nextid,
                  'restore' => $restore]
        );
        $data['nextdiff'] = xarController::URL(
            'changelog',
            'admin',
            'showdiff',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logids' => $logid . '-' . $nextid]
        );
    }
    if (!empty($previd)) {
        $data['prevversion'] = xarController::URL(
            'changelog',
            'admin',
            'showversion',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logid' => $previd,
                  'restore' => $restore]
        );
        $data['prevdiff'] = xarController::URL(
            'changelog',
            'admin',
            'showdiff',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logids' => $previd . '-' . $logid]
        );
    }

    if (!empty($restore)) {
        $data['showlink'] = xarController::URL(
            'changelog',
            'admin',
            'showversion',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logid' => $logid]
        );
        $data['confirmbutton'] = xarML('Confirm');
        // Generate a one-time authorisation code for this operation
        $data['authid'] = xarSec::genAuthKey();
        $data['restore'] = 1;
    } elseif (in_array($modinfo['name'], $supported)) {
        $data['restorelink'] = xarController::URL(
            'changelog',
            'admin',
            'showversion',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logid' => $logid,
                  'restore' => 1]
        );
    }

    return $data;
}
