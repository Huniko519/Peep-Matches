<p style="font-size: 20px; font-weight:bold; text-align:center; width:100%; font-family:Tahoma; background:rgba( 73, 73, 73, 0.7); color: #d76243; margin: -2px 0px 0 -18px; border-bottom: 1px solid #e9eaeb;  line-height:46px; padding:0 18px 0 18px;">License Check </p>

<?php

$purchase_info = "";

function verify_envato_purchase_code($user, $api_key, $code_to_verify) {
     
    // Open cURL channel
    $ch = curl_init();
      
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "https://marketplace.envato.com/api/edge/peepdevco/mxw3u64ab6a3d15ylrbl8rtoaly51n95/verify-purchase:". $code_to_verify .".json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.66 Safari/537.36");
      
    // Decode returned JSON
    $output = json_decode(curl_exec($ch), true);
      
    // Close Channel
    curl_close($ch);
      
    // Return output
    return $output;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 	
 	// Get the key
	$user_key = strip_tags($_POST["e_user"]);
	$api_key = strip_tags($_POST["e_api"]);
	$purchase_key = strip_tags($_POST["e_code"]);
	
	if (!empty($purchase_key)) {
	
	// Verify the key
	$purchase_data = verify_envato_purchase_code($user_key, $api_key, $purchase_key);
	
	// Handle the response
	if (isset($purchase_data['verify-purchase']['buyer'])) {
	    $purchase_info = '<hr><p><strong class="success_valid">Valid License Key!</strong><div class="infos">';

	   
	    $purchase_info .= '<div>Item Name: ' . $purchase_data['verify-purchase']['item_name'] . '</div>';
	    $purchase_info .= '<div>Buyer: ' . $purchase_data['verify-purchase']['buyer'] . '</div>';
	    $purchase_info .= '<div>License: ' . $purchase_data['verify-purchase']['licence'] . '</div>';
	    
	    $purchase_info .= '</div><a href="configuration"><div class="install_go_btn">Install Now</div></a></p>'; 
	} else {
		$purchase_info .= '<p class="invalid_error"><strong>Invalid license key.</strong><br/>Do not use any nulled or free share for our script<br/>We advice you to purchase a valid license if you want your site be stable and grow .</p>';
	}
	
	// Print error because some fields are missing
	}
}
?>




   
             
              <h3 class="before-heading" style="font-size:15px; margin-bottom:20px;">Before proceeding to install proccess we need to verify your purchase code first .</h3>
             <center> <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
              <div class="row">
              
	            <div class="col-md-4">
	              	<div class="input-group">
	              	  <input type="text" name="e_code" class="form-control" placeholder="Put Purchase Code Here">
	              	  <span class="input-group-btn">
	              	    <button type="submit" class="ver-btn">Check</button>
	              	  </span>
	              	</div>
	            </div>
	           </div>
	           
              </form><center>
              <?php echo $purchase_info;?>
            