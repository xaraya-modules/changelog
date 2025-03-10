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
use xarMod;
use xarModVars;
use xarHooks;
use xarDB;
use xarUser;
use xarServer;
use xarVar;
use sys;

sys::import('xaraya.structures.hooks.observer');

/**
 * create an entry for a module item - hook for ('item','create','GUI')
 * Optional $extrainfo['changelog_remark'] from arguments, or 'changelog_remark' from input
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return array extrainfo array
 */
class ItemCreateObserver extends HookObserver implements ixarHookObserver
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
        $status = 'created';
        if (isset($extrainfo['changelog_remark']) && is_string($extrainfo['changelog_remark'])) {
            $remark = $extrainfo['changelog_remark'];
        } else {
            $this->var()->find('changelog_remark', $remark, 'str:1:');
            if (empty($remark)) {
                $remark = '';
            }
        }

        if (!empty($itemtype)) {
            $getlist = $this->mod()->getVar($modname . '.' . $itemtype);
        }
        if (!isset($getlist)) {
            $getlist = $this->mod()->getVar($modname);
        }
        if (!empty($getlist)) {
            $fieldlist = explode(',', $getlist);
        }
        $fields = [];
        foreach ($extrainfo as $field => $value) {
            // skip some common uninteresting fields
            if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                continue;
            }
            // skip fields we don't want here
            if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                continue;
            }
            $fields[$field] = $value;
        }
        // Check if we need to include any DD fields
        $withdd = $this->mod()->getVar('withdd');
        if (empty($withdd)) {
            $withdd = '';
        }
        $withdd = explode(';', $withdd);
        if (xarHooks::isAttached('dynamicdata', $modname, $itemtype) && !empty($withdd) &&
            (in_array($modname, $withdd) || in_array("$modname.$itemtype", $withdd))) {
            // Note: we need to make sure the DD hook is called before the changelog hook here
            $ddfields = $this->mod()->apiMethod(
                'dynamicdata',
                'userapi',
                'getitem',
                ['module_id' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid]
            );
            if (!empty($ddfields)) {
                foreach ($ddfields as $field => $value) {
                    // skip fields we don't want here
                    if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                        continue;
                    }
                    $fields[$field] = $value;
                }
            }
        }
        $content = serialize($fields);
        $fields = [];

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

        $bindvars = [$nextId,
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

        return $extrainfo;
    }
}
