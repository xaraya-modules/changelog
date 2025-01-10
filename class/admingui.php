<?php

/**
 * @package modules\changelog
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
 **/

namespace Xaraya\Modules\ChangeLog;

use Xaraya\Modules\AdminGuiClass;
use sys;

sys::import('xaraya.modules.admingui');
sys::import('modules.changelog.class.adminapi');

/**
 * Handle the changelog admin GUI
 * @extends AdminGuiClass<Module>
 */
class AdminGui extends AdminGuiClass
{
    // ...
}
