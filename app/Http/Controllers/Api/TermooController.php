<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TermooController extends Controller
{
    private static $jogos = [];

    public function iniciarJogo()
    {
        $palavras = config('palavras.palavras');

        $palavraSecreta = mb_strtolower($palavras[array_rand($palavras)]);

        $idJogo = Str::uuid()->toString();

        self::$jogos[$idJogo] = [
            'palavra' => $palavraSecreta,
            'tentativas' => 0
        ];

        return response()->json([
            'idJogo' => $idJogo,
            'tamanhoPalavra' => 5,
            'tentativasMaximas' => 6
        ], 200);
    }

    public function validarTentativa(Request $request)
    {
        $request->validate([
            'idJogo' => 'required|string',
            'palavra' => 'required|string|size:5'
        ]);

        $idJogo = $request->idJogo;
        $palavraTentativa = mb_strtolower($request->palavra);

        if (!isset(self::$jogos[$idJogo])) {
            return response()->json([
                'erro' => 'Jogo não encontrado'
            ], 404);
        }

        $palavras = config('palavras.palavras');

        $palavraValida = in_array($palavraTentativa, $palavras);

        if (!$palavraValida) {
            return response()->json([
                'resultado' => [],
                'venceu' => false,
                'tentativasRestantes' => 6 - self::$jogos[$idJogo]['tentativas'],
                'palavraValida' => false
            ], 200);
        }

        $palavraSecreta = self::$jogos[$idJogo]['palavra'];

        self::$jogos[$idJogo]['tentativas']++;

        $resultado = [];

        $letrasSecretas = mb_str_split($palavraSecreta);
        $letrasTentativa = mb_str_split($palavraTentativa);

        for ($i = 0; $i < 5; $i++) {

            $letra = $letrasTentativa[$i];

            if ($letra === $letrasSecretas[$i]) {

                $status = 'correta';

            } elseif (in_array($letra, $letrasSecretas)) {

                $status = 'presente';

            } else {

                $status = 'ausente';
            }

            $resultado[] = [
                'letra' => $letra,
                'status' => $status
            ];
        }

        $venceu = ($palavraTentativa === $palavraSecreta);

        $tentativasRestantes = 6 - self::$jogos[$idJogo]['tentativas'];

        return response()->json([
            'resultado' => $resultado,
            'venceu' => $venceu,
            'tentativasRestantes' => $tentativasRestantes,
            'palavraValida' => true
        ], 200);
    }
}