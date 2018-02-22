<?php

namespace App\Http\Controllers;


use App\Exceptions\CustomExceptions\InternalServerError;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    public function verify(Request $request)
    {
        $site = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
        //$secret = env('RECAPTCHA_SECRET', 'hi');// '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
        $secret = config('app.recaptcha_secret');
        if($secret===Null)
            throw new InternalServerError('The connection to the Google API cannot be established!');

        $client = new Client();
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $request->all()['g-recaptcha-response'];

        $response = $client->post($url);
        return $response->getBody();
    }

    public function getTestForm(Request $request)
    {
        return new Response("<script src='https://www.google.com/recaptcha/api.js'></script>﻿<form action=\"/verify_recaptcha\" method=\"POST\"><div class=\"g-recaptcha\" data-sitekey=\"6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI\"></div><input type=\"hidden\" value=\"haa\"/></ipnut><input type=\"submit\" value=\"Submit\"></form>");
    }
}
