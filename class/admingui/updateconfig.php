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
use xarVar;
use xarSec;
use xarSecurity;
use xarModVars;
use xarDB;
use xarController;
use sys;
use BadParameterException;

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
     */
    public function __invoke(array $args = [])
    {
        // Get parameters
        if (!$this->fetch('changelog', 'isset', $changelog, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->fetch('includedd', 'isset', $includedd, null, xarVar::NOT_REQUIRED)) {
            return;
        }

        // Confirm authorisation code
        if (!$this->confirmAuthKey()) {
            return;
        }
        // Security Check
        if (!$this->checkAccess('AdminChangeLog')) {
            return;
        }

        if (isset($changelog) && is_array($changelog)) {
            foreach ($changelog as $modname => $value) {
                if ($modname == 'default') {
                    $this->setModVar('default', $value);
                } else {
                    xarModVars::set('changelog', $modname, $value);
                }
            }
        }
        if (isset($includedd) && is_array($includedd)) {
            $withdd = join(';', array_keys($includedd));
            // Set the sort order of the changelog hooks to 999 to make sure they're called last
            if (defined('XARCORE_GENERATION') && XARCORE_GENERATION == 2) {
                // FIXME: change hook order in 2.x core
            } else {
                $dbconn = xarDB::getConn();
                $xartable = xarDB::getTables();
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
        $this->setModVar('withdd', $withdd);

        if (!$this->fetch('numstats', 'int', $numstats, 100, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->fetch('showtitle', 'checkbox', $showtitle, false, xarVar::NOT_REQUIRED)) {
            return;
        }
        $this->setModVar('numstats', $numstats);
        $this->setModVar('showtitle', $showtitle);

        $this->redirect($this->getUrl('admin', 'modifyconfig'));

        return true;
    }
}
