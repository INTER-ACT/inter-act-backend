<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Responsable;

class NoContentResource implements Responsable
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        return \Response::make(null, 204);
    }
}
