<?php

namespace Bestkit\Update\Controller;

use Bestkit\Http\Controller\AbstractHtmlController;
use Illuminate\Contracts\View\Factory;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends AbstractHtmlController
{
    /**
     * @var Factory
     */
    protected $view;

    /**
     * @param Factory $view
     */
    public function __construct(Factory $view)
    {
        $this->view = $view;
    }

    public function render(Request $request)
    {
        $view = $this->view->make('bestkit.update::app')->with('title', 'Update Bestkit');

        $view->with('content', $this->view->make('bestkit.update::update'));

        return $view;
    }
}
