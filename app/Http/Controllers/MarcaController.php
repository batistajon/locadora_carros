<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{   
    public function __construct(Marca $marca)
    {
        $this->marca = $marca;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {   
        $marcas = [];

        if($request->has('atributos_modelos')) {
            $atributos_modelos = $request->get('atributos_modelos');
            $marcas = $this->marca->with('modelos:id,'. $atributos_modelos);
        } else {
            $marcas = $this->marca->with('modelos');
        }

        if($request->has('filtro')) {
            $filtros = explode(';', $request->filtro);

            foreach($filtros as $key => $condicao) {
                $c = explode(':', $condicao);
                $marcas = $marcas->where($c[0], $c[1], $c[2]);
            }
        }

        if($request->has('atributos')) {
            $atributos = $request->get('atributos'); 
            $marcas = $marcas->selectRaw($atributos)->get();   
            //dd($request->get('atributos'));
        } else {
            $marcas = $marcas->get();
        }

        return response()->json($marcas, 200);
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
            $this->marca->rules(),
            $this->marca->feedback()
        );

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome' => $request->get('nome'),
            'imagem' => $imagem_urn
        ]);

        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $marca = $this->marca->with('modelos')->find($id);

        if ($marca === null) {
            return response()->json(['erro' => 'Resource is null'], 404);
        }

        return response()->json($marca, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json(['erro' => 'Resource is null'], 404);
        }

        if ($request->isMethod('PATCH')) {

            $regrasDinamicas = [];

            foreach ($marca->rules() as $input => $regra) {
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }
            }

            $request->validate(
                $regrasDinamicas,
                $marca->feedback()
            );

        } else {

            $request->validate(
                $marca->rules(),
                $marca->feedback()
            );
        }

        if($request->file('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;
        $marca->save();

        /* $marca->update([
            'nome' => $request->get('nome'),
            'imagem' => $imagem_urn
        ]); */

        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer  $marca
     * @return \Illuminate\Http\Response
     * @param \Illuminate\Http\Request  $request
     */
    public function destroy(int $marca)
    {
        $marca = $this->marca->find($marca);

        if ($marca === null) {
            return response()->json(['erro' => 'Resource is null'], 404);
        }

        Storage::disk('public')->delete($marca->imagem);
        
        $marca->delete();
        return response()->json(['message' => 'Registro removido com sucesso'], 200);
    }
}
