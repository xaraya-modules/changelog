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


use Xaraya\Modules\ChangeLog\AdminGui;
use Xaraya\Modules\ChangeLog\AdminApi;
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
 * @extends MethodClass<AdminGui>
 */
class ShowlogMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the change log for a module item
     * @see AdminGui::showlog()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        $this->var()->find('modid', $modid);
        $this->var()->find('itemtype', $itemtype);
        $this->var()->find('itemid', $itemid);

        if (!$this->sec()->check('ReadChangeLog', 1, 'Item', "$modid:$itemtype:$itemid")) {
            return;
        }

        $data = [];
        $data['changes'] = $adminapi->getchanges(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );
        if (empty($data['changes']) || !is_array($data['changes'])) {
            return;
        }

        if ($this->sec()->checkAccess('AdminChangeLog', 0)) {
            $data['showhost'] = 1;
        } else {
            $data['showhost'] = 0;
        }
        $numchanges = count($data['changes']);
        $data['numversions'] = $numchanges;
        foreach (array_keys($data['changes']) as $logid) {
            $data['changes'][$logid]['profile'] = $this->ctl()->getModuleURL(
                'roles',
                'user',
                'display',
                ['id' => $data['changes'][$logid]['editor']]
            );
            if (!$data['showhost']) {
                $data['changes'][$logid]['hostname'] = '';
                $data['changes'][$logid]['link'] = '';
            } else {
                $data['changes'][$logid]['link'] = $this->mod()->getURL(
                    'admin',
                    'showversion',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                        'logid' => $logid]
                );
            }
            if (!empty($data['changes'][$logid]['remark'])) {
                $data['changes'][$logid]['remark'] = $this->var()->prep($data['changes'][$logid]['remark']);
            }
            // 2template $data['changes'][$logid]['date'] = $this->mls()->formatDate($data['changes'][$logid]['date']);
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
            $data['prevversion'] = $this->mod()->getURL(
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $firstid]
            );
            if (count($logidlist) > 1) {
                $previd = $logidlist[count($logidlist) - 2];
                $data['prevdiff'] = $this->mod()->getURL(
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
            $data['nextversion'] = $this->mod()->getURL(
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $lastid]
            );
            if (count($logidlist) > 2) {
                $nextid = $logidlist[1];
                $data['nextdiff'] = $this->mod()->getURL(
                    'admin',
                    'showdiff',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                        'logids' => $nextid . '-' . $lastid]
                );
            }
        }

        $modinfo = $this->mod()->getInfo($modid);
        if (empty($modinfo['name'])) {
            return $data;
        }
        try {
            $itemlinks = $this->mod()->apiFunc(
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
