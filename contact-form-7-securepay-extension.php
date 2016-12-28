<?php
/**
 * Plugin Name: Contact Form 7 - SecurePay Extension
 * Plugin URL: 
 * Description:  This plugin will integrate Secure Pay Button
 * Version: 1.0
 * Author: Miracle Interface
 * Author URI: https://www.miracleinterface.com
 * Developer: Miracle Interface Wordpress Team
 * Text Domain: contact-form-7-extension
 * Domain Path: /languages
 * 
 */

/**
 * Register the [securepaysubmit] shortcode
 *
 * This shortcode will integrate PayPal button with your contact form.
 * It will allow you to generate tag with parameters like 
 * PayPal business email, item amount field, item name field, currency, PayPal mode, return page URL
 *
 * @access      public
 * @since       1.0 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

define( 'CF7SPE_PLUGIN', __FILE__ );
define( 'CF7SPE_PLUGIN_BASENAME', plugin_basename( CF7SPE_PLUGIN ) );
define( 'CF7SPE_PLUGIN_NAME', trim( dirname( CF7SPE_PLUGIN_BASENAME ), '/' ) );
define( 'CF7SPE_PLUGIN_ASSETS_PATH', WP_PLUGIN_URL.'/'.CF7SPE_PLUGIN_NAME.'/assets/');
define( 'CF7SPE_PLUGIN_CSS_PATH', CF7SPE_PLUGIN_ASSETS_PATH.'css/');
define( 'CF7SPE_PLUGIN_IMAGE_PATH', CF7SPE_PLUGIN_ASSETS_PATH.'images/');

/*slug/id of page where to be redirected
* need to modify as permalink may changed
**/
define ('SECUREPAY_PAGE_SLUG', 'securepay');

/**
 * Check if Contact Form 7 is active
 **/
require_once (dirname(__FILE__) . '/contact-form-7-securepay-extension.php');

