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
// use Validator, Auth;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $cars = $this->getAllCarsByAccount($id);
        $carsResponse = collect();
        $response = NULL;
        $code = NULL;

        if ($cars) {
            foreach ($cars as $car) {
                $categoryDB = $this->getCategoryByCar($id, $car->category_id);
                $category = $categoryDB->getDataObject();

                $markDB = $this->getMarkByCar($id, $car->mark_id);
                $mark = $markDB->getDataObject();

                $carConditionDB = $this->getCarConditionByCar($id, $car->fuel);
                $carCondition = $carConditionDB->getDataObject();

                $carFuelDB = $this->getCarFuelByCar($id, $car->condition);
                $carFuel = $carFuelDB->getDataObject();
                
                $clientDB = $this->getClientByCar($id, $car->buyer_id);
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
                    'description' => $car->description ?? NULL,
                    'year' => $car->year,
                    'kilometres' => $car->kilometres,
                    'condition_id' => $car->condition,
                    'condition' => $carCondition,
                    'fuel_id' => $car->fuel,
                    'fuel' => $carFuel,
                    'trunk_space' => $car->trunk_space ?? NULL,
                    'tank_space' => $car->tank_space ?? NULL,
                    'weight' => $car->weight,
                    'image' => env('IMAGE_URL') . $car->image,
                    'weight' => $car->weight,
                    'buyer_id' => $car->buyer_id,
                    'buyer' => $client,
                    'buy_date' => $car->buy_date ?? NULL,
                    'monthly_fee_paid' => $car->monthly_fee_paid,
                    'expiration_day' => $car->expiration_day,
                ];

                $carsResponse->push($collectResponse);
            }

            $response = APIHelpers::createAPIResponse(true, 200, 'Vehículos encontrados', $carsResponse);
            $code = 200;
        } else {
            $response = APIHelpers::createAPIResponse(false, 409, 'No se encontraron vehículos', 'No se encontraron vehículos');
            $code = 409;
        }

        return response()->json($response, $code);
    }

    // Get cars by account
    protected function getAllCarsByAccount($id) {
        return Car::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();
    }

    // Get category by account and car
    protected function getCategoryByCar($id, $category_id) {
        return Category::where('account_id', '=', $id)->where('id', '=', $category_id)->first();
    }

    // Get mark by account and car
    protected function getMarkByCar($id, $mark_id) {
        return Mark::where('account_id', '=', $id)->where('id', '=', $mark_id)->first();
    }

    // Get car condition by account and car
    protected function getCarConditionByCar($id, $fuel) {
        return Carcondition::where('account_id', '=', $id)->where('id', '=', $fuel)->first();
    }

    // Get car fuel by account and car
    protected function getCarFuelByCar($id, $condition) {
        return Carfuel::where('account_id', '=', $id)->where('id', '=', $condition)->first();
    }

    // Get car fuel by account and car
    protected function getClientByCar($id, $buyer_id) {
        return Client::where('account_id', '=', $id)->where('id', '=', $buyer_id)->first();
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
            $response = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());
            $code = 409;

            return response()->json($response, $code);
        }

        $response = NULL;
        $code = NULL;
        $findCar = $this->getCarByPatent($id, $request->patent);

        if ($findCar) {
            $response = APIHelpers::createAPIResponse(true, 409, 'El vehículo ya existe', $findCar);
            $code = 409;
        } else {
            $form = $request->all();

            if ($request->hasFile('image')) {
                $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                
                $request->file('image')->storeAs('images', $form['image']);
    
                Storage::putFileAs('public/images/', $request->file('image'), $form['image']);
            }

            $car = new Car();

            $car->account_id = $id;
            $car->mark_id = $form['mark_id'];
            $car->patent = $form['patent'];
            $car->category_id = $form['category_id'];
            $car->name = $form['name'];
            $car->description = $form['description'] ?? NULL;
            $car->year = $form['year'];
            $car->kilometres = $form['kilometres'];
            $car->condition = $form['condition_id'];
            $car->fuel = $form['fuel_id'];
            $car->trunk_space = $form['trunk_space'] ?? NULL;
            $car->tank_space = $form['tank_space'] ?? NULL;
            $car->weight = $form['weight'] ?? NULL;
            $car->image = $form['image'] ?? NULL;

            if ($car->save()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Vehículo creado con éxito', $car);
                $code = 200;
            } else {
                $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo crear el vehículo', $validator->errors());
                $code = 409;
            }
        }

        return response()->json($response, $code);
    }

    protected function getCarByPatent($id, $patent) {
        return Car::where('account_id', '=', $id)->where('patent', '=', $patent)->where('deleted_at', '=', NULL)->first();
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
            'buyer_id' => 'required',
            'expiration_day' => 'required'
        ];

        $messages = [
            'buy_date.required' => 'La fecha de venta es requerida',
            'car_id.required' => 'El vehículo a vender es requerido',
            'buyer_id.required' => 'El comprador es requerido',
            'expiration_day.required' => 'El día de vencimiento de la cuota',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 409);
        }

        $car = $this->getCarByID($id, $request->car_id);

        if ($car) {
            $car->buyer_id = $request->buyer_id;
            $car->buy_date = $request->buy_date;
            $car->expiration_day = $request->expiration_day;

            if ($car->save()) {
                $client = $this->getClientByBuyerID($id, $car->buyer_id);
                
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
            } else {
                $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo realizar la venta', $validator->errors());
                return response()->json($response, 409);
            }
            
        } else {
            $response = APIHelpers::createAPIResponse(true, 409, 'El vehículo seleccionado no existe', $validator->errors());
            return response()->json($response, 409);
        }
        
    }

    protected function getCarByID($id, $car_id) {
        return Car::where('account_id', '=', $id)->where('id', '=', $car_id)->where('deleted_at', '=', NULL)->first();
    }

    protected function getClientByBuyerID($id, $buyer_id) {
        return Client::where('account_id', '=', $id)->where('id', '=', $buyer_id)->first();
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
                $car->description = $form['description'] ?? NULL;
                $car->year = $form['year'];
                $car->kilometres = $form['kilometres'];
                $car->condition = $form['condition_id'];
                $car->fuel = $form['fuel_id'];
                $car->trunk_space = $form['trunk_space'] ?? NULL;
                $car->tank_space = $form['tank_space'] ?? NULL;
                $car->weight = $form['weight'] ?? NULL;

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


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function monthlyFees($id)
    {
        $cars = Car::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();
        $overdueMonthlyInstallments = collect();

        $today = Carbon::today();

        if ($today->day == 1) {
            foreach ($cars as $car) {
                $car->monthly_fee_paid = 0;
                $car->save();
            }
        }

        if ($cars) {
            foreach ($cars as $car) {
                // <
                if ($car->monthly_fee_paid == 0 && $car->expiration_day <= $today->day && $car->buyer_id != NULL) {
                    $clientDB = Client::where('account_id', '=', $id)->where('id', '=', $car->buyer_id)->first();
                    if ($clientDB !== null) {
                        $client = $clientDB->getDataObject();
                    } else {
                        $client = null;
                    }

                    $markDB = Mark::where('account_id', '=', $id)->where('id', '=', $car->mark_id)->first();
                    $mark = $markDB->getDataObject();

                    $collectResponse = [
                        'id' => $car->id,
                        'account_id' => $car->account_id,
                        'patent' => $car->patent,
                        'mark_id' => $car->mark_id,
                        'mark' => $mark,
                        'name' => $car->name,
                        'buyer_id' => $car->buyer_id,
                        'client' => $client,
                        'buy_date' => $car->buy_date ?? NULL,
                        'expiration_day' => $car->expiration_day,
                        'monthly_fee_paid' => $car->monthly_fee_paid,
                    ];
    
                    $overdueMonthlyInstallments->push($collectResponse);
                }
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Vehículos encontrados', $overdueMonthlyInstallments);

            return response()->json($request, 200);
        } else {
            $request = APIHelpers::createAPIResponse(false, 409, 'No se encontraron vehículos', 'No se encontraron vehículos');

            return response()->json($request, 409);
        }
        

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function collectFee(Request $request, $id)
    {
        $car = Car::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($car) {
            $car->monthly_fee_paid = 1;

            if ($car->save()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Cuota cobrada con éxito', $car);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(false, 500, 'No se encontró el vehículo', 'No se encontró el vehículo');

            return response()->json($response, 500);
        }
    }

}
