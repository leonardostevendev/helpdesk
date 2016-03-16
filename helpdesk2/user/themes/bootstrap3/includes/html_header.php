<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$pm_unread_count = 0;
if ($auth->logged_in() && $config->get('database_version') > 9) {
    $pm_unread_count = $messages->unread_count(array('user_id' => $auth->get('id')));
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo safe_output($site->get_title()); ?></title>

    <meta name="viewport" content="width=device-width, maximum-scale=1.0" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />

<link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/css/tooltip.css" rel="stylesheet">


    <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/css/responsive-tables.css" rel="stylesheet">

    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/css/bootstrap-custom.css')) { ?>
        <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/css/bootstrap-custom.css" rel="stylesheet">
    <?php } ?>
    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/stylesheets/theme.css')) { ?>
        <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/stylesheets/theme.css" rel="stylesheet">
    <?php } ?>
    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/css/font-awesome.min.css')) { ?>
        <link href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/css/font-awesome.min.css" rel="stylesheet">
    <?php } ?>

    <link rel="shortcut icon" href="<?php echo $config->get('address'); ?>/favicon.ico" />

    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/jquery.js"></script>

    <!-- JQuery autocollapse navbar -->
    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/js/autocollapse.js')) { ?>
        <script src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/js/autocollapse.js" rel="stylesheet"></script>
    <?php } ?>

    <!-- Masonry -->
    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/js/autocollapse.js')) { ?>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/js/masonry.js"></script>
    <?php } ?>

    <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/js/bootstrap.min.js')) { ?>
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/js/bootstrap.min.js"></script>
    <?php } ?>

    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/respond.min.js"></script>

    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/js/moment.min.js"></script>
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
    <link href="<?php echo $config->get('address'); ?>/system/libraries/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

    <script type="text/javascript">
        var sts_base_url 			= "<?php echo safe_output($config->get('address')); ?>";
        var sts_current_theme		= "<?php echo safe_output(CURRENT_THEME); ?>";
        var sts_current_theme_sub	= "<?php echo safe_output(CURRENT_THEME_SUB); ?>";
    </script>

    <?php if ($auth->logged_in()) { ?>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/priorities.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/departments.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/tickets.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/add_ticket.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/custom_fields.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/user_selector.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/users.js"></script>
    <?php } ?>

    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/modal.js"></script>
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/view_ticket.js"></script>

    <?php if ($config->get('html_enabled')) { ?>
		<!--
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/redactor/fontsize.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/redactor/fontfamily.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/redactor/fullscreen.js"></script>
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/redactor/fontcolor.js"></script>
        <link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/redactor/css/redactor.css" />
		-->
        <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/redactor/redactor.min.js"></script>
        <link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/redactor/redactor.css" />

        <script type="text/javascript">
        $(document).ready(
            function()
            {
                $('.wysiwyg_enabled').redactor({
                    focus: false,
                    buttonSource: true,
                    minHeight: 100,
                    toolbarFixed: false
                });
                var contr = 0;
				/*
                $('.wysiwyg_enabled').redactor({
                    focus: false,
                    buttonSource: true,
                    buttons: [
                        'html', '|', 'formatting', '|', 'bold', 'italic', 'deleted', '|',
                        'unorderedlist', 'orderedlist', 'outdent', 'indent', '|',
                        'image', 'table', 'link', '|',
                        'alignleft', 'aligncenter', 'alignright', 'justify', '|',
                        'horizontalrule'
                    ],
                    plugins: ['fontsize', 'fontfamily', 'fontcolor'],
                    minHeight: 100,
                    toolbarFixed: false
                });
				*/
            }
        );
        </script>
    <?php } ?>

    <!-- Select2 -->
    <link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/select2/select2.css" />
    <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/libraries/select2/select2.min.js"></script>

    <!-- Colorbox -->

    <link rel="stylesheet" href="<?php echo $config->get('address'); ?>/system/libraries/colorbox/colorbox.css" />
    <script src="<?php echo $config->get('address'); ?>/system/libraries/colorbox/jquery.colorbox-min.js"></script>

    <script type="text/javascript">
    $(document).ready(function () {
        <?php if (!$site->get_config('disable_custom_select')) { ?>
            //Custom Selectmenu
            $('select').select2({
                width: 'resolve',
                allowClear: true
            });
        <?php } ?>

        //tooltip
        $(".glyphicon-question-sign").tooltip({html: true});
        //popover
        $('.popover-item').popover().click(function(e){e.preventDefault();});

    });
    </script>

    <!-- Adds styling fixes to header if running old themes -->
    <?php if (CURRENT_THEME == 'bootstrap3' ) { ?>
        <style>
                <!-- add custom style fixes here -->
        </style>
    <?php } ?>

    <!-- Adds styling to non v6 themes -->
    <?php if (CURRENT_THEME_SUB !== 'v6dg' || 'connelgriffin' || 'v6' || 'v6morning') { ?>
        <style>
        </style>
    <?php } ?>


    <!-- 57x57 (precomposed) for iPhone 3GS, 2011 iPod Touch and older Android devices -->
    <link rel="apple-touch-icon-precomposed" href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/img/apple-touch-icon-precomposed.png">

    <!-- 72x72 (precomposed) for 1st generation iPad, iPad 2 and iPad mini -->
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/img/apple-touch-icon-72x72-precomposed.png">

    <!-- 114x114 (precomposed) for iPhone 4, 4S, 5 and 2012 iPod Touch -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/img/apple-touch-icon-114x114-precomposed.png">

    <!-- 144x144 (precomposed) for iPad 3rd and 4th generation -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/img/apple-touch-icon-144x144-precomposed.png">

    <?php $plugins->run('html_header'); ?>

