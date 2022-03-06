<?php

namespace App\Http\Controllers;

use App\Models\Modelo;
use App\Repositories\ModeloRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{
    public function __construct(Modelo $modelo)
    {
        $this->modelo = $modelo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $modeloRepository = new ModeloRepository($this->modelo);

        if ( $request->has('atributos_marca') ) {
            $atributos_modelos = 'marca:id,'.$request->atributos_modelos;
            
            $modeloRepository->selectAtributosRegistrosRelecionados($atributos_modelos);
        }else{
            $modeloRepository->selectAtributosRegistrosRelecionados('marca');
        }

        if ( $request->has('filtro') ) {

            $modeloRepository->filtro($request->filtro);

        }

        if ( $request->has('atributos') ) {
            $modeloRepository->selectAtributos($request->atributos);
        }
        
        return response()->json($modeloRepository->getResultado(), 200);
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
    $request->validate($this->modelo->rules()/*, $this->modelo->feedback()*/);

        $imagem     = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');

        $modelo = $this->modelo->create([
            'marca_id'      => $request->marca_id,
            'nome'          => $request->nome,
            'imagem'        => $imagem_urn,
            'numero_portas' => $request->numero_portas,
            'lugares'       => $request->lugares,
            'air_bag'       => $request->air_bag,
            'abs'           => $request->abs,
        ]);

        return response()->json($modelo, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = $this->modelo->with('marca')->find($id);

        if ($modelo === null) {
            return response()->json(['erro' => 'Modelo não encontrada'], 404);
        }

        return response()->json($modelo, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Modelo  $modelo
     * @return \Illuminate\Http\Response
     */
    public function edit(Modelo $modelo)
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
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json(['erro' => 'Modelo não encontrada'], 404);
        }

        if ($request->method() === 'PATCH') {
            $regrasDinamicas = [];

            // percorendo todas as regras definidas no model
            foreach ($modelo->rules() as $input => $regra) {

                // coletar apenas as regras aplicáveis da requisição PATCH
                if (array_key_exists($input, $request->all())) {
                    $regrasDinamicas[$input] = $regra;
                }

            }
            
        $request->validate($regrasDinamicas, /*$modelo->feedback()*/);
        }else {
        $request->validate($modelo->rules(), /*$modelo->feedback()*/);
        }

        // remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        // $modelo->imagem - refere-se ao caminho do arquivo.
        if ( $request->file('imagem') ) {
            Storage::disk('public')->delete($modelo->imagem);
        }

        $imagem     = $request->file('imagem');
        $imagem_urn = $imagem->store('imagens/modelos', 'public');

        $modelo->fill($request->all());
        $modelo->imagem = $imagem_urn;

        $modelo->save();

        // $modelo->update([
        //     'marca_id'      => $request->marca_id,
        //     'nome'          => $request->nome,
        //     'imagem'        => $imagem_urn,
        //     'numero_portas' => $request->numero_portas,
        //     'lugares'       => $request->lugares,
        //     'air_bag'       => $request->air_bag,
        //     'abs'           => $request->abs,
        // ]);
        
        return response()->json($modelo, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $modelo = $this->modelo->find($id);

        if ($modelo === null) {
            return response()->json(['erro' => 'modelo não encontrada'], 404);
        }

        // remove o arquivo antigo caso um novo arquivo tenha sido enviado no request
        // $modelo->imagem - refere-se ao caminho do arquivo.
        Storage::disk('public')->delete($modelo->imagem);
        
        $modelo->delete();

        return response()->json(['msg' => 'O modelo foi removida com sucesso!'], 200);
    }
}
