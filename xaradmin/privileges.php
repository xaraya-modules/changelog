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
 * Manage definition of instances for privileges (unfinished)
 */
function changelog_admin_privileges($args)
{
    // Security Check
    if (!xarSecurityCheck('AdminChangeLog')) {
        return;
    }

    extract($args);

    // fixed params
    if (!xarVarFetch('moduleid', 'isset', $moduleid, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('itemtype', 'isset', $itemtype, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('itemid', 'isset', $itemid, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('apply', 'isset', $apply, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extpid', 'isset', $extpid, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extname', 'isset', $extname, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extrealm', 'isset', $extrealm, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extmodule', 'isset', $extmodule, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extcomponent', 'isset', $extcomponent, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extinstance', 'isset', $extinstance, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('extlevel', 'isset', $extlevel, null, XARVAR_DONT_SET)) {
        return;
    }

    if (!empty($extinstance)) {
        $parts = explode(':', $extinstance);
        if (count($parts) > 0 && !empty($parts[0])) {
            $moduleid = $parts[0];
        }
        if (count($parts) > 1 && !empty($parts[1])) {
            $itemtype = $parts[1];
        }
        if (count($parts) > 2 && !empty($parts[2])) {
            $itemid = $parts[2];
        }
    }

    // Get the list of all modules currently hooked to categories
    $hookedmodlist = xarMod::apiFunc(
        'modules',
        'admin',
        'gethookedmodules',
        ['hookModName' => 'changelog']
    );
    if (!isset($hookedmodlist)) {
        $hookedmodlist = [];
    }
    $modlist = [];
    foreach ($hookedmodlist as $modname => $val) {
        if (empty($modname)) {
            continue;
        }
        $modid = xarMod::getRegId($modname);
        if (empty($modid)) {
            continue;
        }
        $modinfo = xarModGetInfo($modid);
        $modlist[$modid] = $modinfo['displayname'];
    }

    if (empty($moduleid) || $moduleid == 'All' || !is_numeric($moduleid)) {
        $moduleid = 0;
    }
    if (empty($itemtype) || $itemtype == 'All' || !is_numeric($itemtype)) {
        $itemtype = 0;
    }
    if (empty($itemid) || $itemid == 'All' || !is_numeric($itemid)) {
        $itemid = 0;
    }

    // define the new instance
    $newinstance = [];
    $newinstance[] = empty($moduleid) ? 'All' : $moduleid;
    $newinstance[] = empty($itemtype) ? 'All' : $itemtype;
    $newinstance[] = empty($itemid) ? 'All' : $itemid;

    if (!empty($apply)) {
        // create/update the privilege
        $pid = xarReturnPrivilege($extpid, $extname, $extrealm, $extmodule, $extcomponent, $newinstance, $extlevel);
        if (empty($pid)) {
            return; // throw back
        }

        // redirect to the privilege
        xarResponse::Redirect(xarModURL(
            'privileges',
            'admin',
            'modifyprivilege',
            ['pid' => $pid]
        ));
        return true;
    }

    /*
        if (!empty($moduleid)) {
            $numitems = xarMod::apiFunc('categories','user','countitems',
                                      array('modid' => $moduleid,
                                            'cids'  => (empty($cid) ? null : array($cid))
                                           ));
        } else {
            $numitems = xarML('probably');
        }
    */
    $numitems = xarML('probably');

    $data = [
                  'moduleid'     => $moduleid,
                  'itemtype'     => $itemtype,
                  'itemid'       => $itemid,
                  'modlist'      => $modlist,
                  'numitems'     => $numitems,
                  'extpid'       => $extpid,
                  'extname'      => $extname,
                  'extrealm'     => $extrealm,
                  'extmodule'    => $extmodule,
                  'extcomponent' => $extcomponent,
                  'extlevel'     => $extlevel,
                  'extinstance'  => xarVarPrepForDisplay(join(':', $newinstance)),
                 ];

    $data['refreshlabel'] = xarML('Refresh');
    $data['applylabel'] = xarML('Finish and Apply to Privilege');

    return $data;
}
