<?php

namespace App\Http\Controllers;
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleOrden;

class OrdenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $ordenes = Orden::all();
            $response = $ordenes->toArray();
            $i=0;
            foreach($ordenes as $orden){
                $response[$i]["cliente"] = $orden->user->toArray();
                //Obtenemos el detalle de la orden
                $detalle = $orden->detalleOrdenes->toArray();
                //recorremos el detalle de la orden
                
                $f = 0;
                foreach($orden->detalleOrdenes as $item){
                    
                    // $detalle[$f]['producto'] = $item->productos->toArray();
                    /*
                    $detalle[$f]["producto"] = DB::table('productos')
                    ->where("productos.id","=",$item->producto_id)
                    ->select('productos.*')
                    ->get()->toArray();*/
                    /*
                    $detalle[$f]['producto']['marca'] = $item->producto->marca->toArray();
                    $detalle[$f]['producto']['categoria'] = $item->producto->categoria->toArray();
                    */
                    $productos = DB::table('marcas')
                    ->join('productos', 'marcas.id', '=', 'productos.marca_id')
                    ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                    ->where("productos.id","=",$item->producto_id)
                    ->select('productos.*', 'marcas.nombre as nombre_marca', 'categorias.nombre as nombre_categoria')
                    ->get()->toArray();
                    $detalle[$f]['producto'] = $productos;
                    $f++;
                }
                
                $response[$i]['detalleOrdenes'] = $detalle;
                $i++;
            }
            return response()->json($response);
        }catch(\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.ordenes');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $errors = 0;
            DB::beginTransaction();
            //Creamos una intancia de orden
            $orden = New Orden();
            $orden->correlativo = $this->getCorrelativo();
            $orden->fecha = $request->fecha;
            $orden->estado = $request->estado;
            $orden->monto = $request->monto;
            $orden->user_id = $request->user['id'];
            if ($orden->save()<=0) {
                $errors++;
            }
            //obtener el detalle de la orden
            $detalle = $request->detalleOrden;
            foreach ($detalle as $key => $det) {
                //creamos un objeto de DetalleOrden
                $detalleOrden = new DetalleOrden();
                $detalleOrden->cantidad = $det['cantidad'];
                $detalleOrden->precio = $det['precio'];
                $detalleOrden->producto_id = $det['producto']['id'];
                $detalleOrden->orden_id = $orden->id;
                if($detalleOrden->save() <= 0){
                    $errors++;
                }

            }
            if($errors == 0){
                DB::commit();
                return response()->json(['status' => 'Created','data'=>$orden,
                'message' => 'Su orden ha sido registrada...!'],201);
            }
            else{
                DB::rollBack();
                return response()->json(['status'=> 'Not Acceptable','data'=>null,
                 'message'=>'Error al guardar la orden, intente de nuevo...!'], 406);
            }

        }catch(\Exception $e){
            DB::rollBack();
            return $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $orden = Orden::find($id);
            $response = $orden->toArray();
            $response["cliente"] = $orden->user->toArray();
            //Obtenemos el detalle de la orden
            $detalle = $orden->detalleOrdenes->toArray();
            //recorremos el detalle de la orden
            $i = 0;
            foreach($orden->detalleOrdenes as $item){
                $detalle[$i]['producto'] = $item->producto->toArray();
                $detalle[$i]['producto']['marca'] = $item->producto->marca->toArray();
                $detalle[$i]['producto']['categoria'] = $item->producto->categoria->toArray();
                $i++;
            }
            $response['detalleOrdenes'] = $detalle;
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
        try {
            $orden = Orden::find($id);
            if ($request->estado == 'D') {
                $fechaActual = date("Y-m-d");
                $orden->fecha_despacho = $fechaActual;
            }
            $orden->estado = $request->estado;
            if ($orden->update()>0) {
                return response()->json(['status' => 'Accepted', 'data'=>$orden,
                'message' => 'El estado de la orden ha sido cambiado'],202);
            }else{
                return response()->json(['status' => 'Not Accepted', 'data'=>null,
                'message' => 'Error al cambiar el estado de la orden'],406);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function getCorrelativo(){
        $result = DB::select("SELECT CONCAT(TRIM(YEAR(CURDATE())),LPAD(TRIM(MONTH(CURDATE())),2,0),LPAD(IFNULL(MAX(TRIM(SUBSTRING(correlativo,7,4))),0)+1,4,0)) as correlativo FROM ordenes WHERE SUBSTRING(correlativo,1,6) = CONCAT(TRIM(YEAR(CURDATE())),LPAD(TRIM(MONTH(CURDATE())),2,0))");
        return $result[0] -> correlativo;
    }
}
