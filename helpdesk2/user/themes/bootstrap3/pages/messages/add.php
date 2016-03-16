<?php
namespace sts;
use sts as core;

$site->set_title($language->get('Create Message'));
$site->set_config('container-type', 'container');

if (!$auth->can('send_private_messages')) {
    header('Location: ' . $config->get('address') . '/profile/');
    exit;
}

$users_array = $users->get();

if (isset($_POST['submit'])) {
    if ($_POST['subject'] != '') {
        if ((int) $_POST['user_id'] !== 0) {
            //send message
            $message_array['from_user_id']	= $auth->get('id');
            $message_array['user_id']		= (int) $_POST['user_id'];
            $message_array['subject']		= $_POST['subject'];
            $message_array['message']		= $_POST['body'];
            $message_array['date_sent']		= datetime();
            $messages->add($message_array);
        }
        header('Location: ' . $config->get('address') . '/profile/');
        exit;
    }
    else {
        $message = $language->get('Subject Empty');
    }
}

include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

    <form method="post" action="<?php echo core\safe_output($_SERVER['REQUEST_URI']); ?>">

        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3><?php echo safe_output($language->get('Messages')); ?></h3>
                    </div>


                    <div class="pull-right">
                        <button type="submit" name="submit" class="btn btn-success"><?php echo safe_output($language->get('Send')); ?></button>
                        <a href="<?php echo $config->get('address'); ?>/profile/" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>

                    </div>

                    <div class="clearfix"></div>
                </div>

            </div>
        </div>

        <div class="col-md-9">
            <?php if (isset($message)) { ?>
                <div class="alert alert-danger">
                    <a href="#" class="close" data-dismiss="alert">&times;</a>
                    <?php echo html_output($message); ?>
                </div>
            <?php } ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php echo safe_output($language->get('Create Message')); ?>
                </div>
                <div class="panel-body">
                    <p><?php echo safe_output($language->get('To')); ?><br />
                    <select name="user_id">
                        <?php foreach ($users_array as $user) { ?>
                        <option value="<?php echo core\safe_output($user['id']); ?>"<?php if (isset($_POST['user_id']) && ($_POST['user_id'] == $user['id'])) echo ' selected="selected"'; ?>><?php echo core\safe_output(ucfirst($user['name'])); ?></option>
                        <?php } ?>
                    </select>
                    </p>
                    <p><?php echo safe_output($language->get('Subject')); ?><br /><input type="text" size="30" name="subject" value="" /></p>
                </div>
                         <textarea class="wysiwyg_enabled" name="body" cols="80" rows="12"><?php if (isset($_POST['body'])) echo safe_output($_POST['body']); ?></textarea>
                <div class="panel-body">
                    <div class="pull-right"><button type="submit" name="submit" class="btn btn-success"><?php echo safe_output($language->get('Send')); ?></button></div>
                </div>
            </div>
        </div>

    </form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
