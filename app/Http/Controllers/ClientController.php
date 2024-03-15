<?php

namespace App\Http\Controllers;

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

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $clients = Client::where('account_id', '=', $id)->get();
        $clientsResponse = collect();


        if ($clients) {
            foreach ($clients as $client) {
                $clientCars = collect();
                $carsArray = json_decode($client->cars, true);

                if ($carsArray !== NULL) {
                    foreach ($carsArray as $clientCar) {
                        $car = Car::where('id', '=', $clientCar)->first();
                        $clientCars->push($car->patent);
                    }
                }

                $collectResponse = [
                    'id' => $client->id,
                    'account_id' => $client->account_id,
                    'dni' => $client->dni,
                    'name' => $client->name,
                    'lastname' => $client->lastname,
                    'fullname' => $client->lastname . ', ' . $client->name,
                    'birthday' => $client->birthday,
                    'address' => $client->address,
                    'phone_number' => $client->phone_number,
                    'cars' => $client->cars,
                    'carsPatent' => $clientCars ?? NULL,
                ];

                $clientsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Clientes encontrados', $clientsResponse);
        } else {
            $request = APIHelpers::createAPIResponse(false, 500, 'No se encontraron clientes', 'No se encontraron clientes');
        }

        return $request;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 400, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 200);
        }

        $findClient = Client::where('account_id', '=', $id)->where('dni', '=', $request->dni)->where('deleted_at', '=', NULL)->first();

        if ($findClient) {
            $response = APIHelpers::createAPIResponse(true, 409, 'El cliente ya existe', $findClient);

            return response()->json($response, 409);
        } else {
            $form = $request->all();
           
            $client = new Client();
            $client->account_id = $id;
            $client->dni = $form['dni'];
            $client->name = $form['name'];
            $client->lastname = $form['lastname'];
            $client->birthday = $form['birthday'];
            $client->address = $form['address'];
            $client->phone_number = $form['phone_number'];

            if ($client->save()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Cliente creado con éxito', $client);

                return response()->json($response, 200);
            }
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client, $id)
    {
        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $status_code = 400;
            $response = APIHelpers::createAPIResponse(true, 400, 'Se ha producido un error', $validator->errors());

            return response()->json($response, $status_code);
        }

        $findClient = Client::where('account_id', '=', $id)->where('name', '=', $request->name)->where('id', '<>', $request->id)->where('deleted_at', '=', NULL)->first();

        if ($findClient) {
            $status_code = 409;
            $response = APIHelpers::createAPIResponse(true, 409, 'El cliente ya existe', $findClient);

        } else {
            $client = Client::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

            if ($client) {
                $form = $request->all();

                $client->name = $form['name'];
                $client->lastname = $form['lastname'];
                $client->birthday = $form['birthday'];
                $client->address = $form['address'];
                $client->phone_number = $form['phone_number'];

                if ($client->save()) {
                    $status_code = 200;
                    $response = APIHelpers::createAPIResponse(true, 200, 'Cliente modificado con éxito', $client);
                }
            } else {
                $status_code = 500;
                $response = APIHelpers::createAPIResponse(false, 500, 'No se encontró el cliente', 'No se encontró el cliente');
            }
        }

        return response()->json($response, $status_code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client, Request $request, $id)
    {
        $client = Client::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($client) {
            if ($client->delete()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Cliente eliminado con éxito', $client);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(true, 500, 'No se encontró el cliente', 'No se encontró el cliente');

            return response()->json($response, 500);
        }
    }
}
