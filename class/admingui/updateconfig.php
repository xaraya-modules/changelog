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
        if (!xarVar::fetch('changelog', 'isset', $changelog, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('includedd', 'isset', $includedd, null, xarVar::NOT_REQUIRED)) {
            return;
        }

        // Confirm authorisation code
        if (!xarSec::confirmAuthKey()) {
            return;
        }
        // Security Check
        if (!xarSecurity::check('AdminChangeLog')) {
            return;
        }

        if (isset($changelog) && is_array($changelog)) {
            foreach ($changelog as $modname => $value) {
                if ($modname == 'default') {
                    xarModVars::set('changelog', 'default', $value);
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
        xarModVars::set('changelog', 'withdd', $withdd);

        if (!xarVar::fetch('numstats', 'int', $numstats, 100, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('showtitle', 'checkbox', $showtitle, false, xarVar::NOT_REQUIRED)) {
            return;
        }
        xarModVars::set('changelog', 'numstats', $numstats);
        xarModVars::set('changelog', 'showtitle', $showtitle);

        xarController::redirect(xarController::URL('changelog', 'admin', 'modifyconfig'), null, $this->getContext());

        return true;
    }
}
