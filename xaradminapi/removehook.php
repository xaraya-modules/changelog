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
 * delete all entries for a module - hook for ('module','remove','API')
 *
 * @param $args['objectid'] ID of the object (must be the module name here !!)
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function changelog_adminapi_removehook($args)
{
    extract($args);

    if (!isset($extrainfo)) {
        $extrainfo = [];
    }

    // When called via hooks, we should get the real module name from objectid
    // here, because the current module is probably going to be 'modules' !!!
    if (!isset($objectid) || !is_string($objectid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['object id (= module name)', 'admin', 'removehook', 'changelog'];
        throw new BadParameterException($vars, $msg);
    }

    $modid = xarMod::getRegId($objectid);
    if (empty($modid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['module id', 'admin', 'removehook', 'changelog'];
        throw new BadParameterException($vars, $msg);
    }

    // Get database setup
    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();

    $changelog = $xartable['changelog'];

    // Delete the entries
    $query = "DELETE
                 FROM $changelog
                WHERE xar_moduleid = ?";

    $bindvars = [(int) $modid];

    $result = $dbconn->Execute($query, $bindvars);
    if (!$result) {
        // we *must* return $extrainfo for now, or the next hook will fail
        //return false;
        return $extrainfo;
    }

    // Return the extra info
    return $extrainfo;
}
