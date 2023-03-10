<?php
/**
 * Plugin Name: Simple Contact Form
 * Description: Simple Contact Form 
 * Author: Emmauel
 * Author URL: https://sample.com
 * Version 1.0.0
 * Text Domain: simple-contact-form
 */
if (!defined('ABSPATH')) 
{
    exit();
}
class SimpleContactForm {
    public function __construct(){
        add_action('init', [ $this, 'create_custom_post_type']);
        add_action( 'wp_enqueue_scripts', [$this, 'load_assets']);
        add_shortcode( 'contact-form', [$this, 'load_shortcode']);
        add_action('wp_footer', [$this, 'load_scripts']);
        add_action('rest_api_init', [$this, 'register_rest_api']);
    }

    public function create_custom_post_type()
    {
       $arg = [
          'public' => true,
          'has_archive' => true,
          'supports' => ['title'],
          'exclude_from_search' => true,
          'publicly_queryable' => false,
          'capacity' => 'manage_option',
              'labels' => [
                'name' => 'Contact form',
                'singular_name' => 'Contact Form Entry'
              ],
              'menu_icon' => 'dashboards-media-text'
          ,

       ];

       register_post_type( 'simple_contact_form', $arg );
    }

    public function load_assets() 
    {
        // echo '<script>alert("hello") </script>';
         wp_enqueue_style( 'simple-contact-form', plugin_dir_url(__FILE__).'css/simple-contact-form.css', [], 1, 'all' );

         wp_enqueue_script( 'simple-contact-form', plugin_dir_url( __FILE__ ).'js/simple-contact-form.js', ['jquery'], 1, true);
         

        }

    public function load_shortcode() 
    {?>
        <div class="simple-contact-form">
            <h2>Contact Form </h2>
            <form>
                <div class="form-group">
                <input type="text" class="form-control" placeholder="username" name="username"lt(/> 
                <input type="email" class="form-control" name="email" placeholder="email"/> 
                </div>
                <button class="btn btn-success btn-block">Submit</button>
            </form>
     </div>
    <?php }


     public function load_scripts() 
     {?>
          <script>
            let nonce = '<?php echo wp_create_nonce("wp_rest");?>';

               
            jQuery('form').submit(function(event) {
                event.preventDefault();
                const form = jQuery(this).serialize();
                jQuery.ajax(
                    {
                         method: 'post',
                         url: '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');  ?>',
                         headers: { 'X-WP-Nonce' : nonce},
                         data: form
                    
                    }
                )
            })
          </script>
    <?php }

    public function register_rest_api() 
    {
        register_rest_route('simple-contact-form/v1/', 'send-email', [
             'methods' => 'post',
             'callback' => [$this, 'handle_contact_form']
        ] );
    }

    public function handle_contact_form($data)
    {

       $headers = $data->get_headers();
       $params = $data-> get_params();
       $nonce = $headers['x_wp_nonce'][0];
        if(!wp_verify_nonce( $nonce, 'wp_rest')) 
        {
                return new  WP_REST_Response('error', 422);
        }
        $post_id = wp_insert_post([
            'post_type' => 'simple_contact_form',
            'post_title' => 'Contact enquiry',
            'post_status' => 'publish'
        ]);
        if($post_id) 
        { 
           return new WP_REST_Response('message sent', 201);
        }
    }
}
new SimpleContactForm
?>