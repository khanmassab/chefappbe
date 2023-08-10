<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Laravel\Passport\Token;
use Illuminate\Http\Request;
use PeterPetrus\Auth\PassportToken;
use Symfony\Component\HttpFoundation\Response;

class Guest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
            if ($request->bearerToken()) {
                $user = $request->user('api');
                if ($user && $user->token()) {
                    $token = $user->token();
                    $client = $token->client;
                    if ($client && $client->personal_access_client) {
                        return $next($request);
                    }
                }
                else {
                    
                  
        $httpAuthorization = '';
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $httpAuthorization = $_SERVER['HTTP_AUTHORIZATION'];
        }
         if(Str::startsWith($httpAuthorization,'Bearer'))
         {
             $token = Str::substr($httpAuthorization,7);
             $tokenDec = new PassportToken(
                 $token
             );

             if(!Token::where('id', $tokenDec->token_id)->exists())
             {
                return response()->json([
                    'code' =>401,
                    'message' => 'Invalid token'
                ]);
             }
             $accessToken = Token::where('id', $tokenDec->token_id)->first();

             if($accessToken) {
                $newToken = Token::where(['user_id' => $accessToken->user_id,'revoked' => 0])->first();
                if($newToken)
                {
                    $expire = Carbon::parse($newToken->expires_at);
               
                    $currentDatetime = Carbon::now();
    
                    if($expire->greaterThan($currentDatetime)) {
                        return response()->json([
                            'code' => 463,
                            'message' => 'The current session has been ended as the new session is started on another device.'
                        ]);
                    }
                }
               
            }
             if($accessToken && $accessToken->revoked == true)
             {
                 return response()->json([
                     'code' =>463,
                     'message' => 'You are session has been  revoked please login'
                 ]);
             }
             else
             {
                return response()->json([
                    'code' =>499,
                    'message' => 'You are session has been expired please login'
                ],499);
             }
             
         }            
                }
            }
    
        return $next($request);
    }
}
