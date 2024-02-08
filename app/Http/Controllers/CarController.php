<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Category;
use App\Models\Carcondition;
use App\Models\Carfuel;
use App\Models\Mark;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Validator, Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Decimal;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $cars = Car::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();
        $carsResponse = collect();

        if ($cars) {
            foreach ($cars as $car) {
                $categoryDB = Category::where('account_id', '=', $id)->where('id', '=', $car->category_id)->first();
                $category = $categoryDB->getDataObject();

                $markDB = Mark::where('account_id', '=', $id)->where('id', '=', $car->mark_id)->first();
                $mark = $markDB->getDataObject();

                $carConditionDB = Carcondition::where('account_id', '=', $id)->where('id', '=', $car->fuel)->first();
                $carCondition = $carConditionDB->getDataObject();

                $carFuelDB = Carfuel::where('account_id', '=', $id)->where('id', '=', $car->condition)->first();
                $carFuel = $carFuelDB->getDataObject();
                
                $clientDB = Client::where('account_id', '=', $id)->where('id', '=', $car->buyer_id)->first();
                if ($clientDB !== null) {
                    $client = $clientDB->getDataObject();
                } else {
                    $client = null;
                }
                

                $collectResponse = [
                    'id' => $car->id,
                    'account_id' => $car->account_id,
                    'patent' => $car->patent,
                    'category_id' => $car->category_id,
                    'category' => $category,
                    'mark_id' => $car->mark_id,
                    'mark' => $mark,
                    'name' => $car->name,
                    'description' => $car->description,
                    'year' => $car->year,
                    'kilometres' => $car->kilometres,
                    'condition_id' => $car->condition,
                    'condition' => $carCondition,
                    'fuel_id' => $car->fuel,
                    'fuel' => $carFuel,
                    'trunk_space' => $car->trunk_space,
                    'tank_space' => $car->tank_space,
                    'weight' => $car->weight,
                    'image' => env('IMAGE_URL') . $car->image,
                    'weight' => $car->weight,
                    'buyer_id' => $car->buyer_id,
                    'buyer' => $client,
                    'buy_date' => $car->buy_date ?? null,
                ];

                $carsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Vehículos encontrados', $carsResponse);

            return response()->json($request, 200);
        } else {
            $request = APIHelpers::createAPIResponse(false, 409, 'No se encontraron vehículos', 'No se encontraron vehículos');

            return response()->json($request, 409);
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
            $respuesta = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());

            return response()->json($respuesta, 409);
        }

        $findCar = Car::where('account_id', '=', $id)->where('patent', '=', $request->patent)->where('deleted_at', '=', NULL)->first();

        if ($findCar) {
            $response = APIHelpers::createAPIResponse(true, 409, 'El vehículo ya existe', $findCar);

            return response()->json($response, 409);
        } else {
            $form = $request->all();
            $form['uuid'] = (string) Str::uuid();

            if ($request->hasFile('image')) {
                $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                
                $request->file('image')->storeAs('images', $form['image']);
    
                $nombreGuardar = 'public/images/' . $form['image'];
                
                Storage::putFileAs('public/images/', $request->file('image'), $form['image']);
            }

            $car = new Car();

            $car->account_id = $id;
            $car->mark_id = $form['mark_id'];
            $car->patent = $form['patent'];
            $car->category_id = $form['category_id'];
            $car->name = $form['name'];
            $car->description = $form['description'];
            $car->year = $form['year'];
            $car->kilometres = $form['kilometres'];
            $car->condition = $form['condition_id'];
            $car->fuel = $form['fuel_id'];
            $car->trunk_space = $form['trunk_space'] ?? null;
            $car->tank_space = $form['tank_space'] ?? null;
            $car->weight = $form['weight'] ?? null;
            $car->image = $form['image'] ?? null;

            if ($car->save()) {
                $respuesta = APIHelpers::createAPIResponse(true, 200, 'Vehículo creado con éxito', $car);
    
                return response()->json($respuesta, 200);
            } else {
                $respuesta = APIHelpers::createAPIResponse(true, 409, 'No se pudo crear el vehículo', $validator->errors());
    
                return response()->json($respuesta, 409);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sellCar(Request $request, $id)
    {
        $rules = [
            'buy_date' => 'required',
            'car_id' => 'required',
            'buyer_id' => 'required'
        ];

        $messages = [
            'buy_date.required' => 'La fecha de venta es requerida',
            'car_id.required' => 'El vehículo a vender es requerido',
            'buyer_id.required' => 'El comprado es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 409);
        }

        $car = Car::where('account_id', '=', $id)->where('id', '=', $request->car_id)->where('deleted_at', '=', NULL)->first();
        
        if ($car) {
            $form = $request->all();

            $car->buyer_id = $form['buyer_id'];
            $car->buy_date = $form['buy_date'];

            if ($car->save()) {

                $client = Client::where('account_id', '=', $id)->where('id', '=', $car->buyer_id)->first();
                
                if ($client) {
                    $currentCars = json_decode($client->cars);
                    $currentCars[] = $car->id;
                    $client->cars = $currentCars;

                    if ($client->save()) {
                        $respuesta = APIHelpers::createAPIResponse(true, 200, 'Venta realizada con éxito', $car);
    
                        return response()->json($respuesta, 200);
                    } else {
                        $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo realizar la venta', $validator->errors());
    
                        return response()->json($response, 409);
                    }
                } else {
                    $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo realizar la venta', $validator->errors());
    
                    return response()->json($response, 409);
                }

                // $respuesta = APIHelpers::createAPIResponse(true, 200, 'Venta realizada con éxito', $car);
    
                // return response()->json($respuesta, 200);
            } else {
                $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo realizar la venta', $validator->errors());
    
                return response()->json($response, 409);
            }
            
        } else {
            $response = APIHelpers::createAPIResponse(true, 409, 'El vehículo seleccionado no existe', $validator->errors());
    
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
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function show(Car $car)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function edit(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Car $car, $id)
    {
        $rules = [
            'category_id' => 'required',
            'name' => 'required',
            'patent' => 'required',
        ];

        $messages = [
            'category_id.required' => 'La categoría es requerida',
            'name.required' => 'El nombre es requerido',
            'patent.required' => 'La patente es requerida',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 400, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 200);
        }

        $findCar = Car::where('account_id', '=', $id)->where('patent', '=', $request->patent)->where('id', '<>', $request->id)->where('deleted_at', '=', NULL)->first();

        if ($findCar) {
            $response = APIHelpers::createAPIResponse(true, 409, 'El vehículo ya existe', $findCar);

            return response()->json($response, 409);
        } else {
            $car = Car::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

            if ($car) {
                $form = $request->all();
                $form['uuid'] = (string) Str::uuid();

                if ($request->hasFile('image')) {
                    $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                    
                    $request->file('image')->storeAs('images', $form['image']);
        
                    $nombreGuardar = 'public/images/' . $form['image'];
                    
                    Storage::putFileAs('public/images/', $request->file('image'), $form['image']);

                    $car->image =  $form['image'];
                }

                $car->account_id = $id;
                $car->mark_id = $form['mark_id'];
                $car->patent = $form['patent'];
                $car->category_id = $form['category_id'];
                $car->name = $form['name'];
                $car->description = $form['description'];
                $car->year = $form['year'];
                $car->kilometres = $form['kilometres'];
                $car->condition = $form['condition_id'];
                $car->fuel = $form['fuel_id'];
                $car->trunk_space = $form['trunk_space'] ?? null;
                $car->tank_space = $form['tank_space'] ?? null;
                $car->weight = $form['weight'] ?? null;

                if ($car->save()) {
                    $respuesta = APIHelpers::createAPIResponse(true, 200, 'Vehículo actualizado con éxito', $car);
        
                    return response()->json($respuesta, 200);
                } else {
                    $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo actualizar el vehículo', $validator->errors());
        
                    return response()->json($response, 409);
                }
            } else {
                $response = APIHelpers::createAPIResponse(true, 409, $request, 'No se encontró el vehículo');
                return response()->json($response, 409);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car $car, Request $request, $id)
    {
        $car = Car::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($car) {
            if ($car->delete()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Vehículo eliminado con éxito', $car);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(false, 500, 'No se encontró el vehículo', 'No se encontró el vehículo');

            return response()->json($response, 500);
        }
    }
}
