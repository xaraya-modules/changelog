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
sys::import('modules.changelog.class.difflib');
use Xaraya\Modules\ChangeLog\DiffLib;

/**
 * show the differences between 2 versions of a module item
 */
function changelog_admin_showdiff(array $args = [], $context = null)
{
    extract($args);

    if (!xarVar::fetch('modid', 'isset', $modid, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('itemtype', 'isset', $itemtype, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    if (!xarVar::fetch('itemid', 'isset', $itemid, null, xarVar::NOT_REQUIRED)) {
        return;
    }
    // Note : this is an array or a string here
    if (!xarVar::fetch('logids', 'isset', $logids, null, xarVar::NOT_REQUIRED)) {
        return;
    }

    if (!xarSecurity::check('AdminChangeLog', 1, 'Item', "$modid:$itemtype:$itemid")) {
        return;
    }

    // get all changes
    $changes = xarMod::apiFunc(
        'changelog',
        'admin',
        'getchanges',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid]
    );
    if (empty($changes) || !is_array($changes)) {
        return;
    }

    if (empty($logids)) {
        $logidlist = [];
    } elseif (is_string($logids)) {
        $logidlist = explode('-', $logids);
    } else {
        $logidlist = $logids;
    }
    sort($logidlist, SORT_NUMERIC);
    if (count($logidlist) < 2) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['number of versions', 'admin', 'showdiff', 'changelog'];
        throw new BadParameterException($vars, $msg);
    } elseif (!isset($changes[$logidlist[0]]) || !isset($changes[$logidlist[1]])) {
        $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
        $vars = ['version ids', 'admin', 'showdiff', 'changelog'];
        throw new BadParameterException($vars, $msg);
    }

    $data = [];

    $oldid = $logidlist[0];
    $newid = $logidlist[1];

    $numchanges = count($changes);
    $data['numversions'] = $numchanges;
    $nextid = 0;
    $previd = 0;
    $lastid = 0;
    $version = [];
    foreach (array_keys($changes) as $id) {
        $version[$id] = $numchanges;
        $numchanges--;
        if ($id == $newid) {
            $nextid = $lastid;
        } elseif ($lastid == $oldid) {
            $previd = $id;
        }
        $lastid = $id;
    }

    $data['oldversion'] = $version[$oldid];
    $data['newversion'] = $version[$newid];
    if (!empty($nextid)) {
        $data['nextdiff'] = xarController::URL(
            'changelog',
            'admin',
            'showdiff',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logids' => $newid . '-' . $nextid]
        );
    }
    if (!empty($previd)) {
        $data['prevdiff'] = xarController::URL(
            'changelog',
            'admin',
            'showdiff',
            ['modid' => $modid,
                  'itemtype' => $itemtype,
                  'itemid' => $itemid,
                  'logids' => $previd . '-' . $oldid]
        );
    }

    $data['changes'] = [];
    $data['changes'][$newid] = $changes[$newid];
    $data['changes'][$oldid] = $changes[$oldid];

    if (xarSecurity::check('AdminChangeLog', 0)) {
        $data['showhost'] = 1;
    } else {
        $data['showhost'] = 0;
    }

    foreach (array_keys($data['changes']) as $logid) {
        $data['changes'][$logid]['profile'] = xarController::URL(
            'roles',
            'user',
            'display',
            ['id' => $data['changes'][$logid]['editor']]
        );
        if (!$data['showhost']) {
            $data['changes'][$logid]['hostname'] = '';
            $data['changes'][$logid]['link'] = '';
        } else {
            $data['changes'][$logid]['link'] = xarController::URL(
                'changelog',
                'admin',
                'showversion',
                ['modid' => $modid,
                      'itemtype' => $itemtype,
                      'itemid' => $itemid,
                      'logid' => $logid]
            );
        }
        if (!empty($data['changes'][$logid]['remark'])) {
            $data['changes'][$logid]['remark'] = xarVar::prepForDisplay($data['changes'][$logid]['remark']);
        }
        // 2template $data['changes'][$logid]['date'] = xarLocale::formatDate($data['changes'][$logid]['date']);
        $data['changes'][$logid]['version'] = $version[$logid];
    }

    $data['link'] = xarController::URL(
        'changelog',
        'admin',
        'showlog',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid]
    );

    $modinfo = xarMod::getInfo($modid);
    if (empty($modinfo['name'])) {
        return $data;
    }
    try {
        $itemlinks = xarMod::apiFunc(
            $modinfo['name'],
            'user',
            'getitemlinks',
            ['itemtype' => $itemtype,
            'itemids' => [$itemid]]
        );
    } catch (Exception $e) {
        $itemlinks = [];
    }
    if (isset($itemlinks[$itemid])) {
        $data['itemlink'] = $itemlinks[$itemid]['url'];
        $data['itemtitle'] = $itemlinks[$itemid]['title'];
        $data['itemlabel'] = $itemlinks[$itemid]['label'];
    }

    if (!empty($itemtype)) {
        $getlist = xarModVars::get('changelog', $modinfo['name'] . '.' . $itemtype);
    }
    if (!isset($getlist)) {
        $getlist = xarModVars::get('changelog', $modinfo['name']);
    }
    if (!empty($getlist)) {
        $fieldlist = explode(',', $getlist);
    }

    $old = xarMod::apiFunc(
        'changelog',
        'admin',
        'getversion',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid,
              'logid' => $oldid]
    );
    if (empty($old) || !is_array($old)) {
        return;
    }

    if (!empty($old['content'])) {
        $fields = unserialize($old['content']);
        $old['content'] = '';

        ksort($fields);
        foreach ($fields as $field => $value) {
            // skip some common uninteresting fields
            if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                continue;
            }
            // skip fields we don't want here
            if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $value = serialize($value);
            }
            $old['fields'][$field] = $value;
        }
    }
    if (!isset($old['fields'])) {
        $old['fields'] = [];
    }

    $new = xarMod::apiFunc(
        'changelog',
        'admin',
        'getversion',
        ['modid' => $modid,
              'itemtype' => $itemtype,
              'itemid' => $itemid,
              'logid' => $newid]
    );
    if (empty($new) || !is_array($new)) {
        return;
    }

    if (!empty($new['content'])) {
        $fields = unserialize($new['content']);
        $new['content'] = '';

        ksort($fields);
        foreach ($fields as $field => $value) {
            // skip some common uninteresting fields
            if ($field == 'module' || $field == 'itemtype' || $field == 'itemid' ||
                $field == 'mask' || $field == 'pass' || $field == 'changelog_remark') {
                continue;
            }
            // skip fields we don't want here
            if (!empty($fieldlist) && !in_array($field, $fieldlist)) {
                continue;
            }
            if (is_array($value) || is_object($value)) {
                $value = serialize($value);
            }
            $new['fields'][$field] = $value;
        }
    }
    if (!isset($new['fields'])) {
        $new['fields'] = [];
    }

    $fieldlist = array_unique(array_merge(array_keys($old['fields']), array_keys($new['fields'])));
    ksort($fieldlist);

    $data['fields'] = [];
    foreach ($fieldlist as $field) {
        if (!isset($old['fields'][$field])) {
            $old['fields'][$field] = '';
        }
        if (!isset($new['fields'][$field])) {
            $new['fields'][$field] = '';
        }
        $diff = new DiffLib\Diff(explode("\n", $old['fields'][$field]), explode("\n", $new['fields'][$field]));
        $data['fields'][$field] = [];
        if ($diff->isEmpty()) {
            $data['fields'][$field]['diff'] = '';
        } else {
            $fmt = new XarayaDiffFormatter();
            $difference = $fmt->format($diff);
            $data['fields'][$field]['diff'] = nl2br($difference);
        }
        $data['fields'][$field]['old'] = nl2br(xarVar::prepForDisplay($old['fields'][$field]));
        $data['fields'][$field]['new'] = nl2br(xarVar::prepForDisplay($new['fields'][$field]));
    }

    return $data;
}

class XarayaDiffFormatter extends DiffLib\UnifiedDiffFormatter
{
    public function _block_header($xbeg, $xlen, $ybeg, $ylen)
    {
        return parent::_block_header($xbeg, $xlen, $ybeg, $ylen);
    }

    public function _lines($lines, $prefix = '', $postfix = '')
    {
        foreach ($lines as $line) {
            echo $prefix . xarVar::prepForDisplay($line) . $postfix . "\n";
        }
    }

    public function _added($lines)
    {
        $this->_lines($lines, '<ins>', '</ins>');
    }

    public function _deleted($lines)
    {
        $this->_lines($lines, '<del>', '</del>');
    }
}