register_activation_hook (__FILE__, 'securepay_submit_activation_check');
function securepay_submit_activation_check()
{
    if ( !in_array( 'contact-form-7/wp-contact-form-7.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        wp_die( __( '<b>Warning</b> : Install/Activate Contact Form 7 to activate "Contact Form 7 - PayPal Extension" plugin', 'contact-form-7' ) );
    }
}

/**
** A base module for [securepaysubmit] - A submit button that will redirect to PayPal after form submit.
**/

/* Shortcode handler */


add_action('init', 'contact_form_7_securepay_submit', 11);

function contact_form_7_securepay_submit() {	
	wp_enqueue_style( 'securePayFormCss', CF7SPE_PLUGIN_CSS_PATH.'securePayFormCss.css',array(),null);
	if(function_exists('wpcf7_add_shortcode')) {
		wpcf7_add_shortcode( 'securepaysubmit', 'wpcf7_securepay_submit_shortcode_handler', false );
		add_shortcode('securepayform', 'getSecurePayFormHtml');

	} else {
		return; 		
	}
}

/**
  * Generate securepay redirection URL using parameters entered in tag 
  */

add_action('wp_head','wpcf7_securepay_location');
function wpcf7_securepay_location(){	?>
	<script>	
		var redirectUrl="";			
		function setRedirectUrl(e, redirect_url)
		{	
			redirectUrl = redirect_url;

			// changing action of form
		 	// jQuery(e.currentTarget).parents('form').attr('action', redirectUrl);
		 	// jQuery(e.currentTarget).parents('form').attr('id', "securepay_form_id");


		 	var confirmResult = confirm('Are You Sure To Pay?');
		 	if (confirmResult == true) {
		 		// opening another form for secure pay
		 		window.location = redirectUrl;
		 	}
		 	// jQuery('#securepayForm').show();
		}

		jQuery(document).ready(function(){
			jQuery(document).on('mailsent.wpcf7', function () {	
				if(redirectUrl == "")
			    {			    	
			    	//jQuery('.wpcf7-response-output').append('You are not redirected to PayPal as you have not configured PayPal Submit Button properly. <br>');
			    }
			    else if(redirectUrl != "")
			    {
			    	//redirecting
			    	//window.location = "https://api.securepay.com.au/test/directpost/authorise?EPS_MERCHANT=EAH0031&EPS_TXNTYPE=0&EPS_REFERENCEID=100&EPS_AMOUNT=1.00&EPS_TIMESTAMP=20161227064416&EPS_FINGERPRINT=6cf77c9a2c4cef470c689c318b810c9a75ef1e7b&EPS_RESULTURL=http%3A%2F%2Fsite.litecms03.com%2Findex.php&EPS_CARDNUMBER=4444333322221111&EPS_EXPIRYMONTH=12&EPS_EXPIRYYEAR=2016&submitSecurePay=Secure+Pay";
			    }
			});
		});			
	</script>
<?php
}

/**
  * Regenerate shortcode into SecurePay submit button
  */

function wpcf7_securepay_submit_shortcode_handler( $tag ) {	
	$tag = new WPCF7_Shortcode( $tag );	
	$class = wpcf7_form_controls_class( $tag->type );	
	$atts = array();	
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
    //
    $EPS_MERCHANT 		= $tag->get_option('EPS_MERCHANT');
    $EPS_TXNTYPE 		= $tag->get_option('EPS_TXNTYPE');
	$EPS_RESULTURL 		= $tag->get_option('EPS_RESULTURL');
	$EPS_CURRENCY 		= $tag->get_option('EPS_CURRENCY');
    $_EPS_ACTIONURL 	= $tag->get_option('_EPS_ACTIONURL');
    $_EPS_SECUREPAGE 	= $tag->get_option('_EPS_SECUREPAGE');


	$queryString = array(
		'EPS_MERCHANT'		=> (empty($EPS_MERCHANT[0])) ? '' : $EPS_MERCHANT[0],	
		'EPS_TXNTYPE'		=> (empty($EPS_TXNTYPE[0])) ? '0' : $EPS_TXNTYPE[0],	
		'_EPS_ACTIONURL'	=> (empty($_EPS_ACTIONURL[0])) ? '' : $_EPS_ACTIONURL[0],
		'EPS_RESULTURL'		=> (empty($EPS_RESULTURL[0])) ? '' : $EPS_RESULTURL[0],
		'EPS_CURRENCY'		=> (empty($EPS_CURRENCY[0])) ? 'AUD' : $EPS_CURRENCY[0],						
	);

	$url = get_site_url().'/'.SECUREPAY_PAGE_SLUG.'?'.http_build_query($queryString);
	$atts['onclick']= 'setRedirectUrl(event,"'.$url.'")';
	$value = 'SecurePay';

	$atts['type'] = 'button';
	$atts['value'] = $value;
	$atts = wpcf7_format_atts( $atts );

	$html = "";
	$html .= sprintf( '<input %1$s />', $atts );

	return $html;
}

function getSecurePayFormHtml() {

	// To Do dynamic content
	$content = file_get_contents(CF7SPE_PLUGIN_ASSETS_PATH.'securepayForm.php');
	$html = "";
	$html .= $content;
	return $html;
}

/************************************~: Admin Section of paypal submit button :~************************************/

/* Tag generator */

add_action( 'admin_init', 'wpcf7_add_tag_generator_securepay_submit', 55 );

function wpcf7_add_tag_generator_securepay_submit() {	
	if(class_exists('WPCF7_TagGenerator')){
		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add( 'securepay-submit', __( 'Secure Pay', 'contact-form-7' ),
		'wpcf7_tg_pane_securepay_submit', array( 'nameless' => 1 ) );
	}	
}

/** Parameters field for generating tag at backend **/

function wpcf7_tg_pane_securepay_submit( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$description = __( "Generate a form-tag for Secure Pay Submit Button", 'contact-form-7' );
	$desc_link = wpcf7_link( '',__( 'SecurePay Submit Button', 'contact-form-7' ) );
	$currency = array('AUD'=>'Australian Dollar','BRL'=>'Brazilian Real','CAD'=>'Canadian Dollar','CZK'=>'Czech Koruna','DKK'=>'Danish Krone','EUR'=>'Euro','HKD'=>'Hong Kong Dollar','HUF'=>'Hungarian Forint','ILS'=>'Israeli New Sheqel','JPY'=>'Japanese Yen','MYR'=>'Malaysian Ringgit','MXN'=>'Mexican Peso','NOK'=>'Norwegian Krone','NZD'=>'New Zealand Dollar','PHP'=>'Philippine Peso','PLN'=>'Polish Zloty','GBP'=>'Pound Sterling','RUB'=>'Russian Ruble','SGD'=>'Singapore Dollar', 'SEK'=>'Swedish Krona','CHF'=>'Swiss Franc','TWD'=>'Taiwan New Dollar','THB'=>'Thai Baht','TRY'=>'Turkish Lira','USD'=>'U.S. Dollar');
	
	/*Used to determine the processing type for an individual transaction*/
	$arrayTransactionType = array(
		'0'=>'PAYMENT',
		'1'=>'PREAUTH',
		'2'=>'PAYMENT with FRAUDGUARD',
		'3'=>'PREAUTH with FRAUDGUARD',
		'5'=>'PREAUTH with 3D Secure',
		'6'=>'PAYMENT with FRAUDGUARD and 3D Secure',
		'7'=>'PREAUTH with FRAUDGUARD and 3D Secure',
		'8'=>'STORE ONLY'
	);
?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>
<table class="form-table">
<tbody>
<tr><td colspan="2"><a href="http://miracleinterface.com" target="_blank">
	<img src="<?php echo bloginfo('wpurl').'/wp-content/plugins/contact-form-7-securepay-extension/assets/images/securepay.png';?>" width="540">
</a></td></tr>
<tr>
<td colspan="2"><b>NOTE: Please fill all required fields.</b></td>
</tr>
<tr>
	<td><code>EPS_MERCHANT</code> <?php echo '<font style="font-size:10px"> (Required)</font>';?><br />
	<input type="text" name="EPS_MERCHANT" placeholder="ABC0010" class="idvalue oneline option" /></td>

	<td><?php echo esc_html( __( 'Select EPS_TXNTYPE', 'contact-form-7' ) ); echo ' (Default "0")';?><br />
		<select name="EPS_TXNTYPES" onchange="document.getElementById('currency').value = this.value;">
			<?php foreach($arrayTransactionType as $key=>$value) { ?>
				<option value="<?php echo $key;?>" <?php echo ($key == "0")?'selected':'';?>><?php echo $value;?></option>
			<?php } ?>
		</select>
		<input type="hidden" value="" name="EPS_TXNTYPE" id="EPS_TXNTYPE" class="oneline option">
	</td>
</tr>

<tr>
	<td><?php echo esc_html( __( 'TRANSACTION_PASSWORD', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>'; ?><br />
	<input type="text" name="TRANSACTION_PASSWORD" class="oneline" /></td>
	<td><?php echo esc_html( __( 'Select Currency', 'contact-form-7' ) ); echo ' (Default "AUD")';?><br />
		<select name="currencies" onchange="document.getElementById('currency').value = this.value;">
			<?php foreach($currency as $key=>$value) { ?>
				<option value="<?php echo $key;?>" <?php echo ($key == "AUD")?'selected':'';?>><?php echo $value;?></option>
			<?php } ?>
		</select>
		<input type="hidden" value="" name="currency" id="currency" class="oneline option">
	</td>
	
</tr>
<td><?php echo esc_html( __( 'ACTION  URL', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>';?><br />
	<input type="text" name="_EPS_ACTIONURL" placeholder="https://api.securepay.com.au/test/directpost/authorise" class="oneline option" /></td>
<td><?php echo esc_html( __( 'EPS_RESULTURL', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>';?><br />
	<input type="text" name="EPS_RESULTURL"value="<?php echo bloginfo('wpurl'); ?>" placeholder="https://yourdomain.com" class="oneline option" /></td>
</tr>
</tbody>
</table>
</fieldset>
</div>
<div class="insert-box">
	<input type="text" name="securepaysubmit" class="tag code" readonly="readonly" onfocus="this.select()" />
	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>
</div>
<?php
}

?>