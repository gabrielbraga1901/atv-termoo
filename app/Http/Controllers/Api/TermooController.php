<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TermooController extends Controller
{
    private $tentativasMaximas = 6;

    public function iniciarJogo()
    {
        $palavras = include storage_path('app/termoo/palavras.php');

        $palavraSecreta = $palavras[array_rand($palavras)];

        $idJogo = Str::uuid()->toString();

        Cache::put("jogo_$idJogo", [
            'palavra' => $palavraSecreta,
            'tentativas' => 0
        ], now()->addHours(2));

        return response()->json([
            'idJogo' => $idJogo,
            'tamanhoPalavra' => 5,
            'tentativasMaximas' => $this->tentativasMaximas
        ], 200);
    }

    public function validarTentativa(Request $request)
    {
        $request->validate([
            'idJogo' => 'required|string',
            'palavra' => 'required|string|size:5'
        ]);

        $jogo = Cache::get("jogo_" . $request->idJogo);

        if (!$jogo) {
            return response()->json([
                'erro' => 'Jogo não encontrado'
            ], 404);
        }

        $palavraDigitada = mb_strtolower($request->palavra);
        $palavraSecreta = mb_strtolower($jogo['palavra']);

        $palavrasValidas = include storage_path('app/termoo/palavras.php');

        if (!in_array($palavraDigitada, $palavrasValidas)) {

            return response()->json([
                'resultado' => [],
                'venceu' => false,
                'tentativasRestantes' => $this->tentativasMaximas - $jogo['tentativas'],
                'palavraValida' => false
            ], 200);
        }

        $resultado = [];

        for ($i = 0; $i < 5; $i++) {

            $letra = $palavraDigitada[$i];

            if ($letra === $palavraSecreta[$i]) {
                $status = 'correta';
            } elseif (str_contains($palavraSecreta, $letra)) {
                $status = 'presente';
            } else {
                $status = 'ausente';
            }

            $resultado[] = [
                'letra' => $letra,
                'status' => $status
            ];
        }

        $jogo['tentativas']++;

        Cache::put("jogo_" . $request->idJogo, $jogo, now()->addHours(2));

        $venceu = $palavraDigitada === $palavraSecreta;

        return response()->json([
            'resultado' => $resultado,
            'venceu' => $venceu,
            'tentativasRestantes' => $this->tentativasMaximas - $jogo['tentativas'],
            'palavraValida' => true
        ], 200);
    }
}