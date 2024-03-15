<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Validator, Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Decimal;

class MarkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $marks = Mark::where('account_id', '=', $id)->get();
        $marksResponse = collect();


        if ($marks) {
            
            foreach ($marks as $mark) {
                $collectResponse = [
                    'id' => $mark->id,
                    'account_id' => $mark->account_id,
                    'name' => $mark->name,
                    'image' => env('IMAGE_URL') . $mark->image,
                ];

                $marksResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Marcas encontradas', $marksResponse);
        } else {
            $request = APIHelpers::createAPIResponse(false, 500, 'No se encontraron marcas', 'No se encontraron marcas');
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

        $findMark = Mark::where('account_id', '=', $id)->where('name', '=', $request->name)->where('deleted_at', '=', NULL)->first();

        if ($findMark) {
            $response = APIHelpers::createAPIResponse(true, 409, 'La marca ya existe', $findMark);

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

            $mark = new Mark();

            $mark->account_id = $id;
            $mark->name = $form['name'];
            $mark->image = $form['image'] ?? null;

            if ($mark->save()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Marca creada con éxito', $mark);

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
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function show(Mark $mark)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function edit(Mark $mark)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mark $mark, $id)
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

        $findMark = Mark::where('account_id', '=', $id)->where('name', '=', $request->name)->where('id', '<>', $request->id)->where('deleted_at', '=', NULL)->first();

        if ($findMark) {
            $response = APIHelpers::createAPIResponse(true, 409, 'La marca ya existe', $findMark);

            return response()->json($response, 409);
        } else {
            $mark = Mark::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

            if ($mark) {

                $form = $request->all();
                $form['uuid'] = (string) Str::uuid();

                if ($request->hasFile('image')) {
                    $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                    
                    $request->file('image')->storeAs('images', $form['image']);
        
                    $nombreGuardar = 'public/images/' . $form['image'];
                    
                    Storage::putFileAs('public/images/', $request->file('image'), $form['image']);

                    $mark->image =  $form['image'];
                }

                $mark->name = $form['name'];

                if ($mark->save()) {
                    $response = APIHelpers::createAPIResponse(true, 200, 'Marca modificada con éxito', $mark);
                    $code = 200;
                }
            } else {
                $response = APIHelpers::createAPIResponse(true, 500, 'No se encontró la marca', 'No se encontró la marca');
                $code = 500;
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mark  $mark
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mark $mark, Request $request, $id)
    {
        $mark = Mark::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($mark) {
            if ($mark->delete()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Marca eliminada con éxito', $mark);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(true, 500, 'No se encontró la marca', 'No se encontró la marca');

            return response()->json($response, 500);
        }
    }
}
