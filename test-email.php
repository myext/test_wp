<?php
/*
 * Plugin Name: test-email
 */


/* js */

add_action( 'wp_enqueue_scripts', 'my_js_method' );
function my_js_method() {
    wp_deregister_script('jquery-core');
    wp_deregister_script('jquery');

    wp_register_script( 'jquery-core', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', false, null, true );
    wp_register_script( 'jquery', false, array('jquery-core'), null, true );

    wp_enqueue_script( 'jquery' );

    $script_url = plugins_url( '/test_wp.js', __FILE__ );

    wp_enqueue_script('custom-script', $script_url, array('jquery') );

}

/* js */



/* form */

function test_wp_mail(){

    $form = '
    <div id="mail">
        <h2>Send message</h2>
        <div id="test_wp_message" style="min-height: 30px"></div>
           <form id="test_wp_mail" action="'.esc_url( admin_url('admin-post.php')).'" method="post">
              <input type="hidden" name="action" value="test_wp">
              <input name="first_name" type="text" placeholder="First Name">
              <input name="last_name" type="text" placeholder="Last Name">
              <input name="subject" type="text" placeholder="Subject">
              <textarea name="message" placeholder="Message:"></textarea>
              <input name="email" type="text" placeholder="E-mail"> 
              <button type="submit" id="test_wp_send">Send</button>
           </form>
     </div>
    ';

    return $form;
}
add_shortcode('test_wp_mail', 'test_wp_mail');
/* end form */


/* plugin's settings page */

add_action('admin_menu', 'add_admin_menu');

function add_admin_menu() {
    add_options_page(
        'test_wp_mail',
        'test_wp_mail',
        'manage_options',
        'test-wp-options-page.php',
        'test_wp_options_page'
    );
}

function test_wp_options_page() {
    echo '<nodiv class="wrap">';
    echo '<form method="post" action="options.php">';
    do_settings_sections('test_wp_custom');
    settings_fields('test_wp_custom');
    submit_button();
    echo '</form>';
    echo '</nodiv>';
}

function test_wp_settings_init() {
    register_setting('test_wp_custom', 'test_wp_settings');
    add_settings_section(
        'test_wp_section_id',
        'Настройки плагина "test-email"',
        'test_wp_email_render',
        'test_wp_custom'
    );
    add_settings_field(
        'email',
        'Введите email',
        '',
        'test_wp_custom',
        'test_wp_section_id'
    );
}

function test_wp_email_render( ) {
    $options = get_option( 'test_wp_settings' );

    $field = '<input type="email" name="test_wp_settings[email]" value="'.$options['email'].'">';

    echo $field;
}

add_action( 'admin_init', 'test_wp_settings_init' );

/* end plugin's settings page */



/* process POST */

function test_wp_send_email()
{
    if (!isset($_POST['email']) || !is_email( $email = trim($_POST['email']))){
        header('HTTP/1.1 422 Unprocessable Entity');
        header('Content-Type: application/json, charset=utf-8');
        echo json_encode(["error" => "email error"]);
        exit;
    }

    /* create user */

    $data = [
        'properties' => [
            [
                'property' => 'firstname',
                'value' => $_POST['first_name']
            ],
            [
                'property' => 'lastname',
                'value' => $_POST['last_name']
            ]
        ]
    ];

    $url = "https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/{$email}/?hapikey=ccbe433e-281c-46ba-988d-70bc76b8f405";

    $user = wp_remote_post($url,
        [
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8'
            ],
            'body' => json_encode($data)
        ]
    );

    /* end create user */


    $email_to = get_option( 'test_wp_settings' )['email'];

    $res = false;

    if(is_email($email_to)){
        $res = wp_mail($email_to, $_POST['subject'], $_POST['message']);
    }

    if ($res) {
        $log_message = 'Письмо от пользователя с email '.$email.' отправлено';
        file_put_contents( __DIR__.'/test_wp.log',$log_message.PHP_EOL, FILE_APPEND );

        header('HTTP/1.1 200 Ok');
        header('Content-Type: application/json, charset=utf-8');
        echo json_encode(["message" => "email send successful"]);
        exit;
    }

    header('HTTP/1.1 422');
    header('Content-Type: application/json, charset=utf-8');
    echo json_encode(["error" => "email error"]);
    exit;
}


add_action( 'admin_post_nopriv_test_wp', 'test_wp_send_email' );
add_action( 'admin_post_test_wp', 'test_wp_send_email' );







