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
use Xaraya\Modules\MethodClass;
use xarPrivileges;

/**
 * changelog admin privileges function
 * @extends MethodClass<AdminGui>
 */
class PrivilegesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Manage definition of instances for privileges (unfinished)
     * @see AdminGui::privileges()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminChangeLog')) {
            return;
        }

        extract($args);

        // fixed params
        $this->var()->check('moduleid', $moduleid);
        $this->var()->check('itemtype', $itemtype);
        $this->var()->check('itemid', $itemid);
        $this->var()->check('apply', $apply);
        $this->var()->check('extpid', $extpid);
        $this->var()->check('extname', $extname);
        $this->var()->check('extrealm', $extrealm);
        $this->var()->check('extmodule', $extmodule);
        $this->var()->check('extcomponent', $extcomponent);
        $this->var()->check('extinstance', $extinstance);
        $this->var()->check('extlevel', $extlevel);

        if (!empty($extinstance)) {
            $parts = explode(':', $extinstance);
            if (count($parts) > 0 && !empty($parts[0])) {
                $moduleid = $parts[0];
            }
            if (count($parts) > 1 && !empty($parts[1])) {
                $itemtype = $parts[1];
            }
            if (count($parts) > 2 && !empty($parts[2])) {
                $itemid = $parts[2];
            }
        }

        // Get the list of all modules currently hooked to categories
        $hookedmodlist = $this->mod()->apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'changelog']
        );
        if (!isset($hookedmodlist)) {
            $hookedmodlist = [];
        }
        $modlist = [];
        foreach ($hookedmodlist as $modname => $val) {
            if (empty($modname)) {
                continue;
            }
            $modid = $this->mod()->getRegID($modname);
            if (empty($modid)) {
                continue;
            }
            $modinfo = $this->mod()->getInfo($modid);
            $modlist[$modid] = $modinfo['displayname'];
        }

        if (empty($moduleid) || $moduleid == 'All' || !is_numeric($moduleid)) {
            $moduleid = 0;
        }
        if (empty($itemtype) || $itemtype == 'All' || !is_numeric($itemtype)) {
            $itemtype = 0;
        }
        if (empty($itemid) || $itemid == 'All' || !is_numeric($itemid)) {
            $itemid = 0;
        }

        // define the new instance
        $newinstance = [];
        $newinstance[] = empty($moduleid) ? 'All' : $moduleid;
        $newinstance[] = empty($itemtype) ? 'All' : $itemtype;
        $newinstance[] = empty($itemid) ? 'All' : $itemid;

        if (!empty($apply)) {
            // create/update the privilege
            $pid = xarPrivileges::external($extpid, $extname, $extrealm, $extmodule, $extcomponent, $newinstance, $extlevel);
            if (empty($pid)) {
                return; // throw back
            }

            // redirect to the privilege
            $this->ctl()->redirect($this->ctl()->getModuleURL(
                'privileges',
                'admin',
                'modifyprivilege',
                ['pid' => $pid]
            ));
            return true;
        }

        /*
            if (!empty($moduleid)) {
                $numitems = $this->mod()->apiFunc('categories','user','countitems',
                                          array('modid' => $moduleid,
                                                'cids'  => (empty($cid) ? null : array($cid))
                                               ));
            } else {
                $numitems = $this->ml('probably');
            }
        */
        $numitems = $this->ml('probably');

        $data = [
            'moduleid'     => $moduleid,
            'itemtype'     => $itemtype,
            'itemid'       => $itemid,
            'modlist'      => $modlist,
            'numitems'     => $numitems,
            'extpid'       => $extpid,
            'extname'      => $extname,
            'extrealm'     => $extrealm,
            'extmodule'    => $extmodule,
            'extcomponent' => $extcomponent,
            'extlevel'     => $extlevel,
            'extinstance'  => $this->prep()->text(join(':', $newinstance)),
        ];

        $data['refreshlabel'] = $this->ml('Refresh');
        $data['applylabel'] = $this->ml('Finish and Apply to Privilege');

        return $data;
    }
}
