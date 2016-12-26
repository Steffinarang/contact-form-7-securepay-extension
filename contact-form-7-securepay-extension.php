<?php
/**
 * Plugin Name: Contact Form 7 - SecurePay Extension
 * Plugin URL: 
 * Description:  This plugin will integrate Secure Pay Button
 * Version: 1.0
 * Author: Mahendra Rajdhami
 * Author URI: https://mahendrarajdhami.wordpress.com
 * Developer: Miracle Interface Wordpress Team
 * Developer E-Mail: mahendrarajdhami@gmail.com
 * Text Domain: contact-form-7-extension
 * Domain Path: /languages
 * 
 */

/**
 * Register the [paypalsubmit] shortcode
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
** A base module for [paypalsubmit] - A submit button that will redirect to PayPal after form submit.
**/

/* Shortcode handler */

add_action('init', 'contact_form_7_securepay_submit', 11);

function contact_form_7_securepay_submit() {	
	if(function_exists('wpcf7_add_shortcode')) {
		wpcf7_add_shortcode( 'paypalsubmit', 'wpcf7_securepay_submit_shortcode_handler', false );		
	} else {
		 return; 		
	}
}

/**
  * Generate paypal redirection URL using parameters entered in tag 
  */

add_action('wp_head','wpcf7_securepay_location');
function wpcf7_securepay_location(){	?>
	<script>	
		var paypal_location = "";
		var paypal_url="";			
		function returnURL(url, itemamount, itemname, itemqty)
		{				
			var amount = 0;
			paypal_url = url;
			if(itemamount != "" && itemamount != undefined){
				var type = jQuery('#'+itemamount).attr('type');						
				if(type == 'text' || type == 'number' || type == 'range' || type=='hidden'){				
		        	amount = jQuery('#'+itemamount).val();
		        } else {		            	   	
		       		amount = jQuery('#'+itemamount+' :checked').val();		
		        }	
		    }
		    else
		    {
		    	amount = 0;
		    }		   
		    if(amount.indexOf('-') != -1){
		    	amount = amount.split('-');
		    	amount = amount[1].trim();
		    }
		    
	        /*------------------------------------------------------*/
	        var quantity = 0;	
	        if(itemqty != "" && itemqty != undefined){
				var type = jQuery('#'+itemqty).attr('type');
				if(type == 'text' || type == 'number' || type == 'range' || type=='hidden'){
		        	quantity = jQuery('#'+itemqty).val();		
		        } else {	       	   	
		       		quantity = jQuery('#'+itemqty+' :checked').val();
		        } 	  
		    } else {
				quantity = '1';
		    }   			
			/*------------------------------------------------------*/
			var item = '';
			if(itemname != "" && itemname != undefined )
			{
				var type = jQuery('#'+itemname).attr('type');			
				if(type == 'text' || type == 'number' || type == 'range' || type=='hidden'){				
		        	item = jQuery('#'+itemname).val();		
		        } else {	  
		       		item = jQuery('#'+itemname+' :checked').val();		
		        }
		    } else {
		    	item = "";
		    }

	    	if(amount != "" && amount != undefined) {					
				paypal_location = url + '&amount=' + amount + '&item_name=' + item + '&quantity=' + quantity;													
			} 						
		 }	
		jQuery(document).ready(function(){
			jQuery(document).on('mailsent.wpcf7', function () {	
				if(paypal_url != "" && paypal_location == "")
			    {			    	
			    	jQuery('.wpcf7-response-output').append('You are not redirected to PayPal as you have not configured PayPal Submit Button properly. <br>');
			    }
			    else if(paypal_location != "")
			    {
			    	window.location = paypal_location;
			    }
			});

			jQuery(document).on('mailfailed.wpcf7', function () {	
				jQuery('.wpcf7-response-output').append('You are not redirected to Secure Pay as you have not configured PayPal Submit Button properly. <br>');
				/*if(paypal_url != "" && paypal_location == "")
			    {			    	
			    	jQuery('.wpcf7-response-output').append('You are not redirected to PayPal as you have not configured PayPal Submit Button properly. <br>');
			    }
			    else if(paypal_location != "")
			    {
			    	window.location = paypal_location;
			    }*/
			});
		});			
	</script>
<?php
}

/**
  * Regenerate shortcode into PayPal submit button
  */

