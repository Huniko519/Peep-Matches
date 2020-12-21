<p style="font-size: 20px; font-weight:bold; text-align:center; width:100%; font-family:Tahoma; background:rgba( 73, 73, 73, 0.7); color: #d76243; margin: -2px 0px 0 -18px; border-bottom: 1px solid #e9eaeb;  line-height:46px; padding:0 18px 0 18px;">Database </p>

<?php echo install_tpl_feedback(); ?>
<form method="post">
    <table style=" font-size: 15px;" class="form"> <p style=" color: #626262; text-align: center;"> Please create a database and enter its details here. </p>

        <tr> 
            <td class="label">Host</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_host'); ?>">
               <input type="text" name="db_host" value="<?php echo @$_assign_vars['data']['db_host']; ?>" />
            </td>
            <td class="description">MySQL host and port (optionally). Example: <i>localhost</i> or <i>localhost:2083</i></td>
        </tr>
        <tr>
            <td class="label">DB User</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_user'); ?>">
               <input type="text" name="db_user" value="<?php echo @$_assign_vars['data']['db_user']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
        <tr>
            <td class="label">DB Password</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_password'); ?>">
               <input type="text" name="db_password" value="<?php echo @$_assign_vars['data']['db_password']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
        
        <tr>
            <td class="label">DB Name</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_name'); ?>">
               <input type="text" name="db_name" value="<?php echo @$_assign_vars['data']['db_name']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
        
        <tr>
            <td class="label">Table prefix</td>
            <td class="value <?php echo install_tpl_feedback_flag('db_prefix'); ?>">
               <input type="text" name="db_prefix" value="<?php echo @$_assign_vars['data']['db_prefix']; ?>" />
            </td>
            <td class="description"> </td>
        </tr>
    </table>

    <p align="center"><input type="submit" value="Continue" style="cursor:pointer; text-transform: uppercase; font-size: 13px; font-family: 'Tahoma'; font-weight: bold; color: #494949;/></p>

</form>