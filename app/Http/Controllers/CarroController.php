<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use App\Repositories\CarroRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarroController extends Controller
{
    public function __construct(Carro $carro)
    {
        $this->carro = $carro;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $carroRepository = new CarroRepository($this->carro);

        if($request->has('atributos_modelo')) {
            $atributos_modelos = 'modelo:id,'. $request->get('atributos_modelo');
            $carroRepository->selectAtributosRegistrosRelacionados($atributos_modelos);
        } else {
            $carroRepository->selectAtributosRegistrosRelacionados('modelo');
        }

        if($request->has('filtro')) {
            $carroRepository->filtro($request->filtro);
        }

        if($request->has('atributos')) {
            $atributos = $request->get('atributos'); 
            $carroRepository->selectAtributos($atributos);
        }

        return response()->json($carroRepository->getResultado(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            $this->carro->rules()
        );

        $carro = $this->carro->create([
            'modelo_id' => $request->modelo_id,
            'placa' => $request->placa,
            'disponivel' => $request->disponivel,
            'km' => $request->km
        ]);

        return response()->json($carro, 201);
    }

    /**
     * Display the specified resource.
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $carro = $this->carro->with('modelo')->find($id);

        if ($carro === null) {
            return response()->json(['erro' => 'Resource is null'], 404);
        }

        return response()->json($carro, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Carro  $carro
     * @return \Illuminate\Http\Response
     */
    public function edit(Carro $carro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $carro = $this->carro->find($id);

        if ($carro === null) {
            return response()->json(['erro' => 'Resource is null'], 404);
        }

        if ($request->isMethod('PATCH')) {

            $regrasDinamicas = [];

            foreach ($carro->rules() as $input => $regra) {
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate($regrasDinamicas);

        } else {

            $request->validate($carro->rules());
        }

        $carro->fill($request->all());
        $carro->save();

        return response()->json($carro, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $carro = $this->carro->find($id);

        if ($carro === null) {
            return response()->json(['erro' => 'Resource is null'], 404);
        }

        $carro->delete();

        return response()->json(['message' => 'Registro removido com sucesso'], 200);
    }
}
