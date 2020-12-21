<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="{$page_kw|default:$_var.page.keywords}" />
    <meta name="description" content="{$page_desc|default:$_var.page.description}" />
    <title><?php echo $_assign_vars['pageTitle']; ?></title>
    <link rel="StyleSheet" type="text/css" href="<?php echo $_assign_vars['pageStylesheetUrl']; ?>" />
</head>

<body>
    <div class="wrapper">
        <div class="body_wrapper">
            <div class="body_top">
            <h1><?php echo $_assign_vars['pageHeading']; ?></h1>
            </div>
            <div class="body">
 
                <div class="content">
                    <div class="clearfix">
<?php echo $_assign_vars['pageSteps']; ?> 
                       
                    <?php echo $_assign_vars['pageBody']; ?>
                </div>
                <div class="body_bottom">
                <div class="footer_promo">
<div class="provider_copyright">RG93bmxvYWRlZCBmcm9tIENPREVMSVNULkND</div>
</div>
            </div>

           </div>
        </div>
    </div>
</body>
</html>