</style>

<script type="text/javascript">
   var sts_base_url = "http://pegui.edu.co/helpdesk2";
</script>
<link rel="stylesheet" type="text/css" href="http://pegui.edu.co/helpdesk2/user/plugins/livechat/css/client.css" />
<!--<script type="text/javascript" src="http://pegui.edu.co/helpdesk2/system/libraries/js/jquery.js"></script>-->
<script type="text/javascript" src="http://pegui.edu.co/helpdesk2/system/libraries/js/jquery.cookie.js"></script>
<script type="text/javascript" src="http://pegui.edu.co/helpdesk2/user/plugins/livechat/js/loader.js"></script>

<script>
jQuery(document).ready(function(){
    jQuery('[data-toggle="tooltip"]').tooltip();
});
</script>

<style>
.catedra-tooltip + .tooltip > .tooltip-inner {background-color: #5197D2; font-size: 1.7em; text-align: left; padding: 8px;}
.catedra-tooltip + .tooltip > .tooltip-arrow { border-top-color: #5197D2;}	
</style>

</head>

<body>
<div style="width: 100%; background-image: url('http://pegui.edu.co/helpdesk/helpdesk-bg.jpg'); background-size: 100%; padding-top:15.5%; height: 0; background-repeat: no-repeat;"></div>

    <?php $plugins->run('body_header'); ?>
    <?php if (CURRENT_THEME_SUB == 'chrome' || CURRENT_THEME_SUB == 'sesamo') { ?>
        <nav class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
    <?php } else { ?>
        <nav id="autocollapse" class="navbar navbar-default navbar-fixed-top" role="navigation">
    <?php } ?>

        <div class="navbar-header">
            <a class="navbar-brand <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/img/headimg-sm.png')) { ?>smallimage<?php } ?> <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/img/headimg.png')) { ?>largeimage<?php } ?>" href="<?php echo $config->get('address'); ?>/">
                <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/img/headimg.png')) { ?>
                    <img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/img/headimg.png" rel="stylesheet">
                <?php } ?>
                <?php if (file_exists(THEMES . '/' . CURRENT_THEME . '/sub/' . CURRENT_THEME_SUB . '/img/headimg-sm.png')) { ?>
                    <img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/sub/<?php echo safe_output(CURRENT_THEME_SUB); ?>/img/headimg-sm.png" rel="stylesheet">
                <?php } ?>
                <?php echo safe_output($config->get('name')); ?>
            </a>

            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>


        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <?php if (!($auth->logged_in()) && !($config->get('display_dashboard'))) { ?>
            <li class="navbar-right"><button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal">
              Administración
            </button></li>
            
            <?php } ?>
            </ul>
            <ul class="nav navbar-nav">            
            

                <?php if ($auth->logged_in() && $config->get('display_dashboard')) { ?>
                    <li<?php if ($url->get_action() == 'dashboard') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/dashboard/"><span class="glyphicon glyphicon-th-large"></span> <?php echo safe_output($language->get('Dashboard')); ?></a></li>
                <?php } ?>

                <?php $plugins->run('html_header_nav_start'); ?>

                <?php if ($auth->logged_in()) { ?>
                    <?php if ($auth->can('tickets')) { ?>
                        <li<?php if ($url->get_action() == 'tickets') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/tickets/"><span class="glyphicon glyphicon-list"></span> <?php echo safe_output($language->get('Tickets')); ?></a></li>
                    <?php } ?>
                    <?php if ($auth->can('manage_users')) { ?>
                        <li<?php if ($url->get_action() == 'users') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/users/"><span class="glyphicon glyphicon-user"></span> <?php echo safe_output($language->get('Users')); ?></a></li>
                    <?php } ?>
                    <?php if ($auth->can('manage_system_settings')) { ?>
                        <li class="dropdown<?php if ($url->get_action() == 'settings' || $url->get_action() == 'logs') echo ' active'; ?>">
                            <a class="dropdown-toggle" data-toggle="dropdown" data-target="#settings" href="<?php echo $config->get('address'); ?>/settings/"><span class="glyphicon glyphicon-cog"></span> <?php echo safe_output($language->get('Settings')); ?> <strong class="caret"></strong></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo $config->get('address'); ?>/settings/"><?php echo safe_output($language->get('General')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/settings/api/"><?php echo safe_output($language->get('API')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/settings/authentication/"><?php echo safe_output($language->get('Authentication')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/settings/permissions/"><?php echo safe_output($language->get('Permissions')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/settings/email/"><?php echo safe_output($language->get('Email')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/settings/tickets/"><?php echo safe_output($language->get('Tickets')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/settings/plugins/"><?php echo safe_output($language->get('Plugins')); ?></a></li>
                                <li><a href="<?php echo $config->get('address'); ?>/logs/"><?php echo safe_output($language->get('Logs')); ?></a></li>
                                <?php $plugins->run('html_header_nav_settings'); ?>
                            </ul>
                        </li>
                    <?php } ?>
                <?php } else { ?>
                    <li<?php if ($url->get_action() == 'login') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/"><span class="glyphicon glyphicon-home"></span> <?php echo safe_output($language->get('Home')); ?></a></li>
                    <?php if ($config->get('guest_portal')) { ?>
                        <li<?php if ($url->get_action() == 'guest') echo ' class="active"'; ?>><a href="<?php echo $config->get('address'); ?>/guest/"><span class="glyphicon glyphicon-plane"></span> <?php echo safe_output($language->get('Guest Portal')); ?></a></li>
                    <?php } ?>
                <?php } ?>

                <?php $plugins->run('html_header_nav_finish'); ?>
            </ul>
            <?php if ($auth->logged_in()) { ?>
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-star"></span> <?php echo safe_output(ucwords($auth->get('name'))); ?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo $config->get('address'); ?>/profile/"><span class="glyphicon glyphicon-user"></span> <?php echo safe_output($language->get('Profile')); ?></a></li>
                            <?php $plugins->run('html_header_nav_profile'); ?>
                            <li class="logout-button"><a href="<?php echo $config->get('address'); ?>/logout/"><span class="glyphicon glyphicon-eject"></span> <?php echo safe_output($language->get('Logout')); ?></a></li>

                        </ul>
                    </li>
                </ul>
            <?php } ?>
        </div><!--/.nav-collapse -->

    </nav>
<div id="lcs"><a href="#" id="lcs_start_chat">Start Chat</a></div>
    <div class="<?php echo safe_output($site->get_config('container-type')); ?>">

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Administracion</h4>
      </div>
      <div class="modal-body">
        <form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>" role="form" id="formlogin">
            <?php if (!empty($login_message)) { ?>
                <div class="alert alert-success"><?php echo html_output($login_message); ?></div>
            <?php } ?>

            <?php if (isset($message) && !$auth->logged_in()) { ?>
                <script type="text/javascript">$('#myModal').modal('show')</script>
            <?php } ?>
                <div class="alert alert-danger">
                    <?php if(html_output($message)){ echo html_output($message);}?>
                </div>
           

            <div class="alert alert-warning"><?php echo safe_output($language->get('All login attempts are logged.')); ?></div>

            <div class="well well-sm login-box">

                <h2><?php echo safe_output($language->get('Login')); ?>&nbsp;&ndash;&nbsp;<?php echo safe_output($config->get('name')); ?></h2>

                <div class="form-group">
                        <p><input class="form-control" type="text" name="username" placeholder="<?php echo safe_output($language->get('Username')); ?>"></p>
                        <p><input class="form-control" type="password" name="password" placeholder="<?php echo safe_output($language->get('Password')); ?>"></p>
                        <div class="clearfix"></div>

                        <button type="submit" name="submit" id="btnSubmit" class="btn btn-primary"><?php echo safe_output($language->get('Login')); ?></button>

                    <div class="clearfix"></div>
                    <?php if ($config->get('facebook_enabled')) { ?>
                            <?php $loginUrl = $auth_facebook->get_login_url(array('url' => $config->get('address') . '/login/')); ?>
                            <a href="<?php echo safe_output($loginUrl); ?>" class="btn facebook-login"><?php echo safe_output($language->get('Login with Facebook')); ?></a>
                    <?php } ?>



                <div class="clearfix"></div>
                </div><!-- form-group -->
                <div class="forgot">
                    <!--<div class="col-lg-6" id="test">
                        <?php if ($config->get('registration_enabled')) { ?>
                            <a href="<?php echo safe_output($config->get('address')) . '/register/'; ?>" class="btn btn-sm btn-default"><?php echo safe_output($language->get('Create Account')); ?></a>
                        <?php } ?>
                    </div>-->
                    <div class="col-lg-6">
                        <a href="<?php echo safe_output($config->get('address')) . '/forgot/'; ?>" class="btn btn-sm btn-default"><?php echo safe_output($language->get('Forgot Password')); ?></a>
                    </div>
                    <div class="col-md-6">
                        <?php if ($config->get('guest_portal')) { ?>
                            <p><a href="<?php echo safe_output($config->get('address')) . '/guest/'; ?>" class="btn btn-sm btn-default"><?php echo safe_output($language->get('Create Ticket As Guest')); ?></a></p>
                        <?php } ?>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnLogin">Entrar</button>
      </div>
    </div>
  </div>
</div>
