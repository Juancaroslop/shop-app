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
            //convertir a un arreglo
            $response= $productos->toArray();
            $i = 0;
            foreach ($productos as $producto) {
                $response[$i]["categoria"] = $producto->categoria ? $producto->categoria->toArray() : [];
                $response[$i]["marca"] = $producto->marca ? $producto->marca->toArray() : [];
                $response[$i]["imagenes"] = $producto->imagenes ? $producto->imagenes->toArray() : [];
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
        return view('admin.productos');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $a = [$request->nombre, $request->precio, $request->descripcion, $request->marca_id, $request->categoria_id];
        try{
            $request->validate([
              "nombre"=> "required|string|max:80",
              "descripcion"=> "required|string",
              "precio"=> "required|numeric|min:8",
              "imagenes"=> "image|mimes:jpeg,png,jpg,gif,svg|max:2048",
            ]);
              $record = Producto::where("nombre",$request->input("nombre"))->first();
              if($record){
                  return response()->json(['status' =>'Conflict','data'=>null,
                  'message'=>'Ya existe un producto con este nombre Digite otro'],409);
              }
              //creando la instancia del producto y llenando el objeto
              $producto = new Producto();
              $producto->nombre=$request->nombre;
              $producto->descripcion=$request->descripcion;
              $producto->precio=$request->precio;
              $producto->categoria_id=$request->categoria_id;
              $producto->marca_id=$request->marca_id;
              $result =$producto->save();
              //verificando si el producto trae imagenes
  
              if($request->hasfile('imagenes')){
                  foreach($request->file('imagenes') as $imagen){
                      //obtenemos el nombre original de la imagen y generamos un nombre unico
                      $imageName = time() . '_' . $imagen->getClientOriginalName();
                      //subiendo la imagen a la carpeta publica
                      $imagen->move(public_path('images/productos/'), $imageName);
  
                      //creando instanseas de imagen para guardar en la tabla imagenes
                      $img = new Imagen();
                      $img->nombre= $imageName;
                      $img->producto_id= $producto->id;
                      $img->save();
                  }
              }
              if($result>0){
                  return response()->json(['status'=> 'Created','data'=>$producto,
                  'message'=> 'Producto registrado con exito....!'],201);
              }else{
                  return response()->json(['status'=> 'Not Acceptable','data'=>null,
                  'message'=> 'Error al insertar el registro'],406);
              }
          }catch(\Exception $e){
              return $e -> getMessage();
          }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $producto = Producto::findOrfail($id);
            $response = $producto->toArray();
            $response["marca"] = $producto-> marca->toArray();
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
                return response()->json(["status"=> "Conflict","data"=>null,
                "message"=> "Ya existe una producto con este nombre,digite otra"],409);
            }
            
            //obtenemos el producto por el id del parametro y asignamos nuevos valores
            //al objeto producto
            $producto = Producto::findOrfail($id);
            $producto->nombre = $request->nombre;
            $producto->descripcion=$request->descripcion;
            $producto->precio=$request->precio;
            $producto->categoria_id=$request->categoria_id;
            $producto->marca_id=$request->marca_id;

            if($producto->update()>0){
                //return response()->json(['status'=> 'Accepted','data'=>$producto,
                //'message'=> 'Producto Actualizado...'],201);
                return response()->json(['status'=> 'Created','data'=>$producto,'message'=> 'Producto actualizado con exito....!'],201);
            }else{
                return response()->json(['status'=> 'Not Accetable','data'=>null,
                'message'=> 'Error al ctualizar el registro'],406);
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
        try{
            $producto = Producto::findOrfail($id);
            if($producto->delete()>0){
                return response()->json(['status'=> 'Delete','data'=>null,
                'message'=> 'Producto eliminado...'],205);
            }else{
                return response()->json(['status'=> 'Conflict','data'=>null,
                'message'=> 'No se puede eliminar este producto'],409);
            }
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }
}
