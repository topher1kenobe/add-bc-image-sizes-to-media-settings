<?php
/*
Plugin Name: Add BigCommerce Image Sizes To Media Settings
Plugin URI: 
Description: Adds BigCommerce image dimension settings to Media Settings page in WordPress
Version: 1.0
Author: Topher
License: GPLv3+
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
*/

/**
 * Instantiate the BC_Update_Image_Sizes instance
 * @since BC_Update_Image_Sizes 1.0
 */
add_action( 'init', [ 'BC_Update_Image_Sizes', 'instance' ] );

class BC_Update_Image_Sizes {

    /**
    * Instance handle
    *
    * @static
    * @since 1.2
    * @var string
    */
    private static $__instance = null;

    /**
    * Image sizes
    *
    * @var array
    */
    private $sizes = [
        'thumb'  => 'bc-thumb',
        'small'  => 'bc-small',
        'medium' => 'bc-medium',
        'large'  => 'bc-large',
    ];

    /**
     * Constructor, actually contains nothing
     *
     * @access public
     * @return void
     */
    public function __construct() {
    }

    /*
     * Instance initiator, runs setup etc.
     *
     * @static
     * @access public
     * @return self
     */
    public static function instance() {
        if ( ! is_a( self::$__instance, __CLASS__ ) ) {
            self::$__instance = new self;
            self::$__instance->hooks();
        }

        return self::$__instance;
    }

    /*
     * Run hooks
     *
     * @access public
     * @return NULL
     */
    public function hooks() {
        add_action( 'admin_init', [ $this, 'run_on_admin_init' ] );
        add_action( 'init',       [ $this, 'new_bc_images' ], 11 );
    }

    /**
     * Actions to run on admin_init
     *
     * @return null
     */
    public function run_on_admin_init() {
        add_settings_field(
            'image_sizes_add',
            '<h2 class="title">' . __( 'BigCommerce Image Sizes' ) . '</h2>',
            [ $this, 'bc_add_settings_field' ],
            'media'
        );

        foreach ( $this->sizes as $key => $size ) {
            register_setting( 'media', 'bc_' . $key . '_size_w', 'intval' );
            register_setting( 'media', 'bc_' . $key . '_size_h', 'intval' );
            register_setting( 'media', 'bc_' . $key . '_crop', 'intval' );
        }
    }

    /**
     * Add new image sizes
     *
     * @return null
     */
    public function new_bc_images() {

        foreach ( $this->sizes as $key => $size ) {

            if ( ! empty( get_option( 'bc_' . esc_attr( $key ) . '_size_w' ) ) ) {
                add_image_size(
                    'bc-' . esc_attr( $key ),
                    get_option( 'bc_' . esc_attr( $key ) . '_size_w' ),
                    get_option( 'bc_' . esc_attr( $key ) . '_size_h' ),
                    get_option( 'bc_' . esc_attr( $key ) . '_crop' )
                );
            }
        }
    }


    /**
     * Render the form fields
     *
     * @uses   get_image_size()
     * @return string
     */
    public function bc_add_settings_field() {

        foreach ( $this->sizes as $key => $size ) {

            $bc_dimensions = $this->get_image_size( $size );
                ?>
                <tr>
                <th scope="row">BigCommerce <?php echo ucfirst( $key ); ?></th>
                <td><fieldset><legend class="screen-reader-text"><span><?php _e( 'BC Thumbnail size' ); ?></span></legend>
                <label for="bc_<?php echo esc_attr( $key ); ?>_size_w"><?php _e( 'Width' ); ?></label>
                <input name="bc_<?php echo esc_attr( $key ); ?>_size_w" type="number" step="1" min="0" id="bc_<?php echo esc_attr( $key ); ?>_size_w" value="<?php echo get_option( 'bc_' . esc_attr( $key ) . '_size_w', absint( $bc_dimensions['width'] )  ); ?>" class="small-text" />
                <br />
                <label for="bc_<?php echo esc_attr( $key ); ?>_size_h"><?php _e( 'Height' ); ?></label>
                <input name="bc_<?php echo esc_attr( $key ); ?>_size_h" type="number" step="1" min="0" id="bc_<?php echo esc_attr( $key ); ?>_size_h" value="<?php echo get_option( 'bc_' . esc_attr( $key ) . '_size_h', absint( $bc_dimensions['height'] )  ); ?>" class="small-text" />
                </fieldset>
                <input name="bc_<?php echo esc_attr( $key ); ?>_crop" type="checkbox" id="bc_<?php echo esc_attr( $key ); ?>_crop" value="1" <?php checked( '1', get_option( 'bc_' . esc_attr( $key ) . '_crop', absint( $bc_dimensions['crop'] ) ) ); ?>/>
                <label for="bc_<?php echo esc_attr( $key ); ?>_crop"><?php _e( 'Crop to exact dimensions.' ); ?></label>
                </td>
                </tr>
            <?php
        }
    }

    /**
     * Get size information for all currently-registered image sizes.
     *
     * @global $_wp_additional_image_sizes
     * @uses   get_intermediate_image_sizes()
     * @return array $sizes Data for all currently-registered image sizes.
     */
    public function get_image_sizes() {
        global $_wp_additional_image_sizes;

        $sizes = array();

        foreach ( get_intermediate_image_sizes() as $_size ) {
            if ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
                $sizes[ $_size ] = array(
                    'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
                    'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                    'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
                );
            }
        }

        return $sizes;
    }

    /**
     * Get size information for a specific image size.
     *
     * @uses   get_image_sizes()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|array $size Size data about an image size or false if the size doesn't exist.
     */
    public function get_image_size( $size ) {
        $sizes = $this->get_image_sizes();

        if ( isset( $sizes[ $size ] ) ) {
            return $sizes[ $size ];
        }

        return false;
    }

}
?>
