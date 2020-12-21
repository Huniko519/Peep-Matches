<p style=" font-size: 20px; font-weight:bold; text-align:center; width:100%; font-family:Tahoma; background:rgba( 73, 73, 73, 0.7); color: #d76243; margin: -2px 0px 0 -18px; border-bottom: 1px solid #e9eaeb;  line-height:46px; padding:0 18px 0 18px;" > Installing </p>
<?php echo install_tpl_feedback(); ?>

<?php
if ( $_assign_vars['dirs'] )
{
?>
<div class="feedback_msg error">
	&bull; You need to make folder below to be writable by set chmod to "0777" permissions for these files and folders:</br> - storage-1</br>
  - storage-2</br>
  - static</br>
  - cachy/bodycache
</div>

<ul class="directories">
    <?php foreach ($_assign_vars['dirs'] as $dir) { ?>
	    <li><?php echo $dir; ?></li>
	<?php } ?>
</ul>

<hr />
<?php
}
?>
<form method="post">
    <div style="<?= $_assign_vars['isConfigWritable'] ? 'display: none;' : ''; ?>" >
        <p>&bull; Please copy and paste this code replacing the existing one into <b>includes/config.php</b> file.<br />Make sure you do not have any whitespace before and after the code.</p>
        <textarea rows="5" name="configContent" class="config" style="height: 400px;" onclick="this.select();"><?php echo $_assign_vars['configContent']; ?></textarea>
        <input type="hidden" name="isConfigWritable" value="<?= $_assign_vars['isConfigWritable'] ? '1' : '0'; ?>" />
    </div>
    <p style="text-align: center; color: #626262; padding-top: 19px; font-size:16px;">Your Database and site basic system installed</p>
    <p align="center"><input type="submit" value="Continue" name="continue" style=" margin-bottom: 19px; cursor:pointer; text-transform: uppercase; font-size: 13px; font-family: 'Tahoma'; font-weight: bold; color: #494949; /></p>
</form>