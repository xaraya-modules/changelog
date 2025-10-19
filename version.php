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

namespace Xaraya\Modules\ChangeLog;

class Version
{
    /**
     * Get module version information
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'name' => 'Change Log',
            'id' => '185',
            'version' => '2.4.2',
            'displayname' => 'ChangeLog',
            'description' => 'Keep track of changes to module items',
            'credits' => '',
            'help' => '',
            'changelog' => '',
            'license' => '',
            'official' => 1,
            'author' => 'mikespub',
            'contact' => 'http://www.xaraya.com/',
            'admin' => 1,
            'user' => 0,
            'class' => 'Utility',
            'category' => 'Miscellaneous',
            'namespace' => 'Xaraya\\Modules\\ChangeLog',
            'twigtemplates' => true,
            'dependencyinfo'
             => [
                 0
                  => [
                      'name' => 'Xaraya Core',
                      'version_ge' => '2.4.1',
                  ],
             ],
        ];
    }
}
