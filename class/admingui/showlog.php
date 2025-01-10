<?php

/**
 * @package modules\changelog
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\ChangeLog\AdminGui;

use Xaraya\Modules\MethodClass;
use xarVar;
use xarSecurity;
use xarMod;
use xarController;
use xarLocale;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * changelog admin showlog function
 */
class ShowlogMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the change log for a module item
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!xarVar::fetch('modid', 'isset', $modid, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('itemtype', 'isset', $itemtype, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('itemid', 'isset', $itemid, null, xarVar::NOT_REQUIRED)) {
            return;
        }

        if (!xarSecurity::check('ReadChangeLog', 1, 'Item', "$modid:$itemtype:$itemid")) {
            return;
        }

        $data = [];
        $data['changes'] = xarMod::apiFunc(
            'changelog',
            'admin',
            'getchanges',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );
        if (empty($data['changes']) || !is_array($data['changes'])) {
            return;
        }

        if (xarSecurity::check('AdminChangeLog', 0)) {
            $data['showhost'] = 1;
        } else {
            $data['showhost'] = 0;
        }
        $numchanges = count($data['changes']);
        $data['numversions'] = $numchanges;
        foreach (array_keys($data['changes']) as $logid) {
            $data['changes'][$logid]['profile'] = xarController::URL(
                'roles',
                'user',
                'display',
                ['id' => $data['changes'][$logid]['editor']]
            );
            if (!$data['showhost']) {
                $data['changes'][$logid]['hostname'] = '';
                $data['changes'][$logid]['link'] = '';
            } else {
                $data['changes'][$logid]['link'] = xarController::URL(
                    'changelog',
                    'admin',
                    'showversion',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                        'logid' => $logid]
                );
            }
            if (!empty($data['changes'][$logid]['remark'])) {
                $data['changes'][$logid]['remark'] = xarVar::prepForDisplay($data['changes'][$logid]['remark']);
            }
            // 2template $data['changes'][$logid]['date'] = xarLocale::formatDate($data['changes'][$logid]['date']);
            // descending order of changes here
            $data['changes'][$logid]['version'] = $numchanges;
            $numchanges--;
        }
        $data['modid'] = $modid;
        $data['itemtype'] = $itemtype;
        $data['itemid'] = $itemid;

        $logidlist = array_keys($data['changes']);

        if (count($logidlist) > 0) {
            $firstid = $logidlist[count($logidlist) - 1];
            $data['prevversion'] = xarController::URL(
                'changelog',
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $firstid]
            );
            if (count($logidlist) > 1) {
                $previd = $logidlist[count($logidlist) - 2];
                $data['prevdiff'] = xarController::URL(
                    'changelog',
                    'admin',
                    'showdiff',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                        'logids' => $firstid . '-' . $previd]
                );
            }
        }
        if (count($logidlist) > 1) {
            $lastid = $logidlist[0];
            $data['nextversion'] = xarController::URL(
                'changelog',
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $lastid]
            );
            if (count($logidlist) > 2) {
                $nextid = $logidlist[1];
                $data['nextdiff'] = xarController::URL(
                    'changelog',
                    'admin',
                    'showdiff',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                        'logids' => $nextid . '-' . $lastid]
                );
            }
        }

        $modinfo = xarMod::getInfo($modid);
        if (empty($modinfo['name'])) {
            return $data;
        }
        try {
            $itemlinks = xarMod::apiFunc(
                $modinfo['name'],
                'user',
                'getitemlinks',
                ['itemtype' => $itemtype,
                    'itemids' => [$itemid]]
            );
        } catch (Exception $e) {
            $itemlinks = [];
        }
        if (isset($itemlinks[$itemid])) {
            $data['itemlink'] = $itemlinks[$itemid]['url'];
            $data['itemtitle'] = $itemlinks[$itemid]['title'];
            $data['itemlabel'] = $itemlinks[$itemid]['label'];
        }

        return $data;
    }
}
