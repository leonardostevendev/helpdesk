<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Profile'));
$site->set_config('container-type', 'container');

$items = $messages->get(array('to_from_user_id' => $auth->get('id')));

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="pull-left">
                    <h3><?php echo safe_output($language->get('Profile')); ?></h3>
                </div>

                <div class="pull-right">
                    <p><a href="<?php echo safe_output($config->get('address')); ?>/profile/edit/" class="btn btn-warning"><?php echo safe_output($language->get('Edit')); ?></a></p>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="panel-body">
                <label class="left-result"><?php echo safe_output($language->get('Name')); ?></label>
                <p class="right-result">
                    <?php echo safe_output(ucwords($auth->get('name'))); ?>
                </p>
                <div class="clearfix"></div>

                <label class="left-result"><?php echo safe_output($language->get('Username')); ?></label>
                <p class="right-result">
                    <?php echo safe_output($auth->get('username')); ?>
                </p>
                <div class="clearfix"></div>

                <label class="left-result"><?php echo safe_output($language->get('Email')); ?></label>
                <p class="right-result">
                    <?php echo safe_output($auth->get('email')); ?>
                </p>

                <div class="clearfix"></div>
                <?php if ($config->get('gravatar_enabled')) { ?>
                <label class="left-result"><?php echo safe_output($language->get('Gravatar')); ?></label>
                <p class="right-result">
                    <?php $gravatar->setEmail($auth->get('email')); ?>
                    <img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
                </p>
                <?php } ?>
                <div class="clearfix"></div>

                <?php if ($config->get('facebook_enabled')) { ?>
                <br />
                <div class="pull-right">
                    <a href="<?php echo safe_output($config->get('address')); ?>/profile/facebook/" class="btn btn-info"><?php echo safe_output($language->get('Link Your Facebook Profile')); ?></a>
                </div>
                <?php } ?>
                <div class="clearfix"></div>
            </div>


        </div>
    </div>

    <div class="col-md-9">

        <?php if (isset($message)) { ?>
            <div id="content">
                <?php echo message($message); ?>
                <div class="clear"></div>
            </div>
        <?php } ?>

        <!--<div class="well well-sm">-->
            <div class="pull-left">
                <h4><?php echo safe_output($language->get('Private Messages')); ?></h4>
            </div>

            <div class="pull-right">

                <?php if ($auth->can('send_private_messages')) { ?>
                    <p><a href="<?php echo safe_output($config->get('address')); ?>/messages/add/" class="btn btn-info"><?php echo safe_output($language->get('New')); ?></a></p>
                <?php } ?>
            </div>

            <div class="clearfix"></div>


            <?php if (!empty($items)) { ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?php echo safe_output($language->get('Subject')); ?></th>
                            <th><?php echo safe_output($language->get('To')); ?></th>
                            <th><?php echo safe_output($language->get('From')); ?></th>
                            <th><?php echo safe_output($language->get('Date')); ?></th>
                            <th><?php echo safe_output($language->get('Unread')); ?></th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        $i = 0;
                        foreach ($items as $item) {
                        ?>
                        <tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
                            <td class="centre"><a href="<?php echo $config->get('address'); ?>/messages/view/<?php echo safe_output($item['id']); ?>/"><?php echo safe_output(ucfirst($item['subject'])); ?></a></td>
                            <td class="centre"><?php echo safe_output(ucfirst($item['to_name'])); ?></td>
                            <td class="centre"><?php echo safe_output(ucfirst($item['from_name'])); ?></td>
                            <td class="centre"><?php echo safe_output(date('D, d M Y g:i A', strtotime($item['date_added']))); ?></td>
                            <td class="centre"><?php echo (int) $item['unread_count']; ?></td>
                        </tr>
                        <?php $i++; } ?>

                    </tbody>
                </table>
            <?php } else { ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php echo safe_output($language->get('No Messages')); ?>
                </div>
            </div>
            <?php } ?>

            <div class="clearfix"></div>

            <?php if ($auth->can('api_access') && $config->get('api_enabled')) { ?>
                <script type="text/javascript" src="<?php echo $config->get('address'); ?>/system/js/user_api.js"></script>

                <?php $items = $user_api_keys->get(array('where' => array('user_id' => $auth->get('id')))); ?>

                <div class="pull-left">
                    <h4><?php echo safe_output($language->get('API Keys')); ?></h4>
                </div>

                <div class="pull-right">

                    <p><a href="<?php echo safe_output($config->get('address')); ?>/profile/add_api_key/" class="btn btn-info"><?php echo safe_output($language->get('Add')); ?></a></p>
                </div>

                <div class="clearfix"></div>

                <?php if (!empty($items)) { ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo safe_output($language->get('Name')); ?></th>
                                <th><?php echo safe_output($language->get('Key')); ?></th>
                                <th><?php echo safe_output($language->get('Examples')); ?></th>
                                <th><?php echo safe_output($language->get('Delete')); ?></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $i = 0;
                            foreach ($items as $item) {
                            ?>
                            <tr <?php if ($i % 2 == 0 ) { echo 'class="switch-1"'; } else { echo 'class="switch-2"'; }; ?>>
                                <td class="centre"><?php echo safe_output($item['name']); ?></td>
                                <td class="centre"><?php echo safe_output($item['key']); ?></td>
                                <td class="centre"><a href="<?php echo safe_output($config->get('address')); ?>/profile/view_api_key/<?php echo (int) $item['id']; ?>/"><?php echo safe_output($language->get('View')); ?></a></td>
                                <td class="centre" id="keyexisting-<?php echo (int) $item['id']; ?>"><a href="#custom" id="delete_existing_user_api_key_item"><img src="<?php echo $config->get('address'); ?>/user/themes/<?php echo safe_output(CURRENT_THEME); ?>/images/icons/delete.png" alt="Delete API Key" /></a></td>
                            </tr>
                            <?php $i++; } ?>

                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="alert alert-success"><?php echo safe_output($language->get('No Keys')); ?></div>
                <?php } ?>

                <div class="clearfix"></div>
            <?php } ?>

        <!--</div>-->

        <?php $plugins->run('profile_content_finish'); ?>
    </div>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
