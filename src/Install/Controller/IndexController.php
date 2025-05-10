<?php

namespace Bestkit\Install\Controller;

use Bestkit\Http\Controller\AbstractHtmlController;
use Bestkit\Install\Installation;
use Illuminate\Contracts\View\Factory;
use Psr\Http\Message\ServerRequestInterface as Request;

class IndexController extends AbstractHtmlController
{
    /**
     * @var Factory
     */
    protected $view;

    /**
     * @var Installation
     */
    protected $installation;

    public function __construct(Factory $view, Installation $installation)
    {
        $this->view = $view;
        $this->installation = $installation;
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function render(Request $request)
    {
        $view = $this->view->make('bestkit.install::app')->with('title', 'Install Bestkit');

        $problems = $this->installation->prerequisites()->problems();

        if ($problems->isEmpty()) {
            $view->with('content', $this->view->make('bestkit.install::install'));
        } else {
            $view->with('content', $this->view->make('bestkit.install::problems')->with('problems', $problems));
        }

        return $view;
    }
}
