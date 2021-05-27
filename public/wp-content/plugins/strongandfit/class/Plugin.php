<?php

namespace StrongAndFit;

class Plugin
{
    public function __construct()
    {
        add_action('init', [$this, 'createProgramPostType']);
        add_action('init', [$this, 'createSessionPostType']);
        add_action('init', [$this, 'createCustomTaxonomies']);
    }

    // CPT Programme -----------------------------------------------------------

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

        // Custom meta data
        $options = [
            'type' => 'int',
            'single' => true,
            'show_in_rest' => true
        ];
        // we associate the custom meta to the cpt program
        register_post_meta('program', 'best_user_id', $options);
        register_post_meta('program', 'best_user_time', $options);

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

    // CPT Séance -----------------------------------------------------------

    public function createSessionPostType()
    {

        $labels = [
            'name' => 'Session',
            'singular_name' => 'Session',
            'menu_name' => 'Session',
            'all_items' => 'Sessions list',
            'view_item' => 'View session',
            'add_new_item' => 'New session',
            'add_new' => 'New session',
            'edit_item' => 'Edit session',
            'update_item' => 'Edit session',
            'search_items' => 'Sessions search',
            'not_found' => 'No session found',
            'not_found_in_trash' => 'No session found in trash',
        ];

        register_post_type(
            'session',
            [
                'label' => 'Session',
                'labels' => $labels,
                'public' => true,
                'hierarchical' => false,
                'show_in_rest' =>  true,
                'supports' => [
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'author',
                    'comments',
                    'custom-fields',
                ]
            ]
        );

        $options = [
            'type' => 'int',
            'single' => true,
            'show_in_rest' => true
        ];

        register_post_meta('session', 'user_time', $options);
        register_post_meta('session', 'program_id', $options);

        // display the fields
        add_action('edit_form_after_editor', [$this, 'displayUserTimeForm']);
        add_action('edit_form_after_editor', [$this, 'displayProgramIdForm']);

        // save the fields when the cpt is saved
        add_action('save_post_session', [$this, 'saveUserTimeMeta']);
        add_action('save_post_session', [$this, 'saveProgramIdMeta']);
    }

    public function displayUserTimeForm($post)
    {
        if($post->post_type !== 'session') {
            return false;
        }

        $postId = $post->ID;

        // getting the meta
        $userTime = get_post_meta(
            $postId,
            'user_time',
            true
        );

        $userTime = htmlentities($userTime);

        echo '
            <div id="user_time_div" style="margin-top: 1rem">
                <div id="user_time_wrap" class="form-field">
                    <label class="" id="user_time-prompt-text" for="user_time">User Time</label>
                    <input type="number" name="session_user_time" size="30" value="' . $userTime . '" id="user_time" style="width: 100%">
                </div>
            </div>
        ';
    }

    public function saveUserTimeMeta($postId)
    {

        $userTime = filter_input(INPUT_POST, 'session_user_time', FILTER_VALIDATE_INT);

        if($userTime) {
            update_post_meta(
                $postId,
                'user_time',
                $userTime
            );
        }
    }

    public function displayProgramIdForm($post)
    {
        if($post->post_type !== 'session') {
            return false;
        }

        $postId = $post->ID;

        $programId = get_post_meta(
            $postId,
            'program_id',
            true
        );

        $programId = htmlentities($programId);

        echo '
            <div id="program_id_div" style="margin-top: 1rem">
                <div id="program_id_wrap" class="form-field">
                    <label class="" id="program_id-prompt-text" for="program_id">Program Id</label>
                    <input type="number" name="session_program_id" size="30" value="' . $programId . '" id="program_id" style="width: 100%">
                </div>
            </div>
        ';
    }

    public function saveProgramIdMeta($postId)
    {

        $programId = filter_input(INPUT_POST, 'session_program_id', FILTER_VALIDATE_INT);

        if($programId) {
            update_post_meta(
                $postId,
                'program_id',
                $programId
            );
        }
    }

    // ---------------------------------------------------------------

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