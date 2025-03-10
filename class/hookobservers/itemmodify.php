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
use xarVar;
use xarSecurity;
use xarController;
use xarTpl;
use sys;

sys::import('xaraya.structures.hooks.observer');

/**
 * modify an entry for a module item - hook for ('item','modify','GUI')
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @return string hook output in HTML
 */
class ItemModifyObserver extends HookObserver implements ixarHookObserver
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
            $vars = ['item id', 'admin', 'modifyhook', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }

        if (!empty($extrainfo['changelog_remark'])) {
            $remark = $extrainfo['changelog_remark'];
        } else {
            $this->var()->find('changelog_remark', $remark, 'str:1:');
            if (empty($remark)) {
                $remark = '';
            }
        }

        if ($this->sec()->check('ReadChangeLog', 0, 'Item', "$modid:$itemtype:$itemid")) {
            $link = $this->mod()->getURL(
                'admin',
                'showlog',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid]
            );
        } else {
            $link = '';
        }

        return $this->mod()->template(
            'modifyhook',
            ['remark' => $remark,
                'context' => $this->getContext(),
                'link' => $link]
        );
    }
}
