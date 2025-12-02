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

class Tables
{
    /**
     * Return changelog table names to xaraya
     *
     * This function is called internally by the core whenever the module is
     * loaded.  It is loaded by xar::mod()->loadDbInfo().
     *
     * @access private
     * @return array
     */
    public function __invoke(string $prefix = 'xar')
    {
        // Initialise table array
        $xarTables = [];
        // Get the name for the changelog item table.  This is not necessary
        // but helps in the following statements and keeps them readable
        $changelogTable = $prefix . '_changelog';
        // Set the table name
        $xarTables['changelog'] = $changelogTable;
        // Return the table information
        return $xarTables;
    }
}
