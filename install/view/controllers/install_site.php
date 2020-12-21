<p style=" font-size: 20px; font-weight:bold; text-align:center; width:100%; font-family:Tahoma; background:rgba( 73, 73, 73, 0.7); color: #d76243; margin: -2px 0px 0 -18px; border-bottom: 1px solid #e9eaeb;  line-height:46px; padding:0 18px 0 18px;" >Configuration</p>

<?php echo install_tpl_feedback(); ?>

<form method="post">
	<table class="form">
	    <tr style="color: #626262; font-size: 15px;"><th colspan="3">Site Details</th></tr>
	    <tr>
	       
	        <td class="value <?php echo install_tpl_feedback_flag('site_title'); ?>">
	           <input type="text" placeholder="Site Title" name="site_title" value="<?php echo @$_assign_vars['data']['site_title']; ?>" />
	        </td>
	        
	    </tr>
	    <tr>
	        
	        <td class="value <?php echo install_tpl_feedback_flag('site_tagline'); ?>">
	           <input type="text" placeholder="Short Description" name="site_tagline" value="<?php echo @$_assign_vars['data']['site_tagline']; ?>" />
	        </td>
	        
	    </tr>
	    <tr>
	        
	        <td class="value <?php echo install_tpl_feedback_flag('site_url'); ?>">
	           <input type="text" placeholder="Site URL" name="site_url" value="<?php echo @$_assign_vars['data']['site_url']; ?>" />
	        </td>
	        
	    </tr>
	    <tr>
	        
	        <td class="value <?php echo install_tpl_feedback_flag('site_path'); ?>">
	           <input type="text" placeholder="Site Path" name="site_path" value="<?php echo @$_assign_vars['data']['site_path']; ?>" />
            </td>
	        
	    </tr>
	    <tr style="color: #626262; font-size: 15px; padding-bottom: 24px;"><th colspan="3">AdminBoard Details</th> </tr>
<tr style="color: #626262; font-size: 12px; padding-bottom: 24px;"><th colspan="3">Important: admin email will be site email so it must be ( any@yourdomain.com ) for sending emails and notifications to users</th> </tr>
	    <tr>
	        
	        <td class="value <?php echo install_tpl_feedback_flag('admin_email'); ?>">
	           <input type="text" placeholder="Admin Email" name="admin_email" value="<?php echo @$_assign_vars['data']['admin_email']; ?>" />
	        </td>	     
	    </tr>
	    <tr>
	        
	        <td class="value <?php echo install_tpl_feedback_flag('admin_username'); ?>">
               <input type="text" placeholder="Admin Username" name="admin_username" value="<?php echo @$_assign_vars['data']['admin_username']; ?>" />
            </td>
	        
	    </tr>
	    <tr>
	        
	        <td class="value <?php echo install_tpl_feedback_flag('admin_password'); ?>">
	           <input type="text" placeholder="Admin Password" name="admin_password" value="<?php echo @$_assign_vars['data']['admin_password']; ?>" />
	        </td>
	        
	    </tr>
	</table>

	<p align="center"><input type="submit" value="Continue" style=" cursor:pointer; text-transform: uppercase; font-size: 13px; font-family: 'Tahoma'; font-weight: bold; color: #494949; /></p>

</form>