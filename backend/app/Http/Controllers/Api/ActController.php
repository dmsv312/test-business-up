<?php

namespace App\Http\Controllers\Api;

use App\Domain\Services\ActUpdater;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateActRequest;
use App\Http\Resources\ActResource;
use App\Models\Act;

class ActController extends Controller
{
    public function update(UpdateActRequest $request, Act $act, ActUpdater $updater): ActResource
    {
        $updater->update($act, $request->validated());

        return new ActResource($act);
    }
}
