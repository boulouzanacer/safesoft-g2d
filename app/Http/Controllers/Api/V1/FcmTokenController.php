<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FcmTokenStoreRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class FcmTokenController extends Controller
{
    use ApiResponseTrait;

    public function store(FcmTokenStoreRequest $request)
    {
        $data = $request->validated();
        $clientId = (int) $request->user()->id;

        DB::table('fcm_tokens')->updateOrInsert(
            [
                'client_id' => $clientId,
                'device_type' => $data['device_type'],
            ],
            [
                'token' => $data['token'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return $this->success(null, 'Token FCM enregistré');
    }
}
