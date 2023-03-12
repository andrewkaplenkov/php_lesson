<?php

namespace App\HTTP\Actions;

use App\HTTP\Request\Request;
use App\HTTP\Response\Response;

interface ActionInterface
{
    public function handle(Request $request): Response;
}
