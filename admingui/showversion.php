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
use sys;
use BadParameterException;
use Exception;

sys::import('xaraya.modules.method');

/**
 * changelog admin showversion function
 * @extends MethodClass<AdminGui>
 */
class ShowversionMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show a particular version of a module item (or restore it if possible)
     * @see AdminGui::showversion()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        // TODO: add more restore options
        // List of currently supported restore modules (see API calls below)
        $supported = ['articles', 'dynamicdata', 'xarpages'];

        $this->var()->find('modid', $modid);
        $this->var()->find('itemtype', $itemtype);
        $this->var()->find('itemid', $itemid);
        $this->var()->find('logid', $logid);
        $this->var()->find('restore', $restore);
        $this->var()->find('confirm', $confirm);

        if (!$this->sec()->check('AdminChangeLog', 1, 'Item', "$modid:$itemtype:$itemid")) {
            return;
        }

        $data = $adminapi->getversion(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'logid' => $logid]
        );
        if (empty($data) || !is_array($data)) {
            return;
        }

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
            $data['remark'] = $this->var()->prep($data['remark']);
        }
        // 2template $data['date'] = $this->mls()->formatDate($data['date']);

        $data['link'] = $this->mod()->getURL(
            'admin',
            'showlog',
            ['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );

        $data['fields'] = [];

        $modinfo = $this->mod()->getInfo($modid);
        if (empty($modinfo['name'])) {
            return $data;
        }
        $data['modid'] = $modid;
        $data['itemtype'] = $itemtype;
        $data['itemid'] = $itemid;
        $data['modname'] = $modinfo['name'];

        if (empty($restore)) {
            $restore = null;
        } else {
            $restore = 1;
        }

        // Check for supported restore modules
        if (!empty($restore) && !in_array($modinfo['name'], $supported)) {
            $msg = 'Restoring items from module #(1) is currently not supported';
            $vars = [$modinfo['name']];
            throw new BadParameterException($vars, $msg);
        }

        // Check for confirmation
        if (!empty($confirm) && !$this->sec()->confirmAuthKey()) {
            return;
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

        if (!empty($data['content'])) {
            $fields = unserialize($data['content']);
            $data['content'] = '';

            if (!empty($itemtype)) {
                $getlist = $this->mod()->getVar($modinfo['name'] . '.' . $itemtype);
            }
            if (!isset($getlist)) {
                $getlist = $this->mod()->getVar($modinfo['name']);
            }
            if (!empty($getlist)) {
                $fieldlist = explode(',', $getlist);
            }
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
                // Note: we'll do the formatting in the template now
                $data['fields'][$field] = $value;
            }
        }

        // Restore this version of the module item
        if (!empty($confirm)) {
            if (empty($data['fields'])) {
                $msg = 'Nothing to restore';
                $vars = [];
                throw new BadParameterException($vars, $msg);
            }
            switch ($modinfo['name']) {
                case 'articles':
                    // Check mandatory fields (if necessary)
                    if (empty($data['fields']['aid'])) {
                        $data['fields']['aid'] = $itemid;
                    }
                    /*
                                    // Prepare optional fields (if necessary)
                                    if (!isset($data['fields']['module'])) {
                                        $data['fields']['module'] = $modinfo['name'];
                                    }
                                    if (!isset($data['fields']['itemtype'])) {
                                        $data['fields']['itemtype'] = $itemtype;
                                    }
                    */
                    // Call the update API function
                    $result = $this->mod()->apiFunc(
                        'articles',
                        'admin',
                        'update',
                        $data['fields']
                    );
                    if (empty($result)) {
                        return;
                    }
                    break;

                case 'dynamicdata':
                    // Call the update API function
                    $result = $this->mod()->apiFunc(
                        'dynamicdata',
                        'admin',
                        'update',
                        ['module_id' => $modid,
                            'itemtype' => $itemtype,
                            'itemid' => $itemid,
                            'values' => $data['fields']]
                    );
                    if (empty($result)) {
                        return;
                    }
                    break;

                case 'xarpages':
                    // Check mandatory fields (if necessary)
                    if (empty($data['fields']['pid'])) {
                        $data['fields']['pid'] = $itemid;
                    }
                    // Call the update API function
                    $result = $this->mod()->apiFunc(
                        'xarpages',
                        'admin',
                        'updatepage',
                        $data['fields']
                    );
                    if (empty($result)) {
                        return;
                    }
                    break;

                    // TODO: add more restore options
                default:
                    $msg = 'Restoring items from module #(1) is currently not supported';
                    $vars = [$modinfo['name']];
                    throw new BadParameterException($vars, $msg);
            }
            $this->ctl()->redirect($this->mod()->getURL(
                'admin',
                'showlog',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid]
            ));
            return true;
        }

        // get all changes
        $changes = $adminapi->getchanges(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid]
        );
        $numchanges = count($changes);
        $data['numversions'] = $numchanges;
        $nextid = 0;
        $previd = 0;
        $lastid = 0;
        $version = [];
        foreach (array_keys($changes) as $id) {
            $version[$id] = $numchanges;
            $numchanges--;
            if ($id == $logid) {
                $nextid = $lastid;
            } elseif ($lastid == $logid) {
                $previd = $id;
            }
            $lastid = $id;
        }

        $data['version'] = $version[$logid];
        if (!empty($nextid)) {
            $data['nextversion'] = $this->mod()->getURL(
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $nextid,
                    'restore' => $restore]
            );
            $data['nextdiff'] = $this->mod()->getURL(
                'admin',
                'showdiff',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logids' => $logid . '-' . $nextid]
            );
        }
        if (!empty($previd)) {
            $data['prevversion'] = $this->mod()->getURL(
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $previd,
                    'restore' => $restore]
            );
            $data['prevdiff'] = $this->mod()->getURL(
                'admin',
                'showdiff',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logids' => $previd . '-' . $logid]
            );
        }

        if (!empty($restore)) {
            $data['showlink'] = $this->mod()->getURL(
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $logid]
            );
            $data['confirmbutton'] = $this->ml('Confirm');
            // Generate a one-time authorisation code for this operation
            $data['authid'] = $this->sec()->genAuthKey();
            $data['restore'] = 1;
        } elseif (in_array($modinfo['name'], $supported)) {
            $data['restorelink'] = $this->mod()->getURL(
                'admin',
                'showversion',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'itemid' => $itemid,
                    'logid' => $logid,
                    'restore' => 1]
            );
        }

        return $data;
    }
}
