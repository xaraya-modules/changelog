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
 * changelog adminapi getversion function
 */
class GetversionMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get a particular entry for a module item
     * @param mixed $args ['modid'] module id
     * @param mixed $args ['itemtype'] item type
     * @param mixed $args ['itemid'] item id
     * @param mixed $args ['logid'] log id
     * @return array|void of changes
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($modid) || !is_numeric($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module id', 'admin', 'getversion', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }
        if (!isset($itemtype) || !is_numeric($itemtype)) {
            $itemtype = 0;
        }
        if (!isset($itemid) || !is_numeric($itemid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['item id', 'admin', 'getversion', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }
        if (!isset($logid) || !is_numeric($logid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['log id', 'admin', 'getversion', 'changelog'];
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
}