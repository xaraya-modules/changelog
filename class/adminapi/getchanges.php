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

use Xaraya\Modules\MethodClass;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog adminapi getchanges function
 */
class GetchangesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get entries for a module item
     * @param mixed $args ['modid'] module id
     * @param mixed $args ['itemtype'] item type
     * @param mixed $args ['itemid'] item id
     * @param mixed $args ['numitems'] number of entries to retrieve (optional)
     * @param mixed $args ['startnum'] starting number (optional)
     * @return array|void of changes
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($modid) || !is_numeric($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module id', 'admin', 'getchanges', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }
        if (!isset($itemtype) || !is_numeric($itemtype)) {
            $itemtype = 0;
        }
        if (!isset($itemid) || !is_numeric($itemid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['item id', 'admin', 'getchanges', 'changelog'];
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
                         $rolestable.name
                    FROM $changelogtable
               LEFT JOIN $rolestable
                      ON $changelogtable.xar_editor = $rolestable.id
                   WHERE $changelogtable.xar_moduleid = ?
                     AND $changelogtable.xar_itemtype = ?
                     AND $changelogtable.xar_itemid = ?
                ORDER BY $changelogtable.xar_logid DESC";

        $bindvars = [(int) $modid, (int) $itemtype, (int) $itemid];

        if (isset($numitems) && is_numeric($numitems)) {
            if (empty($startnum)) {
                $startnum = 1;
            }
            $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        } else {
            $result = $dbconn->Execute($query, $bindvars);
        }
        if (!$result) {
            return;
        }

        $changes = [];
        while (!$result->EOF) {
            $change = [];
            [$change['logid'],
                $change['editor'],
                $change['hostname'],
                $change['date'],
                $change['status'],
                $change['remark'],
                $change['editorname']] = $result->fields;
            $changes[$change['logid']] = $change;
            $result->MoveNext();
        }
        $result->Close();

        return $changes;
    }
}
