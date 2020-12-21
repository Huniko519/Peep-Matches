<p style="font-size: 20px; font-weight:bold; text-align:center; width:100%; font-family:Tahoma; background:rgba( 73, 73, 73, 0.7); color: #d76243; margin: -2px 0px 0 -18px; border-bottom: 1px solid #e9eaeb;  line-height:46px; padding:0 18px 0 18px;">Install plugins</p>

<table style=" font-size: 15px;" class="form"> <p style=" color: #626262; text-align: center;">All plugins included into software installed automatically. just plugins below not installed, if you want install it check it</p>

<form method="post">
<table class="plugin_table" style="padding-left: 119px;">
    <?php 
        foreach ($_assign_vars['plugins'] as $p) 
        {
            $plugin =  $p['plugin'];
            $auto = $p['auto'];
    ?>
        <tr <?php echo $auto ? 'style="display: none;"' : ''; ?>>
            <td width="32">
                <input type="checkbox" name="plugins[]" <?php echo $auto ? 'checked="checked"' : ''; ?> value="<?php echo $plugin['key']; ?>" id="<?php echo $plugin['key']; ?>">
            </td>
            <td>
                <div class="plugin_title">
                    <label for="<?php echo $plugin['key']; ?>"><?php echo $plugin['title']; ?></label>
                </div>
                
                <div class="plugin_desc">
                    <label for="<?php echo $plugin['key']; ?>"><?php echo $plugin['description']; ?></label>
                </div>
            </td>
        </tr>
    <?php } ?>
</table>

<p align="center"><input type="submit" value="Finish" style="cursor:pointer; text-transform: uppercase; font-size: 13px; font-family: 'Tahoma'; font-weight: bold; color: #494949;/></p>


</form>