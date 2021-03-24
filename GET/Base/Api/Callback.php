<?php

namespace Acms\Plugins\Base\GET\Base\Api;

use Acms\Plugins\Base\GET\Base\Api;
use ACMS_Corrector;
use Template;
use Exception;

class Callback extends Api
{
    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());

        $client = $this->getClient();
        $code = $this->Get->get('code');
        $error = $this->Get->get('error', false);

        if ( empty($code) ) {
            return $Tpl->get();
        }
        if ( !!$error ) {
            $Tpl->add('error');
            return $Tpl->get();
        }

        try {
            $client->setGrantType('authorization_code');
            $client->requestAccessToken($code);

            $Tpl->add('success');
        } catch ( Exception $e ) {
            $Tpl->add('error', array('msg' => $e->getMessage()));
        }

        return $Tpl->get();
    }
}
