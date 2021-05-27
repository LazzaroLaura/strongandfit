<?php

namespace StrongAndFit\CustomPostType;

class Session
{

    public function initialize()
    {

        add_action('init', [$this, 'createSessionPostType'], 50);
    }

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

}