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
 *
 * @method mixed delete(array $args)
 * @method mixed hooks(array $args)
 * @method mixed main(array $args)
 * @method mixed modifyconfig(array $args)
 * @method mixed overview(array $args)
 * @method mixed privileges(array $args)
 * @method mixed showdiff(array $args)
 * @method mixed showlog(array $args)
 * @method mixed showversion(array $args)
 * @method mixed updateconfig(array $args)
 * @method mixed view(array $args)
 * @extends AdminGuiClass<Module>
 */
class AdminGui extends AdminGuiClass
{
    // ...
}
