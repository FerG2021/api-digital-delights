<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Validator, Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Decimal;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $promotions = Promotion::where('account_id', '=', $id)->get();
        $promotionsResponse = collect();


        if ($promotions) {
            
            foreach ($promotions as $promotion) {
                $collectResponse = [
                    'id' => $promotion->id,
                    'account_id' => $promotion->account_id,
                    'title' => $promotion->title,
                    'description' => $promotion->description,
                    'available' => $promotion->available,
                    'price' => $promotion->price,
                    'image' => env('IMAGE_URL') . $promotion->image,
                ];

                $promotionsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Promociones encontradas', $promotionsResponse);
        } else {
            $request = APIHelpers::createAPIResponse(false, 500, 'No se encontraron promociones', 'No se encontraron promociones');
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
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'available' => 'required',
        ];

        $messages = [
            'title.required' => 'El nombre es requerido',
            'description.required' => 'La descripción es requerida',
            'price.required' => 'El precio es requerido',
            'available.required' => 'El estado de la promoción es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $respuesta = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());

            return response()->json($respuesta, 409);
        }

        $findPromotion = Promotion::where('account_id', '=', $id)->where('title', '=', $request->title)->where('deleted_at', '=', NULL)->first();

        if ($findPromotion) {
            $response = APIHelpers::createAPIResponse(true, 409, 'La promoción ya existe', $findPromotion);

            return response()->json($response, 409);
        } else {
            $form = $request->all();
            // $form['uuid'] = (string) Str::uuid();

            if ($request->hasFile('image')) {
                $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                
                $request->file('image')->storeAs('images', $form['image']);
    
                $nombreGuardar = 'public/images/' . $form['image'];
                
                Storage::putFileAs('public/images/', $request->file('image'), $form['image']);
            }

            $promotion = new Promotion();

            $promotion->account_id =  $id;
            $promotion->title =  $form['title'];
            $promotion->description =  $form['description'];
            $promotion->price =  $form['price'];
            // $promotion->uuid =  $form['uuid'];
            $promotion->available =  $form['available'];
            $promotion->image =  $form['image'];

            if ($promotion->save()) {
                $respuesta = APIHelpers::createAPIResponse(true, 200, 'Promoción creada con éxito', $promotion);
    
                return response()->json($respuesta, 200);
            } else {
                $respuesta = APIHelpers::createAPIResponse(true, 409, 'No se pudo crear la promoción', $validator->errors());
    
                return response()->json($respuesta, 409);
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll($account_name)
    {
        $account = Account::where('name', '=', $account_name)->first();

        $promotions = Promotion::where('account_id', '=', $account->id)->orderBy('created_at', 'asc')->get();
        $promotionsResponse = collect();

        if ($promotions) {
            foreach ($promotions as $promotion) {
                $collectResponse = [
                    'id' => $promotion->id,
                    'account_id' => $promotion->account_id,
                    'title' => $promotion->title,
                    'description' => $promotion->description,
                    'price' => $promotion->price,
                    'available' => $promotion->available,
                    'uuid' => $promotion->image,
                    'image' => env('IMAGE_URL') . $promotion->image,
                ];

                $promotionsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Promociones encontrados', $promotionsResponse);

            return response()->json($request, 200);
        } else {
            $request = APIHelpers::createAPIResponse(false, 409, 'No se encontraron promociones', 'No se encontraron promociones');

            return response()->json($request, 409);
        }

        return $request;
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
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required',
            'available' => 'required',
        ];

        $messages = [
            'title.required' => 'El título es requerido',
            'description.required' => 'La descripción es requerida',
            'price.required' => 'El precio es requerido',
            'available.required' => 'El estado de la promoción es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 400, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 200);
        }

        $findPromotion = Promotion::where('account_id', '=', $id)->where('title', '=', $request->title)->where('id', '<>', $request->id)->where('deleted_at', '=', NULL)->first();

        if ($findPromotion) {
            $response = APIHelpers::createAPIResponse(true, 409, 'La promoción ya existe', $findPromotion);

            return response()->json($response, 409);
        } else {
            $promotion = Promotion::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

            if ($promotion) {
                $form = $request->all();
                // $form['uuid'] = (string) Str::uuid();

                if ($request->hasFile('image')) {
                    $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                    
                    $request->file('image')->storeAs('images', $form['image']);
        
                    $nombreGuardar = 'public/images/' . $form['image'];
                    
                    Storage::putFileAs('public/images/', $request->file('image'), $form['image']);

                    $promotion->image =  $form['image'];
                }

                $promotion->account_id =  $id;
                $promotion->title =  $form['title'];
                $promotion->description =  $form['description'];
                $promotion->price =  $form['price'];
                // $promotion->uuid =  $form['uuid'];
                $promotion->available =  $form['available'];

                if ($promotion->save()) {
                    $respuesta = APIHelpers::createAPIResponse(true, 200, 'Promoción actualizada con éxito', $promotion);
        
                    return response()->json($respuesta, 200);
                } else {
                    $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo actualizar la promoción', $validator->errors());
        
                    return response()->json($response, 409);
                }
            } else {
                $response = APIHelpers::createAPIResponse(true, 409, $request, 'No se encontró la promoción');
                return response()->json($response, 409);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion, $id, Request $request)
    {
        $promotion = Promotion::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($promotion) {
            if ($promotion->delete()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Promoción eliminada con éxito', $promotion);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(false, 500, 'No se encontró la promoción', 'No se encontró la promoción');

            return response()->json($response, 500);
        }
    }
}
