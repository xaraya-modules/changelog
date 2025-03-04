<?php

/**
 * @package modules\changelog
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\ChangeLog\UserApi;


use Xaraya\Modules\ChangeLog\UserApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog userapi getitems function
 * @extends MethodClass<UserApi>
 */
class GetitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the number of changes for a list of items
     * @param array<mixed> $args
     *     $args['modname'] name of the module you want items from, or
     *     $args['modid'] module id you want items from
     *     $args['itemtype'] item type of the items (only 1 type supported per call)
     *     $args['itemids'] array of item IDs
     *     $args['editor'] optional editor of the changelog entries
     *     $args['sort'] string sort by itemid (default) or numhits
     *     $args['numitems'] number of items to return
     *     $args['startnum'] start at this number (1-based)
     * @return array|null $array[$itemid] = $changes;
     * @see UserApi::getitems()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($modname) && !isset($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module', 'user', 'getitems', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }
        if (!empty($modname)) {
            $modid = $this->mod()->getRegID($modname);
        }
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module', 'user', 'getitems', 'changelog'];
            throw new BadParameterException($vars, $msg);
        } elseif (empty($modname)) {
            $modinfo = $this->mod()->getInfo($modid);
            $modname = $modinfo['name'];
        }
        if (empty($itemtype)) {
            $itemtype = 0;
        }
        if (empty($sort)) {
            $sort = 'itemid';
        }
        if (empty($startnum)) {
            $startnum = 1;
        }

        if (!isset($itemids)) {
            $itemids = [];
        }

        // Security Check
        if (!$this->sec()->check('ReadChangeLog', 1, "$modid:$itemtype:All")) {
            return;
        }

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = $this->db()->getTables();
        $changelogtable = $xartable['changelog'];

        // Get items
        $query = "SELECT xar_itemid,
                         COUNT(*) as numchanges
                    FROM $changelogtable
                   WHERE xar_moduleid = ?
                     AND xar_itemtype = ?";
        $bindvars = [(int) $modid, (int) $itemtype];

        if (!empty($editor) && is_numeric($editor)) {
            $query .= " AND xar_editor = ? ";
            $bindvars[] = (int) $editor;
        }
        if (count($itemids) > 0) {
            $allids = join(', ', $itemids);
            $query .= " AND xar_itemid IN ( ? ) ";
            $bindvars[] = (string) $allids;
        }
        $query .= " GROUP BY xar_itemid";
        if ($sort == 'numchanges') {
            $query .= " ORDER BY numchanges DESC, xar_itemid ASC";
        } else {
            $query .= " ORDER BY xar_itemid ASC";
        }

        if (!empty($numitems) && !empty($startnum)) {
            $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        } else {
            $result = $dbconn->Execute($query, $bindvars);
        }
        if (!$result) {
            return;
        }

        $hitlist = [];
        while ($result->next()) {
            [$id, $changes] = $result->fields;
            $hitlist[$id] = $changes;
        }
        $result->close();

        return $hitlist;
    }
}
