<p style=" font-size: 20px; font-weight:bold; text-align:center; width:100%; font-family:Tahoma; background:rgba( 73, 73, 73, 0.7); color: #d76243; margin: -2px 0px 0 -18px; border-bottom: 1px solid #e9eaeb;  line-height:46px; padding:0 18px 0 18px;"> Server Requirements</p>

<p class="red">
	Your host server doesn't support our software requirements:
</p>

<ul class="peep_regular">
<!-- PHP version -->
<?php if ( !empty($_assign_vars['fails']['php']['version']) ) { $requiredVersion = $_assign_vars['fails']['php']['version']; ?>
    
        <li>
               Required PHP version: <b class="high"><?php echo $requiredVersion ?></b> or higher <span class="small">(currently <b><?php echo $_assign_vars['current']['php']['version']; ?></b>)</span>
        </li>
    
<?php } ?>

<!-- PHP extensions -->
<?php if ( !empty($_assign_vars['fails']['php']['extensions']) ) { ?>
    <?php foreach ($_assign_vars['fails']['php']['extensions'] as $requiredExt) { ?>
        
        <li>
               <b class="high"><?php echo $requiredExt; ?></b> PHP extension not installed
        </li>    
            
    <?php } ?>
<?php } ?>

<!-- INI Configs -->
<?php if ( !empty($_assign_vars['fails']['ini']) ) { ?>
    
        <?php foreach ($_assign_vars['fails']['ini'] as $iniName => $iniValue) { ?>
        
            <li>
                   <span class="high"><?php echo $iniName; ?></span> must be <b class="high"><?php echo $iniValue ? 'on' : 'off'; ?></b>
                   <span class="small">(currently <b><?php echo $_assign_vars['current']['ini'][$iniName] ? 'on' : 'off'; ?></b>)</span>
            </li>    
                
        <?php } ?>
    
<?php } ?>

<!-- GD version -->
<?php if ( !empty($_assign_vars['fails']['gd']['version']) ) { $requiredVersion = $_assign_vars['fails']['gd']['version']; ?>
    
        <li>
               Required <span class="high">GD library</span> version: <b class="high"><?php echo $requiredVersion ?></b> or higher 
               <span class="small">(currently <b><?php echo $_assign_vars['current']['gd']['version']; ?></b>)</span>
        </li>
    
<?php } ?>

<!-- GD support -->
<?php if ( !empty($_assign_vars['fails']['gd']['support']) ) { $requiredSupportType = $_assign_vars['fails']['gd']['support']; ?>
    
        <li>
               <b class="high"><?php echo $requiredSupportType ?></b> required for <span class="high">GD library</span>
        </li>
    
<?php } ?>

</ul>

<p>
	Be sure that your host server meet our software requirements then try installing it again ( you can contact your hosting provider for this ). for more information <a href="http://www.peepdev.com/hosting/" target="_blank">Check Software Host Requierments or choose from our recommended host</a>
</p>