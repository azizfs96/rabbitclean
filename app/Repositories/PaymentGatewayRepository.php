<?php
namespace App\Repositories;


use App\Models\PaymentGateway;
use App\Http\Requests\PaymentGatewayRequest;

class PaymentGatewayRepository extends Repository
{
    public function model()

    {
        return PaymentGateway::class;
    }

    public function updateByRequest(PaymentGatewayRequest $request, PaymentGateway $paymentGateway): PaymentGateway
    {
        $config = json_encode($request->config);
        $media = $paymentGateway->media;

        if ($request->hasFile('logo')) {
            $media = (new MediaRepository())->updateOrCreateByRequest($request->logo, 'gateway/logo', 'Image', $media);
        }
        $paymentGateway->update([
            'mode' => $request->mode,
            'title' => $request->title,
            'media_id' => $media->id ?? null,
            'config' => $config,
        ]);

        return $paymentGateway;
    }

   

}
