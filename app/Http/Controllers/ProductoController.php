<?php

namespace App\Http\Controllers;
use App\Models\Producto;
use App\Models\Imagen;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $productos = Producto::all();
            //convertimos a un array
            $response = $productos->toArray();
            $i=0;
            foreach ($productos as $producto) {
                $response[$i]["categoria"] = $producto->categoria->toArray();
                $response[$i]["marca"] = $producto->marca->toArray();
                //obtener las imagenes de cada producto
                $response[$i]["imagenes"] = $producto->imagenes->toArray();
                $i++;
            }
            return response()->json($response);
        }catch(\Exception $e){
            return $e ->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.productos");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $nombre = $request->input("nombre");
            $record = Producto::where("nombre",$nombre)->first();
            if($record){
                return response()->json(["status"=> 'Conflict',
                "data"=> null,"message"=> 'Ya existe un producto con este nombre, digite otro'],409);
            }
            
            //creando la instancia de Producto y llenando el objeto
            $producto = new Producto();
            $producto->nombre = $request->nombre;
            $producto->descripcion = $request->descripcion;
            $producto->precio = $request->precio;
            $producto->stock = $request->stock;
            $producto->estado = $request->estado;
            $producto->modelo = $request->modelo;
            $producto->categoria_id = $request->categoria_id;
            $producto->marca_id = $request->marca_id;
            $result = $producto->save();
            //verificando si el producto trae imagenes
            if($request->hasFile('imagenes')){
                foreach( $request->file('imagenes') as $imagen){
                    //obtenemos el nombre original de la imagen y generando un nombre unico
                    $imageName = time() . '_'. $imagen->getClientOriginalName();
                    //subiendo la imagen a la carpeta publica
                    $imagen->move(public_path('images/productos/'), $imageName);

                    //creando instancia de Imagen para guardar en la tabla de imagenes
                    $img = new Imagen();
                    $img->nombre = $imageName;
                    $img->producto_id = $producto->id;
                    $img->save();
                }
            }

            if ($result >0) {
                $newProd = $this->show($producto->id);
                return response()->json(["status"=> 'Created',
                     "data"=> $newProd, "message"=> 'Producto Registrada con exito...!'],201);
            }else{
                return response()->json(["status"=> 'Not Acceptable',
                    "data"=> null, "message"=> 'Error al insertar el registro de Producto'],406);
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $producto = Producto::findOrFail($id);
            $response = $producto->toArray();
            $response["marca"] = $producto->marca->toArray();
            $response["categoria"] = $producto->categoria->toArray();
            $response["imagenes"] = $producto->imagenes->toArray();
            return response()->json($response);

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $record = Producto::where("nombre", $request->input("nombre"))->first();
            if($record && $record->id != $id){
                return response()->json(["status"=> 'Conflict',
                "data"=> null,"message"=> 'Ya existe un producto con este nombre, digite otro'],409);
            }
            //Obtenemos el producto por el id del parametro y asignamos nuevos valores
            //al objeto de $
            $producto = Producto::findOrFail($id);
            $producto->nombre = $request->nombre;
            $producto->descripcion = $request->descripcion;
            $producto->precio = $request->precio;
            $producto->stock = $request->stock;
            $producto->estado = $request->estado;
            $producto->modelo = $request->modelo;
            $producto->categoria_id = $request->categoria['id'];
            $producto->marca_id = $request->marca['id'];
            if($producto->update() >0){
                return response()->json(["status"=> 'Updated',
                 "data"=> $producto, "message"=> 'Producto Actualizada...!'],202);
            }else{
                return response()->json(["status"=> 'Not Acceptable',
                "data"=> null, "message"=> 'Error al actualizar el registro'],406);
            }

        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Eliminar una categoria
        try{
            //Eliminando las imagenes fisicas
            $producto = Producto::findOrFail($id);
            foreach ($producto -> imagenes as $image) {
                $imagePath = public_path() . '/images/productos/' . $image->nombre;
                unlink($imagePath);
            }
            //eliminando las imagenes  del producto de la tabla de imagenes
            $producto->imagenes()->delete();
            if($producto->delete() >0){
                return response()->json(["status"=> 'Deleted',
                 "data"=> null, "message"=> 'Producto Eliminado...!'],205);
            }else{
                return response()->json(["status"=> 'Conflict',
                "data"=> null, "message"=> 'No se puede eliminar este producto'],409);
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
