<?php

namespace Acms\Plugins\Base;
use ACMS_App;
use Config;
use Acms\Services\Common\InjectTemplate;

class ServiceProvider extends ACMS_App
{
    public $version     = '1.0.0';
    public $name        = 'The BASE';
    public $author      = 'com.appleple';
    public $module      = false;
    public $menu        = 'base_index';
    public $desc        = 'The BASEと連携するためのモジュールを提供します。';

    private $installTable = array(
        'base_api',
    );

    /**
     * サービスの起動処理
     */
    public function init()
    {
        $inject = InjectTemplate::singleton();
        if (ADMIN === 'app_base_index') {
            $inject->add('admin-topicpath', PLUGIN_DIR . 'Base/theme/topicpath.html');
            $inject->add('admin-main', PLUGIN_DIR . 'Base/theme/index.html');
        }
        $inject->add('admin-module-config-Base_Items', PLUGIN_DIR . 'Base/theme/items_body.html');
        $inject->add('admin-module-config-Base_Detail', PLUGIN_DIR . 'Base/theme/detail_body.html');
        $inject->add('admin-module-config-Base_Search', PLUGIN_DIR . 'Base/theme/search_body.html');
        $inject->add('admin-module-select', PLUGIN_DIR . 'Base/theme/select.user.html');
    }

    /**
     * インストールする前の環境チェック処理
     *
     * @return bool
     */
    public function checkRequirements()
    {
        return true;
    }

    /**
     * インストールするときの処理
     * データベーステーブルの初期化など
     *
     * @return void
     */
    public function install()
    {
        //------------
        // テーブル削除
        dbDropTables($this->installTable);

        //---------------------
        // テーブルデータ読み込み
        $yamlTable  = preg_replace('/%{PREFIX}/', DB_PREFIX, file_get_contents(dirname(__FILE__).'/db/schema.yaml'));
        $tablesData = Config::yamlParse($yamlTable);
        if ( !is_array($tablesData) ) $tablesData = array();
        if ( !empty($tablesData[0]) ) unset($tablesData[0]);
        $tableList  = array_merge(array_diff(array_keys($tablesData), array('')));

        $yamlIndex  = preg_replace('/%{PREFIX}/', DB_PREFIX, file_get_contents(dirname(__FILE__).'/db/index.yaml'));
        $indexData  = Config::yamlParse($yamlIndex);
        if ( !is_array($indexData) ) $indexData = array();
        if ( !empty($indexData[0]) ) unset($indexData[0]);

        //---------------
        // テーブル作成
        foreach ( $tableList as $tb ) {
            $index = isset($indexData[$tb]) ? $indexData[$tb] : null;
            dbCreateTables($tb, $tablesData[$tb], $index);
        }
    }

    /**
     * アンインストールするときの処理
     * データベーステーブルの始末など
     *
     * @return void
     */
    public function uninstall()
    {
        dbDropTables($this->installTable);
    }

    /**
     * アップデートするときの処理
     *
     * @return bool
     */
    public function update()
    {
        return true;
    }

    /**
     * 有効化するときの処理
     *
     * @return bool
     */
    public function activate()
    {
        return true;
    }

    /**
     * 無効化するときの処理
     *
     * @return bool
     */
    public function deactivate()
    {
        return true;
    }
}
