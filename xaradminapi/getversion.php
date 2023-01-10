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
 * get a particular entry for a module item
 *
 * @param $args['modid'] module id
 * @param $args['itemtype'] item type
 * @param $args['itemid'] item id
 * @param $args['logid'] log id
 * @return array of changes
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function changelog_adminapi_getversion($args)
{
    extract($args);

    if (!isset($modid) || !is_numeric($modid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = array('module id', 'admin', 'getversion', 'changelog');
        throw new BadParameterException($vars, $msg);
    }
    if (!isset($itemtype) || !is_numeric($itemtype)) {
        $itemtype = 0;
    }
    if (!isset($itemid) || !is_numeric($itemid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = array('item id', 'admin', 'getversion', 'changelog');
        throw new BadParameterException($vars, $msg);
    }
    if (!isset($logid) || !is_numeric($logid)) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = array('log id', 'admin', 'getversion', 'changelog');
        throw new BadParameterException($vars, $msg);
    }

    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();
    $changelogtable = $xartable['changelog'];
    $rolestable = $xartable['roles'];

    // Get changes for this module item
    $query = "SELECT $changelogtable.xar_logid,
                     $changelogtable.xar_editor,
                     $changelogtable.xar_hostname,
                     $changelogtable.xar_date,
                     $changelogtable.xar_status,
                     $changelogtable.xar_remark,
                     $changelogtable.xar_content,
                     $rolestable.name
                FROM $changelogtable
           LEFT JOIN $rolestable
                  ON $changelogtable.xar_editor = $rolestable.id
               WHERE $changelogtable.xar_moduleid = ?
                 AND $changelogtable.xar_itemtype = ?
                 AND $changelogtable.xar_itemid = ?
                 AND $changelogtable.xar_logid = ?";

    $bindvars = [(int) $modid, (int) $itemtype, (int) $itemid, (int) $logid];

    $result = $dbconn->Execute($query, $bindvars);
    if (!$result) {
        return;
    }

    $version = [];
    if ($result->EOF) {
        return $version;
    }
    [$version['logid'],
        $version['editor'],
        $version['hostname'],
        $version['date'],
        $version['status'],
        $version['remark'],
        $version['content'],
        $version['editorname']] = $result->fields;
    $result->Close();

    return $version;
}
