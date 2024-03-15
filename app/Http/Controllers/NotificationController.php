<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Client;
use App\Models\Car;
use Carbon\Carbon;
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
    public function create($id)
    {
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $currentDay = Carbon::now()->day;

        $clients = Client::where('account_id', '=', $id)
        ->whereMonth('birthday', $currentMonth)
        ->whereDay('birthday', $currentDay)
        ->orderBy('created_at', 'asc')
        ->get();

        $birthdayNotifications = Notification::where('account_id', '=', $id)->where('notification_type', '=', 1)->orderBy('created_at', 'asc');

        foreach ($clients as $client) {
            $birthdayNotificationExist = $birthdayNotifications->where('client_id', '=', $client->id)->whereYear('created_at', $currentYear)->first();

            if ($birthdayNotificationExist === NULL) {
                $notification = new Notification();

                $notification->account_id = $id;
                $notification->date = $client->birthday;
                $notification->notification_type = 1;
                $notification->client_id = $client->id;

                $notification->save();
            }
        }

        
        $dateTo14 = Carbon::now()->subDays(14)->toDateString();
        
        $cars = Car::where('account_id', $id)
        ->whereRaw("DATE(buy_date) <= '$dateTo14'")
        ->where('buyer_id', '<>', NULL)
        ->orderBy('created_at', 'asc')
        ->get();   


        $carsNotifications = Notification::where('account_id', '=', $id)->where('notification_type', '=', 2)->orderBy('created_at', 'asc')->get();

        if ($cars !== NULL) {
            foreach ($cars as $car) {
                $carsNotificationExist = $carsNotifications->where('client_id', '=', $car->buyer_id)->where('car_id', '=', $car->id)->first();
    
                if ($carsNotificationExist === NULL) {
                    $notification = new Notification();
    
                    $notification->account_id = $id;
                    $notification->date = $car->buy_date;
                    $notification->notification_type = 2;
                    $notification->client_id = $car->buyer_id;
                    $notification->car_id = $car->id;
    
                    $notification->save();
                }
            }
        }

        $notifications = Notification::where('account_id', $id)->orderBy('created_at', 'asc')->get();
        $notificationsResponse = collect();

        if ($notifications) {
            foreach ($notifications as $notification) {
                $clientDB = Client::where('account_id', '=', $id)->where('id', '=', $notification->client_id)->first();
                if ($clientDB !== NULL) {
                    $client = $clientDB->getDataObject();
                } else {
                    $client = NULL;
                }

                $carDB = Car::where('account_id', '=', $id)->where('id', '=', $notification->car_id)->first();
                if ($carDB !== NULL) {
                    $car = $carDB->getDataObject();
                } else {
                    $car = NULL;
                }
                

                $notificationTypeDB = NotificationType::where('account_id', '=', $id)->where('id', '=', $notification->notification_type)->first();
                $notificationType = $notificationTypeDB->getDataObject();

                $collectResponse = [
                    'id' => $notification->id,
                    'account_id' => $notification->account_id,
                    'date' => $notification->date,
                    'notification_type' => $notification->notification_type,
                    'notification' => $notificationType,
                    'client_id' => $notification->client_id,
                    'client' => $client,
                    'car_id' => $notification->car_id,
                    'car' => $car,
                    'read' => $notification->read,
                    'createad_at' => $notification->created_at
                ];

                $notificationsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Notificaciones encontradas', $notificationsResponse);
            $status_code = 200;
            
        } else {
            $request = APIHelpers::createAPIResponse(false, 200, 'No se encontraron notificaciones', 'No se encontraron notificaciones');
            $status_code = 409;
        }
        
        return response()->json($request, $status_code);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function readNotification(Request $request, $id)
    {
        $rules = [
            'arrayNotifications' => 'required'
        ];

        $messages = [
            'arrayNotifications.required' => 'Las notificaciones son requeridas'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 409);
        }

        $notifications = Notification::where('account_id', '=', $id)->where('deleted_at', '=', NULL)->get();

        $form = $request->all();

        if ($notifications) {
            foreach ($notifications as $notification) {
                foreach (json_decode($form['arrayNotifications']) as $notificationToRead) {
                    if ($notification->id == $notificationToRead) {
                        $notification->read = 1;
                        $notification->save();
                    }
                }
            }

            $response = APIHelpers::createAPIResponse(true, 200, 'Notificaciones leídas con éxito', 'Notificaciones leídas con éxito');
    
            return response()->json($response, 200);
        } else {
            $response = APIHelpers::createAPIResponse(true, 409, 'No existen notificaciones', $validator->errors());
    
            return response()->json($response, 409);
        }
        
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
