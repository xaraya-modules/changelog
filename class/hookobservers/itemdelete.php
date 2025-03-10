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
use ixarHookObserver;
use ixarEventSubject;
use ixarHookSubject;
use BadParameterException;
use xarMod;
use xarDB;
use xarUser;
use xarServer;
use sys;

sys::import('xaraya.structures.hooks.observer');

/**
 * delete entry for a module item - hook for ('item','delete','API')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 */
class ItemDeleteObserver extends HookObserver implements ixarHookObserver
{
    public $module = 'changelog';

    /**
     * @param ixarHookSubject $subject
     */
    public function notify(ixarEventSubject $subject)
    {
        $this->setContext($subject->getContext());
        // get extrainfo from subject (array containing module, module_id, itemtype, itemid)
        $extrainfo = $subject->getExtrainfo();

        // everything is already validated in HookSubject, except possible empty objectid/itemid for create/display
        $modname = $extrainfo['module'];
        $itemtype = $extrainfo['itemtype'];
        $itemid = $extrainfo['itemid'];
        $modid = $extrainfo['module_id'];
        if (empty($itemid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['item id', 'admin', 'deletehook', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }

        xarMod::loadDbInfo('changelog', 'changelog');
        $dbconn = $this->db()->getConn();
        $xartable = $this->db()->getTables();
        $changelogtable = $xartable['changelog'];

        $editor = $this->user()->getId();
        $forwarded = $this->ctl()->getServerVar('HTTP_X_FORWARDED_FOR');
        if (!empty($forwarded)) {
            $hostname = preg_replace('/,.*/', '', $forwarded);
        } else {
            $hostname = $this->ctl()->getServerVar('REMOTE_ADDR');
        }
        $date = time();
        $status = 'deleted';
        /*
            if (isset($extrainfo['changelog_remark']) && is_string($extrainfo['changelog_remark'])) {
                $remark = $extrainfo['changelog_remark'];
            } else {
                $this->var()->find('changelog_remark', $remark, 'str:1:');
                if (empty($remark)){
                    $remark = '';
                }
            }
        */
        $remark = '';
        // probably not relevant here...
        //    $content = serialize($extrainfo);
        $content = '';

        // Get a new changelog ID
        $nextId = $dbconn->GenId($changelogtable);
        // Create new changelog
        $query = "INSERT INTO $changelogtable(xar_logid,
                                        xar_moduleid,
                                        xar_itemtype,
                                        xar_itemid,
                                        xar_editor,
                                        xar_hostname,
                                        xar_date,
                                        xar_status,
                                        xar_remark,
                                        xar_content)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $bindvars = [(int) $nextId,
            (int) $modid,
            (int) $itemtype,
            (int) $itemid,
            (int) $editor,
            (string) $hostname,
            (int) $date,
            (string) $status,
            (string) $remark,
            (string) $content];

        $result = $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return $extrainfo;
        }

        $logid = $dbconn->PO_Insert_ID($changelogtable, 'xar_logid');

        // Return the extra info with the id of the newly created item
        // (not that this will be of any used when called via hooks, but
        // who knows where else this might be used)
        $extrainfo['changelogid'] = $logid;

        // Return the extra info
        return $extrainfo;
    }
}
