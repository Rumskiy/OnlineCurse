<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public static function sendJsonWithData($data,$resourceClass){
        if (!$data){
            return response([
                'status' => false,
                'message' => "Не знайдено запис"
            ],404);
        }
        if ($data instanceof \Illuminate\Database\Eloquent\Model){
            return response([
                'status' => true,
                'data' => $resourceClass::make($data)
            ]);
        }
        return response([
            'status' => true,
            'data' => $resourceClass::collection($data)
        ]);
    }

    public static function sendJsonWithToken($data,$resourceClass,$token){
        if (!$data){
            return response([
                'status' => false,
                'message' => "Не знайдено запис"
            ],404);
        }
        if ($data instanceof \Illuminate\Database\Eloquent\Model){
            return response([
                'status' => true,
                "token" => $token,
                'data' => $resourceClass::make($data)
            ]);
        }
        return response([
            'status' => true,
            "token" => $token,
            'data' => $resourceClass::collection($data)
        ]);
    }
}
