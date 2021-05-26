<?php

namespace StrongAndFit;

class Api
{
    protected $baseURI;

    public function __construct()
    {
        // baseURI calculation
        // DOC basename https://www.php.net/dirname
        $this->baseURI = dirname($_SERVER['SCRIPT_NAME']);


        // recording of the call to the registerRoutes method when initializing wordpress
        add_action('rest_api_init', [$this, 'registerRoutes']);

        // allowing routes that do not need authentication(token jwt)
        $this->allowEndpoints();
    }

    public function registerRoutes()
    {
        // route to create a new user
        register_rest_route(
            'strongandfit/v1',
            '/signup',
            [
                'methods' => 'POST',
                'callback' => [$this, 'signup'],
            ]
        );
    }

    public function signup()
    {
        // recovery of data sent in POST (be careful, they are sent in JSON "format")
        $data = $this->getDataFromPostJSON();

        // creating a wp user
        // DOC wp_create_user https://developer.wordpress.org/reference/functions/wp_create_user/#return
        $result = wp_create_user(
            $data['username'],
            $data['password'],
            $data['email']
        );

        // if $ result is a number, user creation worked fine
        if(is_int($result)) {
            return [
                // we indicate that everything went well
                'success' => true,

                // we return the id of the new user
                'id' => $result,
                'username' => $data['username'],
                'email' => $data['email'],
            ];
        }
        // user creation failed
        else {
            return [
                'success' => false,
                'errors' => $result->errors
            ];
        }

    }

    // this function allows us to retrieve data that was sent in POST, but in json format
    protected function getDataFromPostJSON()
    {
        // retrieving the sent json
        $json = file_get_contents('php://input');

        // transform the json into an array (the second argument true, tells php that we want an array)
        $data = json_decode($json, true);

        // we return the array
        return $data;
    }

    protected function allowEndpoints()
    {
        // configure jwt-auth to specify which endpoints do not need tokens
        add_filter('jwt_auth_whitelist' , function($endpoints) {

            // WARNING if the endpoint is not found, jwt auth requests an access token
            return [
                $this->baseURI . '/wp-json/strongandfit/v1/signup',
            ];
        });
    }
}