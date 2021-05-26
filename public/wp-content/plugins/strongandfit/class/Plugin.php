<?php

namespace StrongAndFit;

class Plugin
{
    public function __construct()
    {
        add_action('init', [$this, 'createCustomPostTypes']);
        add_action('init', [$this, 'createCustomTaxonomies']);
    }

    public function createCustomPostTypes()
    {
        // DOC create custom post type https://developer.wordpress.org/reference/functions/register_post_type/
        register_post_type(
            'program',    // cpt identifier
            [
                'label' => 'Programme',
                'public' => true, // the account can be edited from the bo
                'hierarchical' => false,
                'show_in_rest' =>  true, // our cpt will be accessible from the API rest of wp
                'supports' => [
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'author',
                    'comments',
                    'custom-fields', // we allow the custom meta to be available in the api, so we activate the cutom-fields field
                ]
            ]
        );
    }

    public function createCustomTaxonomies()
    {

        // creation of the zone taxonomy
        register_taxonomy(
            'zone',  //identifier
            ['program'], // which cpt the taxonomy is associated with
            [
                'label' => 'Zone',
                'public' => true,   // manageable from the bo
                'show_in_rest' => true, // the custom taxonomy is accessible from the API rest
                'hierarchical' => true,
            ]
        );

        register_taxonomy(
            'type',
            ['program'],
            [
                'label' => 'Type d\'entrainement',
                'public' => true,
                'show_in_rest' => true,
                'hierarchical' => true,
            ]
        );
    }
}