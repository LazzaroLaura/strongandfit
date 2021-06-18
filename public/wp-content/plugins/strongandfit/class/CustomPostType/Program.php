<?php

namespace StrongAndFit\CustomPostType;

class Program
{

    public function initialize()
    {

        add_action('init', [$this, 'createProgramPostType'], 50);
        add_action('init', [$this, 'createCustomTaxonomies'], 50);
    }

    public function createProgramPostType()
    {

        $labels = [
            'name' => 'Program',
            'singular_name' => 'Program',
            'menu_name' => 'Program',
            'all_items' => 'Programs list',
            'view_item' => 'View program',
            'add_new_item' => 'New program',
            'add_new' => 'New program',
            'edit_item' => 'Edit program',
            'update_item' => 'Edit program',
            'search_items' => 'Programs search',
            'not_found' => 'No program found',
            'not_found_in_trash' => 'No program found in trash',
        ];

        // DOC create custom post type https://developer.wordpress.org/reference/functions/register_post_type/
        register_post_type(
            'program',    // cpt identifier
            [
                'label' => 'Program',
                'labels' => $labels,
                'public' => true, // the cpt can be edited from the bo
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

        add_action( 'rest_api_init', function () {
            register_post_meta('program', 'best_user_id', [
                'show_in_rest' => true,
                'object_subtype' => 'program',
                'type' => 'string',
                'single' => true,
            ]);
            },
        );

        add_action( 'rest_api_init', function () {
            register_post_meta('program', 'best_user_time', [
                'show_in_rest' => true,
                'object_subtype' => 'program',
                'type' => 'string',
                'single' => true,
            ]);
            },
        );

        // display the fields
        add_action('edit_form_after_editor', [$this, 'displayBestUserForm']);
        add_action('edit_form_after_editor', [$this, 'displayBestUserTimeForm']);

        // save the fields when the cpt is saved
        add_action('save_post_program', [$this, 'saveBestUserMeta']);
        add_action('save_post_program', [$this, 'saveBestUserTimeMeta']);
    }

    // wp send us a WP_Post object when the edit_form_after_editor is fired
    public function displayBestUserForm($post)
    {
        if($post->post_type !== 'program') {
            return false;
        }

        // the post id we are editing
        $postId = $post->ID;

        // getting the meta
        $bestUserId = get_post_meta(
            $postId,    // the post on which we are working
            'best_user_id', // which custom meta we want to get
            true // we don't want the result to be an array
        );

        // security: use of htmlentities to not display corrupted html code (malicious javasript example)
        $bestUserId = htmlentities($bestUserId);

        echo '
            <div id="best_user_id_div" style="margin-top: 1rem">
                <div id="best_user_id_wrap" class="form-field">
                    <label class="" id="best_user_id-prompt-text" for="best_user_id">Best User Id</label>
                    <input type="number" name="program_best_user_id" size="30" value="' . $bestUserId . '" id="best_user_id" style="width: 100%">
                </div>
            </div>
        ';
    }

    // the postId parameter is filled in by worpress when triggering the save_post_ * hook
    public function saveBestUserMeta($postId)
    {

        // saving the cpt custom meta
        $bestUserId = filter_input(INPUT_POST, 'program_best_user_id', FILTER_VALIDATE_INT);

        if($bestUserId) {
            update_post_meta(
                // pour quel post nous souhaitons enregistrer un "champ custom" (custom meta)
                $postId,
                // pour quel champs custom nous enregistrons une valeur
                'best_user_id',
                // ma valeur à enregistrer
                $bestUserId
            );
        }
    }

    public function displayBestUserTimeForm($post)
    {
        if($post->post_type !== 'program') {
            return false;
        }

        $postId = $post->ID;

        $bestUserTime = get_post_meta(
            $postId,
            'best_user_time',
            true
        );

        $bestUserTime = htmlentities($bestUserTime);

        echo '
            <div id="best_user_time_div" style="margin-top: 1rem">
                <div id="best_user_time_wrap" class="form-field">
                    <label class="" id="best_user_time-prompt-text" for="best_user_time">Best User Time</label>
                    <input type="number" name="program_best_user_time" size="30" value="' . $bestUserTime . '" id="best_user_time" style="width: 100%">
                </div>
            </div>
        ';
    }

    public function saveBestUserTimeMeta($postId)
    {

        // saving the cpt custom meta
        $bestUserTime = filter_input(INPUT_POST, 'program_best_user_time', FILTER_VALIDATE_INT);

        if($bestUserTime) {
            update_post_meta(
                $postId,
                // pour quel champs custom nous enregistrons une valeur
                'best_user_time',
                // ma valeur à enregistrer
                $bestUserTime
            );
        }
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