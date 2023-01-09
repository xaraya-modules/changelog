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
/**
 * initialise the changelog module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function changelog_init()
{
    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();

    $changelogtable = $xartable['changelog'];

    //Load Table Maintenance API
    sys::import('xaraya.tableddl');
    //xarDBLoadTableMaintenanceAPI();
    $query = xarDBCreateTable(
        $xartable['changelog'],
        ['xar_logid'      => ['type'        => 'integer',
                                      'null'        => false,
                                      'default'     => '0',
                                      'increment'   => true,
                                      'primary_key' => true],
// TODO: replace with unique id
              'xar_moduleid'   => ['type'        => 'integer',
                                       'unsigned'    => true,
                                       'null'        => false,
                                       'default'     => '0'],
              'xar_itemtype'   => ['type'        => 'integer',
                                       'unsigned'    => true,
                                       'null'        => false,
                                       'default'     => '0'],
              'xar_itemid'     => ['type'        => 'integer',
                                       'unsigned'    => true,
                                       'null'        => false,
                                       'default'     => '0'],
              'xar_editor'     => ['type'        => 'integer',
                                       'unsigned'    => true,
                                       'null'        => false,
                                       'default'     => '0'],
              'xar_hostname'   => ['type'        => 'varchar',
                                       'size'        => 254,
                                       'null'        => false,
                                       'default'     => ''],
              'xar_date'       => ['type'        => 'integer',
                                       'unsigned'    => true,
                                       'null'        => false,
                                       'default'     => '0'],
              'xar_status'     => ['type'        => 'varchar',
                                       'size'        => 20,
                                       'null'        => false,
                                       'default'     => 'created'],
              'xar_remark'     => ['type'        => 'varchar',
                                       'size'        => 254,
                                       'null'        => false,
                                       'default'     => ''],
              'xar_content'    => ['type'        => 'text',
                                       'size'        => 'medium']]
    );

    if (empty($query)) {
        return;
    } // throw back

    // Pass the Table Create DDL to adodb to create the table and send exception if unsuccessful
    $result = &$dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = [
        'name'      => 'i_' . xarDB::getPrefix() . '_changelog_combo',
        'fields'    => ['xar_moduleid','xar_itemtype','xar_itemid'],
        'unique'    => false,
    ];
    $query = xarDBCreateIndex($changelogtable, $index);
    $result =& $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = [
        'name'      => 'i_' . xarDB::getPrefix() . '_changelog_editor',
        'fields'    => ['xar_editor'],
        'unique'    => false,
    ];
    $query = xarDBCreateIndex($changelogtable, $index);
    $result =& $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = [
        'name'      => 'i_' . xarDB::getPrefix() . '_changelog_status',
        'fields'    => ['xar_status'],
        'unique'    => false,
    ];
    $query = xarDBCreateIndex($changelogtable, $index);
    $result =& $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    xarModVars::set('changelog', 'SupportShortURLs', 0);
    xarModVars::set('changelog', 'numstats', 100);
    xarModVars::set('changelog', 'showtitle', false);

    /* // nothing to do here
        if (!xarModRegisterHook('item', 'new', 'GUI',
                               'changelog', 'admin', 'newhook')) {
            return false;
        }
    */
    if (!xarModRegisterHook(
        'item',
        'create',
        'API',
        'changelog',
        'admin',
        'createhook'
    )) {
        return false;
    }
    if (!xarModRegisterHook(
        'item',
        'modify',
        'GUI',
        'changelog',
        'admin',
        'modifyhook'
    )) {
        return false;
    }
    if (!xarModRegisterHook(
        'item',
        'update',
        'API',
        'changelog',
        'admin',
        'updatehook'
    )) {
        return false;
    }
    if (!xarModRegisterHook(
        'item',
        'delete',
        'API',
        'changelog',
        'admin',
        'deletehook'
    )) {
        return false;
    }
    if (!xarModRegisterHook(
        'module',
        'remove',
        'API',
        'changelog',
        'admin',
        'removehook'
    )) {
        return false;
    }
    if (!xarModRegisterHook(
        'item',
        'display',
        'GUI',
        'changelog',
        'user',
        'displayhook'
    )) {
        return false;
    }

    /* // TODO: show items you created/edited someday ?
        if (!xarModRegisterHook('item', 'usermenu', 'GUI',
                'changelog', 'user', 'usermenu')) {
            return false;
        }
    */

    $instances = [
                       ['header' => 'external', // this keyword indicates an external "wizard"
                             'query'  => xarModURL('changelog', 'admin', 'privileges'),
                             'limit'  => 0,
                            ],
                    ];
    xarDefineInstance('changelog', 'Item', $instances);

    // TODO: tweak this - allow viewing changelog of "your own items" someday ?
    xarRegisterMask('ReadChangeLog', 'All', 'changelog', 'Item', 'All:All:All', 'ACCESS_READ');
    xarRegisterMask('AdminChangeLog', 'All', 'changelog', 'Item', 'All:All:All', 'ACCESS_ADMIN');

    // Initialisation successful
    return true;
}

/**
 * upgrade the changelog module from an old version
 * This function can be called multiple times
 * @return bool
 */
function changelog_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch ($oldversion) {
        case '1.0':
            // Code to upgrade from version 1.0 goes here
            if (!xarModRegisterHook(
                'item',
                'display',
                'GUI',
                'changelog',
                'user',
                'displayhook'
            )) {
                return false;
            }
            break;
        case '1.1':
            // compatability upgrade
            break;
        case 2.0:
            // Code to upgrade from version 2.0 goes here
            break;
    }
    // Update successful
    return true;
}

/**
 * delete the changelog module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function changelog_delete()
{
    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();

    xarDBLoadTableMaintenanceAPI();

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['changelog']);
    if (empty($query)) {
        return;
    } // throw back

    // Drop the table and send exception if returns false.
    $result = &$dbconn->Execute($query);
    if (!$result) {
        return;
    }

    // Delete any module variables
    xarModVars::delete('changelog', 'SupportShortURLs');

    // Remove module hooks
    /* // nothing to do here
        if (!xarModUnregisterHook('item', 'new', 'GUI',
                               'changelog', 'admin', 'newhook')) {
            return false;
        }
    */
    if (!xarModUnregisterHook(
        'item',
        'create',
        'API',
        'changelog',
        'admin',
        'createhook'
    )) {
        return false;
    }
    if (!xarModUnregisterHook(
        'item',
        'modify',
        'GUI',
        'changelog',
        'admin',
        'modifyhook'
    )) {
        return false;
    }
    if (!xarModUnregisterHook(
        'item',
        'update',
        'API',
        'changelog',
        'admin',
        'updatehook'
    )) {
        return false;
    }
    if (!xarModUnregisterHook(
        'item',
        'delete',
        'API',
        'changelog',
        'admin',
        'deletehook'
    )) {
        return false;
    }
    // when a whole module is removed, e.g. via the modules admin screen
    // (set object ID to the module name !)
    if (!xarModUnregisterHook(
        'module',
        'remove',
        'API',
        'changelog',
        'admin',
        'removehook'
    )) {
        return false;
    }
    if (!xarModUnregisterHook(
        'item',
        'display',
        'GUI',
        'changelog',
        'user',
        'displayhook'
    )) {
        return false;
    }
    /* // TODO: show items you created/edited someday ?
        if (!xarModUnregisterHook('item', 'usermenu', 'GUI',
                'changelog', 'user', 'usermenu')) {
            return false;
        }
    */

    // Remove Masks and Instances
    xarRemoveMasks('changelog');
    xarRemoveInstances('changelog');

    // Deletion successful
    return true;
}
