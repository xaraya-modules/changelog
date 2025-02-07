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

use Xaraya\Modules\AdminApiClass;
use sys;

sys::import('xaraya.modules.adminapi');

/**
 * Handle the changelog admin API
 *
 * @method mixed delete(array $args)
 * @method mixed getchanges(array $args)
 * @method mixed getmenulinks(array $args)
 * @method mixed getversion(array $args)
 * @extends AdminApiClass<Module>
 */
class AdminApi extends AdminApiClass
{
    // ...
}
