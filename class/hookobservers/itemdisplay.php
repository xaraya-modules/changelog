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

/**
 * display changelog entry for a module item - hook for ('item','display','GUI')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return bool true on success, false on failure
 */
class ItemDisplayObserver extends HookObserver implements ixarHookObserver
{
    public $module = 'changelog';
    public $type = 'user';

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

        $changes = $this->mod()->apiMethod(
            'changelog',
            'adminapi',
            'getchanges',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'numitems' => 1]
        );
        // return empty string here
        if (empty($changes) || !is_array($changes) || count($changes) == 0) {
            return '';
        }

        $data = array_pop($changes);

        if ($this->sec()->checkAccess('AdminChangeLog', 0)) {
            $data['showhost'] = 1;
        } else {
            $data['showhost'] = 0;
        }

        $data['profile'] = $this->ctl()->getModuleURL(
            'roles',
            'user',
            'display',
            ['id' => $data['editor']]
        );
        if (!$data['showhost']) {
            $data['hostname'] = '';
        }
        if (!empty($data['remark'])) {
            $data['remark'] = $this->prep()->text($data['remark']);
        }
        $data['link'] = $this->mod()->getURL(
            'admin',
            'showlog',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );

        // TODO: use custom template per module + itemtype ?
        return $this->render(
            'displayhook',
            $data
        );
    }
}
