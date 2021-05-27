<?php

namespace StrongAndFit;

use StrongAndFit\CustomPostType\Program;
use StrongAndFit\CustomPostType\Session;

class Plugin
{
    public function __construct()
    {
        $this->initialize();
    }

    // this function is called each time the plugin is "started"
    protected function initialize()
    {
        // when initializing wordpress, we save the custom post types and custom taxonomies we need
        $program = new Program();
        $program->initialize();


        $session = new Session();
        $session->initialize();

    }
    
}