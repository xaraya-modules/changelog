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
use Xaraya\Modules\ChangeLog\UserApi;
use Xaraya\Modules\MethodClass;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * changelog admin view function
 * @extends MethodClass<AdminGui>
 */
class ViewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * View changelog entries
     * @see AdminGui::view()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminChangeLog')) {
            return;
        }

        $this->var()->check('modid', $modid);
        $this->var()->check('itemtype', $itemtype);
        $this->var()->check('itemid', $itemid);
        $this->var()->check('sort', $sort);
        $this->var()->find('startnum', $startnum, 'isset', 1);
        $this->var()->check('editor', $editor);

        if (empty($editor) || !is_numeric($editor)) {
            $editor = null;
        }

        $data = [];
        $data['editor'] = $editor;

        $modlist = $userapi->getmodules(['editor' => $editor]
        );

        if (empty($modid)) {
            $data['moditems'] = [];
            $data['numitems'] = 0;
            $data['numchanges'] = 0;
            foreach ($modlist as $modid => $itemtypes) {
                $modinfo = $this->mod()->getInfo($modid);
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                foreach ($itemtypes as $itemtype => $stats) {
                    $moditem = [];
                    $moditem['numitems'] = $stats['items'];
                    $moditem['numchanges'] = $stats['changes'];
                    if ($itemtype == 0) {
                        $moditem['name'] = ucwords($modinfo['displayname']);
                        //    $moditem['link'] = $this->ctl()->getModuleURL($modinfo['name'],'user','main');
                    } else {
                        if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                            $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                            //    $moditem['link'] = $mytypes[$itemtype]['url'];
                        } else {
                            $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                            //    $moditem['link'] = $this->ctl()->getModuleURL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                        }
                    }
                    $moditem['link'] = $this->mod()->getURL(
                        'admin',
                        'view',
                        ['modid' => $modid,
                            'itemtype' => empty($itemtype) ? null : $itemtype,
                            'editor' => $editor]
                    );
                    $moditem['delete'] = $this->mod()->getURL(
                        'admin',
                        'delete',
                        ['modid' => $modid,
                            'itemtype' => empty($itemtype) ? null : $itemtype,
                            'editor' => $editor]
                    );
                    $data['moditems'][] = $moditem;
                    $data['numitems'] += $moditem['numitems'];
                    $data['numchanges'] += $moditem['numchanges'];
                }
            }
            $data['delete'] = $this->mod()->getURL(
                'admin',
                'delete',
                ['editor' => $editor]
            );
        } else {
            $modinfo = $this->mod()->getInfo($modid);
            if (empty($itemtype)) {
                $data['modname'] = ucwords($modinfo['displayname']);
                $itemtype = null;
                if (isset($modlist[$modid][0])) {
                    $stats = $modlist[$modid][0];
                }
            } else {
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    //    $data['modlink'] = $mytypes[$itemtype]['url'];
                } else {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    //    $data['modlink'] = $this->ctl()->getModuleURL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                }
                if (isset($modlist[$modid][$itemtype])) {
                    $stats = $modlist[$modid][$itemtype];
                }
            }
            if (isset($stats)) {
                $data['numitems'] = $stats['items'];
                $data['numchanges'] = $stats['changes'];
            } else {
                $data['numitems'] = 0;
                $data['numchanges'] = '';
            }
            $numstats = $this->mod()->getVar('numstats');
            if (empty($numstats)) {
                $numstats = 100;
            }
            // pager
            $data['startnum'] = $startnum;
            $data['total'] = $data['numitems'];
            $data['urltemplate'] = $this->mod()->getURL(
                'admin',
                'view',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'editor' => $editor,
                    'sort' => $sort,
                    'startnum' => '%%']
            );
            $data['itemsperpage'] = $numstats;

            $data['modid'] = $modid;
            $getitems = $userapi->getitems(['modid' => $modid,
                    'itemtype' => $itemtype,
                    'editor' => $editor,
                    'numitems' => $numstats,
                    'startnum' => $startnum,
                    'sort' => $sort]
            );
            $showtitle = $this->mod()->getVar('showtitle');
            if (!empty($showtitle)) {
                $itemids = array_keys($getitems);
                try {
                    $itemlinks = $this->mod()->apiFunc(
                        $modinfo['name'],
                        'user',
                        'getitemlinks',
                        ['itemtype' => $itemtype,
                            'itemids' => $itemids]
                    );
                } catch (Exception $e) {
                    $itemlinks = [];
                }
            } else {
                $itemlinks = [];
            }
            $data['moditems'] = [];
            foreach ($getitems as $itemid => $numchanges) {
                $data['moditems'][$itemid] = [];
                $data['moditems'][$itemid]['numchanges'] = $numchanges;
                $data['moditems'][$itemid]['showlog'] = $this->mod()->getURL(
                    'admin',
                    'showlog',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid]
                );
                $data['moditems'][$itemid]['delete'] = $this->mod()->getURL(
                    'admin',
                    'delete',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid,
                        'editor' => $editor]
                );
                if (isset($itemlinks[$itemid])) {
                    $data['moditems'][$itemid]['link'] = $itemlinks[$itemid]['url'];
                    $data['moditems'][$itemid]['title'] = $itemlinks[$itemid]['label'];
                }
            }
            unset($getitems);
            unset($itemlinks);
            $data['delete'] = $this->mod()->getURL(
                'admin',
                'delete',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'editor' => $editor]
            );
            $data['sortlink'] = [];
            if (empty($sort) || $sort == 'itemid') {
                $data['sortlink']['itemid'] = '';
            } else {
                $data['sortlink']['itemid'] = $this->mod()->getURL(
                    'admin',
                    'view',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'editor' => $editor]
                );
            }
            if (!empty($sort) && $sort == 'numchanges') {
                $data['sortlink']['numchanges'] = '';
            } else {
                $data['sortlink']['numchanges'] = $this->mod()->getURL(
                    'admin',
                    'view',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'editor' => $editor,
                        'sort' => 'numchanges']
                );
            }
        }

        return $data;
    }
}
