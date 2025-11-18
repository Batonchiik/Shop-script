<?php

class shopGiftforyouPlugin extends shopPlugin
{
    public function routing($route = array())
    {
        return array(
            'giftforyou/' => 'frontend/giftPage',
            'giftforyou/send/' => 'frontend/send'
        );
    }
}