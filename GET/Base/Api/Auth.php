<?php

namespace Acms\Plugins\Base\GET\Base\Api;
use Acms\Plugins\Base\GET\Base\Api;
use ACMS_GET;
use ACMS_Corrector;
use Template;
use Exception;

class Auth extends Api
{
    function get()
    {
        $Tpl = new Template($this->tpl, new ACMS_Corrector());
        $client = $this->getClient();
        $user = array();

        try {
            $json = $client->get('users/me');
            $user = $json->user;
        } catch ( Exception $e ) {}

        return $Tpl->render(array(
            'redirect_url' => $client->getAuthUrl(),
            'user' => $user,
        ));
    }
}
