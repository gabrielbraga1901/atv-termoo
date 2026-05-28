<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TermooController extends Controller
{
    private $maxTentativas = 6;

    public function novoJogo()
    {
        $config = include config_path('palavras.php');

        $palavras = $config['palavras'];

        $palavraAleatoria = $palavras[array_rand($palavras)];

        $idJogo = Str::uuid()->toString();

        Cache::put($idJogo, [
            'palavra' => $palavraAleatoria,
            'tentativas' => 0
        ], now()->addHours(2));

        return response()->json([
            'idJogo' => $idJogo,
            'tamanhoPalavra' => 5,
            'tentativasMaximas' => $this->maxTentativas
        ]);
    }

    public function testarPalavra(Request $request)
    {
        $request->validate([
            'idJogo' => 'required',
            'palavra' => 'required|size:5'
        ]);

        $jogo = Cache::get($request->idJogo);

        if (!$jogo) {
            return response()->json([
                'erro' => 'Jogo não encontrado'
            ], 404);
        }

        $tentativa = strtolower($request->palavra);
        $correta = strtolower($jogo['palavra']);

        $resultado = [];

        for ($i = 0; $i < 5; $i++) {

            if ($tentativa[$i] == $correta[$i]) {
                $status = 'certa';
            } elseif (str_contains($correta, $tentativa[$i])) {
                $status = 'existe';
            } else {
                $status = 'errada';
            }

            $resultado[] = [
                'letra' => $tentativa[$i],
                'status' => $status
            ];
        }

        $jogo['tentativas']++;

        Cache::put($request->idJogo, $jogo, now()->addHours(2));

        return response()->json([
            'resultado' => $resultado,
            'tentativas' => $jogo['tentativas'],
            'venceu' => $tentativa == $correta
        ]);
    }
}