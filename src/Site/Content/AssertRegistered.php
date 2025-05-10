<?php

namespace Bestkit\Site\Content;

use Bestkit\Frontend\Document;
use Bestkit\Http\RequestUtil;
use Psr\Http\Message\ServerRequestInterface as Request;

class AssertRegistered
{
    public function __invoke(Document $document, Request $request)
    {
        RequestUtil::getActor($request)->assertRegistered();
    }
}
