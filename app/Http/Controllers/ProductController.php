<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\APIHelpers;
use Validator, Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Decimal;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $products = Product::where('account_id', '=', $id)->orderBy('created_at', 'asc')->get();
        $productsResponse = collect();

        if ($products) {
            foreach ($products as $product) {
                $categoryDB = Category::where('account_id', '=', $id)->where('id', '=', $product->category_id)->first();
                $category = $categoryDB->getDataObject();

                $collectResponse = [
                    'id' => $product->id,
                    'account_id' => $product->account_id,
                    'category_id' => $product->category_id,
                    'category' => $category,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'uuid' => $product->image,
                    'image' => env('IMAGE_URL') . $product->image,
                ];

                $productsResponse->push($collectResponse);
            }

            $request = APIHelpers::createAPIResponse(false, 200, 'Productos encontrados', $productsResponse);

            return response()->json($request, 200);
        } else {
            $request = APIHelpers::createAPIResponse(false, 409, 'No se encontraron productos', 'No se encontraron productos');

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
            'description' => 'required',
            'price' => 'required',
            'stock' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es requerido',
            'description.required' => 'La descripción es requerida',
            'price.required' => 'El precio es requerido',
            'stock.required' => 'El stock es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $respuesta = APIHelpers::createAPIResponse(true, 409, 'Se ha producido un error', $validator->errors());

            return response()->json($respuesta, 409);
        }

        $findProduct = Product::where('account_id', '=', $id)->where('name', '=', $request->name)->where('deleted_at', '=', NULL)->first();

        if ($findProduct) {
            $response = APIHelpers::createAPIResponse(true, 409, 'El producto ya existe', $findProduct);

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

            $product = new Product();

            $product->account_id =  $id;
            $product->category_id =  $form['category_id'];
            $product->name =  $form['name'];
            $product->description =  $form['description'];
            $product->price =  $form['price'];
            $product->uuid =  $form['uuid'];
            $product->stock =  $form['stock'];
            $product->image =  $form['image'];

            if ($product->save()) {
                $respuesta = APIHelpers::createAPIResponse(true, 200, 'Producto creado con éxito', $product);
    
                return response()->json($respuesta, 200);
            } else {
                $respuesta = APIHelpers::createAPIResponse(true, 409, 'No se pudo crear el producto', $validator->errors());
    
                return response()->json($respuesta, 409);
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
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'category_id' => 'required',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'stock' => 'required',
        ];

        $messages = [
            'category_id.required' => 'La categoría es requerida',
            'name.required' => 'El nombre es requerido',
            'description.required' => 'La descripción es requerida',
            'price.required' => 'El precio es requerido',
            'stock.required' => 'El stock es requerido',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $response = APIHelpers::createAPIResponse(true, 400, 'Se ha producido un error', $validator->errors());

            return response()->json($response, 200);
        }

        $findProduct = Product::where('account_id', '=', $id)->where('name', '=', $request->name)->where('id', '<>', $request->id)->where('deleted_at', '=', NULL)->first();

        if ($findProduct) {
            $response = APIHelpers::createAPIResponse(true, 409, 'El producto ya existe', $findProduct);

            return response()->json($response, 409);
        } else {
            $product = Product::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

            if ($product) {
                $form = $request->all();
                $form['uuid'] = (string) Str::uuid();

                if ($request->hasFile('image')) {
                    $form['image'] = time() . '_' . $request->file('image')->getClientOriginalName();
                    
                    $request->file('image')->storeAs('images', $form['image']);
        
                    $nombreGuardar = 'public/images/' . $form['image'];
                    
                    Storage::putFileAs('public/images/', $request->file('image'), $form['image']);

                    $product->image =  $form['image'];
                }

                // $respuesta = APIHelpers::createAPIResponse(false, 200, 'Producto actualizado con éxito', $form);
                // return response()->json($respuesta, 200);
        
                
                $product->account_id =  $id;
                $product->category_id =  $form['category_id'];
                $product->name =  $form['name'];
                $product->description =  $form['description'];
                $product->price =  $form['price'];
                $product->uuid =  $form['uuid'];
                $product->stock =  $form['stock'];

                if ($product->save()) {
                    $respuesta = APIHelpers::createAPIResponse(true, 200, 'Producto actualizado con éxito', $product);
        
                    return response()->json($respuesta, 200);
                } else {
                    $response = APIHelpers::createAPIResponse(true, 409, 'No se pudo actualizar el producto', $validator->errors());
        
                    return response()->json($response, 409);
                }
            } else {
                $response = APIHelpers::createAPIResponse(true, 409, $request, 'No se encontró el producto');
                return response()->json($response, 409);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, $id, Request $request)
    {
        $product = Product::where('account_id', '=', $id)->where('id', '=', $request->id)->first();

        if ($product) {
            if ($product->delete()) {
                $response = APIHelpers::createAPIResponse(true, 200, 'Producto eliminado con éxito', $product);

                return response()->json($response, 200);
            }
        } else {
            $response = APIHelpers::createAPIResponse(false, 500, 'No se encontró el producto', 'No se encontró el producto');

            return response()->json($response, 500);
        }
    }
}
