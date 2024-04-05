<?php
/**
 * Change Log Module
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
/**
 * the main administration function
 * Redirect to modifyconfig
 *
 * @author mikespub
 * @access public
 * @return bool true on success of redirect or void on failure
 */
function changelog_admin_main(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('AdminChangeLog')) {
        return;
    }

    xarController::redirect(xarController::URL('changelog', 'admin', 'view'), null, $context);
    // success
    return true;
}
