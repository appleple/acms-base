<?php

namespace Acms\Plugins\Base\GET\Base;
use Acms\Plugins\Base\GET\Base\Api;
use ACMS_Corrector;
use ACMS_GET;
use Template;
use Exception;

class Items extends Api
{
    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $items = array();
        $error = '';

        try {
            $client = $this->getClient();
            $json = $client->get('items', array(
                'order'     => config('base_items_order', 'list_order'), // list_order|created
                'sort'      => config('base_items_sort', 'asc'), // asc|desc
                'limit'     => config('base_items_limit', 20), // MAX: 100
                'offset'    => config('base_items_offset', 0),
            ));
            $items = $json->items;
        } catch ( Exception $e ) {
            $error = $e->getMessage();
        }


        return $Tpl->render(array(
            'error' => $error,
            'items' => $items,
        ));
    }
}
