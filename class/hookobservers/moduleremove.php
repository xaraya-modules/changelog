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

namespace Xaraya\Modules\ChangeLog\HookObservers;

use HookObserver;
use ixarEventObserver;
use ixarEventSubject;
use BadParameterException;
use xarMod;
use xarDB;
use sys;

sys::import('xaraya.structures.hooks.observer');

/**
 * delete all entries for a module - hook for ('module','remove','API')
 *
 * @param $args['objectid'] ID of the object (must be the module name here !!)
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
class ModuleRemoveObserver extends HookObserver implements ixarEventObserver
{
    public $module = 'changelog';

    public function notify(ixarEventSubject $subject)
    {
        // for module remove, we need the module name, we get that from the objectid
        // get args from subject (array containing objectid, extrainfo)
        $args = $subject->getArgs();
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
        xarMod::loadDbInfo('changelog', 'changelog');
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
            return $extrainfo;
        }

        // Return the extra info
        return $extrainfo;
    }
}
