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
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * changelog userapi getmodules function
 * @extends MethodClass<UserApi>
 */
class GetmodulesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of modules where we're tracking item changes
     * @param array<mixed> $args
     * @var mixed $editor optional editor of the changelog entries
     * @return array|null $array[$modid][$itemtype] = array('items' => $numitems,'changes' => $numchanges);
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        // Security Check
        if (!$this->sec()->checkAccess('ReadChangeLog')) {
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
            $result = $dbconn->Execute($query, [(int) $editor]);
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
}
