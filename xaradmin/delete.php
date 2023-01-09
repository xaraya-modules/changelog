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
 * Delete changelog entries of module items
 */
function changelog_admin_delete()
{
    // Security Check
    if (!xarSecurityCheck('AdminChangeLog')) {
        return;
    }

    if (!xarVarFetch('modid', 'isset', $modid, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('itemtype', 'isset', $itemtype, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('itemid', 'isset', $itemid, null, XARVAR_DONT_SET)) {
        return;
    }
    if (!xarVarFetch('confirm', 'str:1:', $confirm, '', XARVAR_NOT_REQUIRED)) {
        return;
    }
    if (!xarVarFetch('editor', 'isset', $editor, null, XARVAR_DONT_SET)) {
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
            $modinfo = xarModGetInfo($modid);
            if (empty($itemtype)) {
                $data['modname'] = ucwords($modinfo['displayname']);
            } else {
                // Get the list of all item types for this module (if any)
                $mytypes = xarMod::apiFunc(
                    $modinfo['name'],
                    'user',
                    'getitemtypes',
                    // don't throw an exception if this function doesn't exist
                    [],
                    0
                );
                if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                } else {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                }
            }
        }
        $data['confirmbutton'] = xarML('Confirm');
        // Generate a one-time authorisation code for this operation
        $data['authid'] = xarSecGenAuthKey();
        // Return the template variables defined in this function
        return $data;
    }

    if (!xarSecConfirmAuthKey()) {
        return;
    }

    // comment out the following line if you want this
    return xarML('This feature is currently disabled for security reasons');

    if (!xarMod::apiFunc(
        'changelog',
        'admin',
        'delete',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid,
              'editor' => $editor,
              'confirm' => $confirm]
    )) {
        return;
    }
    xarResponse::Redirect(xarModURL('changelog', 'admin', 'view'));
    return true;
}
