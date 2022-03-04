<?php

namespace App\Http\Controllers;

use App\Models\Marca;
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
    public function index(Request $request)
    {
        $marcas = [];

        if ( $request->has('atributos_modelos') ) {
            $atributos_modelos = $request->atributos_modelos;

            $marcas = $this->marca->with('modelos:id,'.$atributos_modelos);
        }else{
            $marcas = $this->marca->with('modelos');
        }

        if ( $request->has('filtro') ) {
            
            $filtros = explode(';', $request->filtro);
            
            foreach ($filtros as $key => $condicao) {

                $c = explode(':', $condicao);
                $marcas = $marcas->where($c[0], $c[1], $c[2]);
            }

        }

        if ( $request->has('atributos') ) {
            $atributos = $request->atributos;
            
            $marcas = $marcas->selectRaw($atributos)->get();

        }else {
            $marcas = $marcas->get();
        }


        // $marcas = $this->marca->with('modelos')->get();

        if (count($marcas) === 0) {
            return response()->json(['erro' => 'Nrnhuma maraca cadastrada'], 404);
        }

        return response()->json($marcas, 200);
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
        $request->validate($this->marca->rules(), $this->marca->feedback());

        $imagem     = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca = $this->marca->create([
            'nome'      => $request->nome,
            'imagem'    => $imagem_urn,
        ]);

        return response()->json($marca, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $marca = $this->marca->with('modelos')->find($id);

        if ($marca === null) {
            return response()->json(['erro' => 'Marca não encontrada'], 404);
        }

        return response()->json($marca, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marca  $marca
     * @return \Illuminate\Http\Response
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json(['erro' => 'Marca não encontrada'], 404);
        }

        if ($request->method() === 'PATCH') {
            $regrasDinamicas = [];

            // percorendo todas as regras definidas no model
            foreach ($marca->rules() as $input => $regra) {

                // coletar apenas as regras aplicáveis da requisição PATCH
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }

            }
            
            $request->validate($regrasDinamicas, $marca->feedback());
        }else {
            $request->validate($marca->rules(), $marca->feedback());
        }

        // remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        // $marca->imagem - refere-se ao caminho do arquivo.
        if ( $request->file('imagem') ) {
            Storage::disk('public')->delete($marca->imagem);
        }

        $imagem     = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens', 'public');

        $marca->fill($request->all());
        $marca->imagem = $imagem_urn;

        $marca->save();
        // $marca->update([
        //     'nome'      => $request->nome,
        //     'imagem'    => $imagem_urn,
        // ]);
        
        return response()->json($marca, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marca = $this->marca->find($id);

        if ($marca === null) {
            return response()->json(['erro' => 'Marca não encontrada'], 404);
        }

        // remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        // $marca->imagem - refere-se ao caminho do arquivo.
        Storage::disk('public')->delete($marca->imagem);
        
        $marca->delete();

        return response()->json(['msg' => 'A marca foi removida com sucesso!'], 200);
    }
}