function wpcf7_securepay_submit_shortcode_handler( $tag ) {		
	$tag = new WPCF7_Shortcode( $tag );	
	$class = wpcf7_form_controls_class( $tag->type );	
	$atts = array();	
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
	$currencycode = $tag->get_option('currency');
	$successURL = $tag->get_option('return_url');
	$cancelURL = $tag->get_option('cancel_url');
    $returnURL = $tag->get_option('return_url');
    $itemqty = $tag->get_option('quantity');
    $itemamount = $tag->get_option('itemamount');
    $itemname = $tag->get_option('itemname');
    //
    $EPS_MERCHANT = $tag->get_option('EPS_MERCHANT');
    $EPS_TXNTYPE = $tag->get_option('EPS_TXNTYPE');
    $actionUrl = $tag->get_option('action_url');


	if(!empty($businessemail[0]))
	{
		$querystring = array(
						'business'=> $businessemail[0],
						'currency_code'=> (empty($currencycode[0])) ? 'USD' : $currencycode[0],						
						'return'=> (empty($successURL[0])) ? get_site_url() : $successURL[0],
						'cancel_return'=> (empty($cancelURL[0])) ? get_site_url() : $cancelURL[0],
						'notify_url'=> $returnURL[0]
					);

		$mode = $tag->has_option( 'sandbox' );
		$mode = (isset($mode) && !empty($mode)) ? 'sandbox.paypal' : 'paypal';

		$location = "https://www.".$mode.".com/us/cgi-bin/webscr?cmd=_xclick&".http_build_query($querystring);	
		$atts['onclick'] = 'returnURL("'.$location.'","'.$itemamount[0].'","'.$itemname[0].'","'.$itemqty[0].'");';
	}
	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

	if ( empty( $value ) )
		$value = __( 'Submit', 'contact-form-7' );

	$atts['type'] = 'submit';
	$atts['value'] = $value;

	$atts = wpcf7_format_atts( $atts );

	$html .= sprintf( '<input %1$s />', $atts );

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
?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>
<table class="form-table">
<tbody>
<tr><td colspan="2"><a href="https://opensource.zealousweb.com/shop/" target="_blank">
	<img src="<?php //echo bloginfo('wpurl').'/wp-content/plugins/contact-form-7-paypal-extension/assets/cf7pn.jpg';?>" width="540">
</a></td></tr>
<tr>
<td colspan="2"><b>NOTE: Please fill all required fields.</b></td>
</tr>
<tr>
	<td><code>Merchant ID</code> <?php echo '<font style="font-size:10px"> (Required)</font>';?><br />
	<input type="text" name="EPS_MERCHANT" placeholder="ABC0010" class="idvalue oneline option" /></td>

	<td><code>Transaction Type</code> <?php echo '<font style="font-size:10px"> (Required)</font>'; ?><br />
	<input type="text" name="EPS_TXNTYPE" class="classvalue oneline option" /></td>
</tr>

<tr>
	<td><?php echo esc_html( __( 'Transaction Password', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>'; ?><br />
	<input type="text" name="TRANSACTION_PASSWORD" class="oneline" /></td>
	<td><?php echo esc_html( __( 'Action Url', 'contact-form-7' ) );echo '<font style="font-size:10px"> (required)</font>';?><br />
	<input type="text" name="action_url" placeholder="https://api.securepay.com.au/test/directpost/authorise" class="oneline option" /></td>
</tr>
<tr>
<td><?php echo esc_html( __( 'Select Currency', 'contact-form-7' ) ); echo ' (Default "USD")';?><br />
	<select name="currencies" onchange="document.getElementById('currency').value = this.value;">
		<?php foreach($currency as $key=>$value) { ?>
			<option value="<?php echo $key;?>" <?php echo ($key == "USD")?'selected':'';?>><?php echo $value;?></option>
		<?php } ?>
	</select>
	<input type="hidden" value="" name="currency" id="currency" class="oneline option">
</td>
</tr>
<tr>	
	<td colspan="2"><hr><font color="blue"><i>Enter Contact Form 7 Field's ID for these 4 Secure Pay fields,<i></font></td>
</tr>
<tr>
	<td colspan="2">
	<table>
		<tr><td><?php echo esc_html( __( 'Canrd No.', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>'; ?></td>
			<td><input type="text" name="Card Number" class="oneline option"/></td>
		</tr>
		<tr><td><?php echo esc_html( __( 'Expiry Month', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>';?></td>
			<td><input type="text" name="EPS_EXPIRYMONTH" class="oneline option" /></td>
		</tr>
		<tr><td><?php echo esc_html( __( 'Expiry Year', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>'; ?></td>
			<td><input type="text" name="EPS_EXPIRYYEAR" class="oneline option" /></td>
		</tr>
		<tr><td><?php echo esc_html( __( 'CCV', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (required)</font>'; ?></td>
			<td><input type="text" name="EPS_CCV" class="oneline option" /></td>
		</tr>
	</table><hr>
</td>
</tr>
<tr>

<td><?php echo esc_html( __( 'Result  URL', 'contact-form-7' ) ); echo '<font style="font-size:10px"> (optional)</font>';?><br />
	<input type="text" name="result_url" class="oneline option" /></td>
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