<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class GoogleAuthController extends Controller
{
    protected function jwt(User $user)
    {
        $payload = [
            'iss' => "jwt-auth", 
            'sub' => $user->id, 
            'iat' => time(), 
            'exp' => time() + 60 * 60 
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }

    public function redirect()
    {
        $parameters = [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'redirect_uri' => 'https://uts-salmanfauzi-1063.libranation.my.id/api/oauth/call-back',
            // d
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'state' => 'state_parameter_passthrough_value',
            'prompt' => 'consent' 
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($parameters);
        return response()->json(['redirect_url' => $authUrl]);
    }

    public function callbackGoogle(Request $request)
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json(['error' => 'Kode otorisasi tidak ditemukan'], 400);
        }

        try {
            $client = new Client();

            $response = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'code' => $code,
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'redirect_uri' => 'https://uts-salmanfauzi-1063.libranation.my.id/api/oauth/call-back',
                    // http://127.0.0.1:8000/api/oauth/call-back =>local
                    'grant_type' => 'authorization_code',
                    'access_type' => 'offline',
                ],
            ]);

            $tokenData = json_decode($response->getBody(), true);
            $accessToken = $tokenData['access_token'];

            //Gunakan token akses untuk mendapatkan informasi pengguna
            $google_user = Socialite::driver('google')->stateless()->userFromToken($accessToken);

            // Periksa apakah pengguna dengan email yang sama sudah ada
            $user = User::where('email', $google_user->getEmail())->first();

            if (!$user) {
                // Periksa apakah pengguna dengan ID Google yang sama sudah ada
                $user = User::where('google_id', $google_user->getId())->first();

                if (!$user) {
                    // Buat pengguna baru jika tidak ditemukan email atau ID Google yang cocok
                    $user = User::create([
                        'name' => $google_user->getName(),
                        'email' => $google_user->getEmail(),
                        'google_id' => $google_user->getId(),
                        // Assuming a default role of 'user'
                    ]);
                } else {
                    // Perbarui email pengguna jika berbeda
                    $user->update(['email' => $google_user->getEmail()]);
                }
            }

            $token = $this->jwt($user);

            Auth::login($user);
            return response()->json(['message' => 'Pengguna berhasil login', 'user' => $user, 'bearer token' => $token], 200);

        } catch (\Throwable $th) {
            return response()->json(['error' => 'Ada yang salah', 'details' => $th->getMessage()], 424);
        }
    }
}
