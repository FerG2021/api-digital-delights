<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Validator, Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Decimal;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $categories = Category::where('account_id', '=', $id)->get();
        $categoriesResponse = collect();


        if ($categories) {
            
            foreach ($categories as $category) {
                $collectResponse = [
                    'id' => $category->id,
                    'account_id' => $category->account_id,
                    'name' => $category->name,
                    // 'uuid' => $category->image,
                    'image' => env('IMAGE_URL') . $category->image,
                ];

                $categoriesResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Categorías encontradas', $categoriesResponse);
        } else {
            $request = APIHelpers::createAPIResponse(false, 500, 'No se encontraron categorías', 'No se encontraron categorías');
        }

        return $request;
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll($account_name)
    {
        $account = Account::where('name', '=', $account_name)->first();

        $categories = Category::where('account_id', '=', $account->id)->get();
        $categoriesResponse = collect();


        if ($categories) {
            
            foreach ($categories as $category) {
                $collectResponse = [
                    'id' => $category->id,
                    'account_id' => $category->account_id,
                    'name' => $category->name,
                    // 'uuid' => $category->image,
                    'image' => env('IMAGE_URL') . $category->image,
                ];

                $categoriesResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Categorías encontradas', $categoriesResponse);
        } else {
            $request = APIHelpers::createAPIResponse(false, 500, 'No se encontraron categorías', 'No se encontraron categorías');
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

        $findCategory = Category::where('account_id', '=', $id)->where('name', '=', $request->name)->where('deleted_at', '=', NULL)->first();

        if ($findCategory) {
            $response = APIHelpers::createAPIResponse(true, 409, 'La categoría ya existe', $findCategory);

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

            $category = new Category();

            $category->account_id = $id;
            $category->name  = $form['name'];
            $category->uuid =  $form['uuid'];
            $category->image =  $form['image'];

            if ($category->save()) {
                $response = APIHelpers::createAPIResponse(false, 200, 'Categoría creada con éxito', $category);

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
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category, $id)
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

        $findCategory = Category::where('account_id', '=', $id)->where('name', '=', $request->name)->where('id', '<>', $request->id)->where('deleted_at', '=', NULL)->first();

        if ($findCategory) {
            $response = APIHelpers::createAPIResponse(true, 409, 'La categoría ya existe', $findCategory);

            return response()->json($response, 409);
        } else {
            $category = Category::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

            if ($category) {

                $form = $request->all();
                $form['uuid'] = (string) Str::uuid();

                if ($request->hasFile('image')) {
                    $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                    
                    $request->file('image')->storeAs('images', $form['image']);
        
                    $nombreGuardar = 'public/images/' . $form['image'];
                    
                    Storage::putFileAs('public/images/', $request->file('image'), $form['image']);

                    $category->image =  $form['image'];
                }


                $category->name = $form['name'];
                $category->uuid =  $form['uuid'];


                if ($category->save()) {
                    $respuesta = APIHelpers::createAPIResponse(false, 200, 'Categoría modificada con éxito', $category);
                }
            } else {
                $respuesta = APIHelpers::createAPIResponse(false, 500, 'No se encontró la categoría', 'No se encontró la categoría');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category, $id, Request $request)
    {
        $category = Category::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($category) {
            if ($category->delete()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Categoría eliminada con éxito', $category);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(true, 500, 'No se encontró la categoría', 'No se encontró la categoría');

            return response()->json($response, 500);
        }
    }
}
