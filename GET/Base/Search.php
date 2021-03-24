<?php

namespace Acms\Plugins\Base\GET\Base;

use ACMS_Corrector;
use App;
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
        $config = App::make('app-the-base-config');
        $items = array();
        $error = '';
        $found = 0;
        $start = 0;

        try {
            $client = $this->getClient();
            $json = $client->get('search', array(
                'client_id'     => $config->get('base_search_client_id'),
                'client_secret' => $config->get('base_search_client_secret'),
                'q'             => $this->keyword,
                'sort'          => config('base_search_order', 'item') . ' ' . config('base_search_sort', 'asc'), // (item_id|price|stock|order_count|modified) + (asc|desc)
                'start'         => config('base_search_offset', 0),
                'size'          => config('base_search_limit', 20),
                'fields'        => config('base_search_fields', 'shop_name,title.detail,categories'),
                'shop_id'       => config('base_search_shop_id', ''),
            ));
            if (empty($json)) {
                throw new \RuntimeException('Failed to get json.');
            }
            $items = $json->items;
            $found = $json->found;
            $start = $json->start;
        } catch ( Exception $e ) {
            $error = $e->getMessage();
        }

        return $Tpl->render(array(
            'error' => $error,
            'found' => $found,
            'start' => $start,
            'items' => $items,
        ));
    }
}
