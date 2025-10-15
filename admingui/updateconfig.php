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
use sys;

sys::import('xaraya.modules.method');

/**
 * changelog admin updateconfig function
 * @extends MethodClass<AdminGui>
 */
class UpdateconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update configuration
     * @see AdminGui::updateconfig()
     */
    public function __invoke(array $args = [])
    {
        // Get parameters
        $this->var()->find('changelog', $changelog);
        $this->var()->find('includedd', $includedd);

        // Confirm authorisation code
        if (!$this->sec()->confirmAuthKey()) {
            return;
        }
        // Security Check
        if (!$this->sec()->checkAccess('AdminChangeLog')) {
            return;
        }

        if (isset($changelog) && is_array($changelog)) {
            foreach ($changelog as $modname => $value) {
                if ($modname == 'default') {
                    $this->mod()->setVar('default', $value);
                } else {
                    $this->mod()->setVar($modname, $value);
                }
            }
        }
        if (isset($includedd) && is_array($includedd)) {
            $withdd = join(';', array_keys($includedd));
            // Set the sort order of the changelog hooks to 999 to make sure they're called last
            if (defined('XARCORE_GENERATION') && XARCORE_GENERATION == 2) {
                // FIXME: change hook order in 2.x core
            } else {
                $dbconn = $this->db()->getConn();
                $xartable = $this->db()->getTables();
                $query = "UPDATE $xartable[hooks]
                             SET xar_order = 999
                           WHERE xar_tmodule = 'changelog'";
                $result = $dbconn->Execute($query);
                if (!$result) {
                    return;
                }
            }
        } else {
            $withdd = '';
        }
        $this->mod()->setVar('withdd', $withdd);

        $this->var()->find('numstats', $numstats, 'int', 100);
        $this->var()->find('showtitle', $showtitle, 'checkbox', false);
        $this->mod()->setVar('numstats', $numstats);
        $this->mod()->setVar('showtitle', $showtitle);

        $this->ctl()->redirect($this->mod()->getURL('admin', 'modifyconfig'));

        return true;
    }
}
