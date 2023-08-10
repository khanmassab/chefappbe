<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
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
                                'code' => 499,
                                'message' => 'You are session has been expired please login'
                            ],499);
                        }
                        
                    }            
                }
            }
        return ($request);
    }

}
