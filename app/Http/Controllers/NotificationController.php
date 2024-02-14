<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Client;
use App\Models\Car;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Validator, Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Decimal;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $notifications = Notification::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();
        $notificationsResponse = collect();

        if ($notifications) {
            foreach ($notifications as $notification) {
                $notificationTypeDB = NotificationType::where('account_id', '=', $id)->where('id', '=', $notification->notification_type)->first();
                $notificationType = $notificationTypeDB->getDataObject();

                $clientDB = Client::where('account_id', '=', $id)->where('id', '=', $client->client_id)->first();
                $client = $clientDB->getDataObject();

                $carDB = Car::where('account_id', '=', $id)->where('id', '=', $car->car_id)->first();
                $car = $carDB->getDataObject();

                $collectResponse = [
                    'id' => $car->id,
                    'account_id' => $car->account_id,
                    'date' => $car->date,
                    'notification_type' => $car->notification_type,
                    'notification_legend' => $notificationType,
                    'client_id' => $car->client_id,
                    'client' => $client,
                    'event_date' => $car->event_date,
                    'car_id' => $car->car_id ?? NULL,
                    'car' => $car ?? NULL,
                ];

                $notificationsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Notificaciones encontradas', $notificationsResponse);

            return response()->json($request, 200);

        } else {
            $request = APIHelpers::createAPIResponse(false, 409, 'No se encontraron notificaciones', 'No se encontraron notificaciones');

            return response()->json($request, 409);
        }

        return $request;
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $clients = Client::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();
        $cars = Car::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();





    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show(Notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit(Notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        //
    }
}
