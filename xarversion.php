<?php
/**
 * Change Log Module version information
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
$modversion['name']           = 'Change Log';
$modversion['id']             = '185';
$modversion['version']        = '2.4.2';
$modversion['displayname']    = xarMLS::translate('ChangeLog');
$modversion['description']    = 'Keep track of changes to module items';
$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'mikespub';
$modversion['contact']        = 'http://www.xaraya.com/';
$modversion['admin']          = 1;
$modversion['user']           = 0;
$modversion['class']          = 'Utility';
$modversion['category']       = 'Miscellaneous';
$modversion['namespace']      = 'Xaraya\Modules\ChangeLog';
$modversion['twigtemplates']  = true;
$modversion['dependencyinfo'] = [
    0 => [
        'name' => 'Xaraya Core',
        'version_ge' => '2.4.1',
    ],
];
