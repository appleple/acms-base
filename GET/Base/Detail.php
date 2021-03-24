<?php

namespace Acms\Plugins\Base\GET\Base;

use ACMS_Corrector;
use Template;
use RuntimeException;
use Exception;

class Detail extends Api
{
    var $_scope = array(
        'bid' => 'global',
        'cid' => 'global',
        'eid' => 'global',
    );

    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $error = '';
        $item = array();


        try {
            $fieldType = config('base_detail_field_type', 'entry');
            $fieldName = config('base_detail_field_name', 'base_item_id');
            $item_id = $this->getItemId($fieldType, $fieldName);

            $client = $this->getClient();
            $json = $client->get('items/detail/' . $item_id, array());
            $item = $json->item;
        } catch ( Exception $e ) {
            $error = $e->getMessage();
        }

        return $Tpl->render(array(
            'error' => $error,
            'item' => $item,
        ));
    }

    /**
     * get item id
     *
     * @param string $type
     * @param string $name
     * @return string
     */
    protected function getItemId($type, $name)
    {
        $field = null;
        switch ( $type ) {
            case 'entry':
                $field = loadEntryField($this->eid);
                break;
            case 'category':
                $field = loadCategoryField($this->cid);
                break;
            case 'blog':
                $field = loadBlogField($this->bid);
                break;
            default:
                throw new RuntimeException('Failed to get item id.');
        }

        if ( $item_id = $field->get($name) ) {
            return $item_id;
        }

        throw new RuntimeException('Failed to get item id.');
    }
}
