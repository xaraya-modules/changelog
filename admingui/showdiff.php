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
use Xaraya\Modules\ChangeLog\DiffLib;
use Xaraya\Modules\MethodClass;
use xarVar;
use xarSecurity;
use xarMod;
use xarController;
use xarLocale;
use xarModVars;
use sys;
use BadParameterException;
use Exception;

sys::import('xaraya.modules.method');

/**
 * changelog admin showdiff function
 * @extends MethodClass<AdminGui>
 */
class ShowdiffMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the differences between 2 versions of a module item
     * @see AdminGui::showdiff()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        $this->var()->find('modid', $modid);
        $this->var()->find('itemtype', $itemtype);
        $this->var()->find('itemid', $itemid);
        // Note : this is an array or a string here
        $this->var()->find('logids', $logids);

        if (!xarSecurity::check('AdminChangeLog', 1, 'Item', "$modid:$itemtype:$itemid")) {
            return;
        }

        // get all changes
        $changes = $adminapi->getchanges(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );
        if (empty($changes) || !is_array($changes)) {
            return;
        }

        if (empty($logids)) {
            $logidlist = [];
        } elseif (is_string($logids)) {
            $logidlist = explode('-', $logids);
        } else {
            $logidlist = $logids;
        }
        sort($logidlist, SORT_NUMERIC);
        if (count($logidlist) < 2) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['number of versions', 'admin', 'showdiff', 'changelog'];
            throw new BadParameterException($vars, $msg);
        } elseif (!isset($changes[$logidlist[0]]) || !isset($changes[$logidlist[1]])) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['version ids', 'admin', 'showdiff', 'changelog'];
            throw new BadParameterException($vars, $msg);
        }

        $data = [];

        $oldid = $logidlist[0];
        $newid = $logidlist[1];

        $numchanges = count($changes);
        $data['numversions'] = $numchanges;
        $nextid = 0;
        $previd = 0;
        $lastid = 0;
        $version = [];
        foreach (array_keys($changes) as $id) {
            $version[$id] = $numchanges;
            $numchanges--;
            if ($id == $newid) {
                $nextid = $lastid;
            } elseif ($lastid == $oldid) {
                $previd = $id;
            }
            $lastid = $id;
        }

        $data['oldversion'] = $version[$oldid];
        $data['newversion'] = $version[$newid];
        if (!empty($nextid)) {
            $data['nextdiff'] = $this->mod()->getURL(
                'admin',
                'showdiff',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logids' => $newid . '-' . $nextid]
            );
        }
        if (!empty($previd)) {
            $data['prevdiff'] = $this->mod()->getURL(
                'admin',
                'showdiff',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logids' => $previd . '-' . $oldid]
            );
        }

        $data['changes'] = [];
        $data['changes'][$newid] = $changes[$newid];
        $data['changes'][$oldid] = $changes[$oldid];

        if ($this->sec()->checkAccess('AdminChangeLog', 0)) {
            $data['showhost'] = 1;
        } else {
            $data['showhost'] = 0;
        }

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
            // 2template $data['changes'][$logid]['date'] = xarLocale::formatDate($data['changes'][$logid]['date']);
            $data['changes'][$logid]['version'] = $version[$logid];
        }

        $data['link'] = $this->mod()->getURL(
            'admin',
            'showlog',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );

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

        if (!empty($itemtype)) {
            $getlist = $this->mod()->getVar($modinfo['name'] . '.' . $itemtype);
        }
        if (!isset($getlist)) {
            $getlist = $this->mod()->getVar($modinfo['name']);
        }
        if (!empty($getlist)) {
            $fieldlist = explode(',', $getlist);
        }

        $old = $adminapi->getversion(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'logid' => $oldid]
        );
        if (empty($old) || !is_array($old)) {
            return;
        }

        if (!empty($old['content'])) {
            $fields = unserialize($old['content']);
            $old['content'] = '';

            ksort($fields);
            foreach ($fields as $field => $value) {
                // skip some common uninteresting fields
                if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                    $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                    continue;
                }
                // skip fields we don't want here
                if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                    continue;
                }
                if (is_array($value) || is_object($value)) {
                    $value = serialize($value);
                }
                $old['fields'][$field] = $value;
            }
        }
        if (!isset($old['fields'])) {
            $old['fields'] = [];
        }

        $new = $adminapi->getversion(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'logid' => $newid]
        );
        if (empty($new) || !is_array($new)) {
            return;
        }

        if (!empty($new['content'])) {
            $fields = unserialize($new['content']);
            $new['content'] = '';

            ksort($fields);
            foreach ($fields as $field => $value) {
                // skip some common uninteresting fields
                if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                    $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                    continue;
                }
                // skip fields we don't want here
                if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                    continue;
                }
                if (is_array($value) || is_object($value)) {
                    $value = serialize($value);
                }
                $new['fields'][$field] = $value;
            }
        }
        if (!isset($new['fields'])) {
            $new['fields'] = [];
        }

        $fieldlist = array_unique(array_merge(array_keys($old['fields']), array_keys($new['fields'])));
        ksort($fieldlist);

        $data['fields'] = [];
        foreach ($fieldlist as $field) {
            if (!isset($old['fields'][$field])) {
                $old['fields'][$field] = '';
            }
            if (!isset($new['fields'][$field])) {
                $new['fields'][$field] = '';
            }
            $diff = new DiffLib\Diff(explode("\n", $old['fields'][$field]), explode("\n", $new['fields'][$field]));
            $data['fields'][$field] = [];
            if ($diff->isEmpty()) {
                $data['fields'][$field]['diff'] = '';
            } else {
                // @todo no idea where this is now
                $fmt = new XarayaDiffFormatter();
                $difference = $fmt->format($diff);
                $data['fields'][$field]['diff'] = nl2br($difference);
            }
            $data['fields'][$field]['old'] = nl2br($this->var()->prep($old['fields'][$field]));
            $data['fields'][$field]['new'] = nl2br($this->var()->prep($new['fields'][$field]));
        }

        return $data;
    }
}
