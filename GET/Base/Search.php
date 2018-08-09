<?php

namespace Acms\Plugins\Base\GET\Base;
use Acms\Plugins\Base\GET\Base\Api;
use ACMS_Corrector;
use ACMS_GET;
use Template;
use Exception;

class Search extends Api
{
    var $_scope = array(
        'keyword'   => 'global',
    );

    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $items = array();
        $error = '';

        try {
            $client = $this->getClient();
            $json = $client->get('search', array(
                'client_id'     => config('base_search_client_id'),
                'client_secret' => config('base_search_client_secret'),
                'q'             => $this->keyword,
                'sort'          => config('base_search_order', 'item') . ' ' . config('base_search_sort', 'asc'), // (item_id|price|stock|order_count|modified) + (asc|desc)
                'start'         => config('base_search_offset', 0),
                'size'          => config('base_search_limit', 20),
                'fields'        => config('base_search_fields', 'shop_name,title.detail,categories'),
                'shop_id'       => config('base_search_shop_id', ''),
            ));
            $items = $json->items;
        } catch ( Exception $e ) {
            $error = $e->getMessage();
        }

        return $Tpl->render(array(
            'error' => $error,
            'found' => $json->found,
            'start' => $json->start,
            'items' => $items,
        ));
    }
}
