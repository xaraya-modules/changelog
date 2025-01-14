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


use Xaraya\Modules\ChangeLog\AdminApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog adminapi delete function
 * @extends MethodClass<AdminApi>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete changelog entries
     * @param array<mixed> $args
     * @var mixed $modid int module id, or
     * @var mixed $modname name of the calling module
     * @var mixed $itemtype optional item type for the item
     * @var mixed $itemid int item id
     * @var mixed $editor optional editor of the changelog entries
     * @return bool|void true on success, false on failure
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!$this->checkAccess('AdminChangeLog')) {
            return;
        }

        // Database information
        $dbconn = xarDB::getConn();
        $xartable = xarDB::getTables();
        $changelogtable = $xartable['changelog'];

        $query = "DELETE FROM $changelogtable ";
        $bindvars = [];
        if (!empty($modid)) {
            if (!is_numeric($modid)) {
                $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
                $vars = ['module id', 'admin', 'delete', 'changelog'];
                throw new BadParameterException($vars, $msg);
            }
            if (empty($itemtype) || !is_numeric($itemtype)) {
                $itemtype = 0;
            }
            $query .= " WHERE xar_moduleid = ?
                          AND xar_itemtype = ?";

            $bindvars[] = (int) $modid;
            $bindvars[] = (int) $itemtype;

            if (!empty($itemid) && is_numeric($itemid)) {
                $query .= " AND xar_itemid = ?";
                $bindvars[] = (int) $itemid;
            }

            if (!empty($editor) && is_numeric($editor)) {
                $query .= " AND xar_editor = ?";
                $bindvars[] = (int) $editor;
            }
        } elseif (!empty($editor) && is_numeric($editor)) {
            $query .= " WHERE xar_editor = ?";
            $bindvars[] = (int) $editor;
        }

        $result = $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return;
        }

        return true;
    }
}
