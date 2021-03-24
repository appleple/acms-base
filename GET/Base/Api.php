<?php

namespace Acms\Plugins\Base\GET\Base;

use ACMS_GET;
use App;
use Acms\Plugins\Base\Api as BaseApi;

class Api extends ACMS_GET
{
    /**
     * @var AAPP_Base_Api
     */
    protected $api;

    /**
     * @return AAPP_Base_Api
     */
    public function getClient()
    {
        if ( $this->api ) {
            return $this->api;
        }

        $this->api = self::create();
        $this->api->setScope(array('read_users', 'read_items'));

        return $this->api;
    }

    public static function create()
    {
        $config = App::make('app-the-base-config');

        return new BaseApi(
            $config->get('base_client_id'),
            $config->get('base_client_secret'),
            BASE_URL. 'bid/' .BID .'/admin/app_base_index/'
        );
    }
}
