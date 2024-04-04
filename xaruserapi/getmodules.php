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
 * get the list of modules where we're tracking item changes
 *
 * @param $args['editor'] optional editor of the changelog entries
 * @return array|null $array[$modid][$itemtype] = array('items' => $numitems,'changes' => $numchanges);
 */
function changelog_userapi_getmodules($args)
{
    extract($args);

    // Security Check
    if (!xarSecurity::check('ReadChangeLog')) {
        return;
    }

    // Database information
    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();
    $changelogtable = $xartable['changelog'];

    // Get items
    if (!empty($editor) && is_numeric($editor)) {
        $query = "SELECT xar_moduleid, xar_itemtype, COUNT(DISTINCT xar_itemid), COUNT(*)
                FROM $changelogtable
                WHERE xar_editor = ?
                GROUP BY xar_moduleid, xar_itemtype";
        $result = $dbconn->Execute($query, [(int)$editor]);
    } else {
        $query = "SELECT xar_moduleid, xar_itemtype, COUNT(DISTINCT xar_itemid), COUNT(*)
                FROM $changelogtable
                GROUP BY xar_moduleid, xar_itemtype";
        $result = $dbconn->Execute($query);
    }

    if (!$result) {
        return;
    }

    $modlist = [];
    while (!$result->EOF) {
        [$modid, $itemtype, $numitems, $numchanges] = $result->fields;
        $modlist[$modid][$itemtype] = ['items' => $numitems, 'changes' => $numchanges];
        $result->MoveNext();
    }
    $result->close();

    return $modlist;
}
