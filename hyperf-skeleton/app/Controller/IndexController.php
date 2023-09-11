<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use GuzzleHttp\Client;

class IndexController extends AbstractController
{

    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();
            try{
                $client=new Client(['base_uri'=>'http://127.0.0.1:9502','timeout'=>2]);
                $response=$client->get('/product/getPrice?id=1');
                if($response->getStatusCode()==200){
                    $body=$response->getBody()->getContents();
                    $data= json_decode($body,true);
                    var_dump($data);
                }

            }catch (\Exception $e){
                print_r($e->getMessage());
            }


        return [
            'method' => $method,
            'message' => "Hello {$user}.",
            'client'=>$data

        ];
    }
}
