<?php
class ShopController extends AppController
{
    //http://localhost/shopping/shop/shopResult
    public function shopResult()
    {
        file_put_contents(DIR.'/runtime/logs/'.time().'.txt', file_get_contents('php://input'));
        echo 'ok';
    }
}