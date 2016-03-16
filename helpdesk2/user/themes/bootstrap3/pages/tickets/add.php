<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('New Ticket'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_tickets') && !$auth->can('tickets')) {
    header('Location: ' . $config->get('address') . '/');
    exit;
}

if (isset($_POST['add'])) {

    $add_array =
        array(
            'subject'			=> $_POST['subject'],
            'description'		=> $_POST['description'],
            'priority_id'		=> (int) $_POST['priority_id']
        );

    $add_array['user_id'] 		= $auth->get('id');
    $add_array['company_id'] 	= $auth->get('company_id');

    if (isset($_POST['department_id']) && ($_POST['department_id'] != '')) {
        $add_array['department_id']	= (int) $_POST['department_id'];
    }

    if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) {
        if (isset($_POST['user_id']) && ($_POST['user_id'] != '')) {
            $add_array['user_id']	= (int) $_POST['user_id'];
        }
        if (isset($_POST['assigned_user_id']) && ($_POST['assigned_user_id'] != '')) {
            $add_array['assigned_user_id']	= (int) $_POST['assigned_user_id'];
        }
        if (isset($_POST['cc']) && (!empty($_POST['cc']))) {
            $add_array['cc']	= $_POST['cc'];
        }
        if (isset($_POST['company_id']) && ($_POST['company_id'] != '')) {
            $add_array['company_id']	= (int) $_POST['company_id'];
        }
        if (isset($_POST['project_id']) && ($_POST['project_id'] != '')) {
            $add_array['project_id']	= (int) $_POST['project_id'];
        }
    }

    if (isset($_FILES['file']) && is_array($_FILES['file'])) {
        $add_array['files'] = $_FILES['file'];
    }

    foreach($_POST as $index => $value){
        if(strncasecmp($index, 'field-', 6) === 0) {
            $add_array[$index] = $value;
        }
    }

    $add_result = $tickets->add_ticket($add_array);

    if ($add_result['success']) {
        header('Location: ' . $config->get('address') . '/tickets/view/' . (int) $add_result['id'] . '/');
        exit;
    }
    else {
        $message = $add_result['message'];
    }
}

$priorities 	= $ticket_priorities->get(array('enabled' => 1));

if ($auth->can('manage_tickets')) {
    $departments	= $ticket_departments->get(array('enabled' => 1));
} else {
    $departments 	= $ticket_departments->get(array('enabled' => 1, 'get_other_data' => true, 'user_id' => $auth->get('id')));
}


include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
    <form method="post" enctype="multipart/form-data" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

        <div class="col-md-3">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h4><?php echo safe_output($language->get('Tickets')); ?></h4>
                    </div>
                    <div class="pull-right">
                        <button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add Ticket')); ?></button>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department') || $auth->can('tickets_view_assigned_department')) { ?>
                    <div class="panel-body">
                        <div class="pull-right">
                            <a href="#" id="show_extra_settings" class="btn btn-info"><?php echo safe_output($language->get('Show Extra Options')); ?></a>
                        </div>

                        

                    </div>
                <?php } ?>
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
                        <div class="pull-left">
                            <h4><?php echo safe_output($language->get('Add')); ?></h4>
                        </div>

                        <div class="clearfix"></div>

                    </div>
                    <div class="panel-body">
                        <div class="col-md-4">
                            <p>
                                <?php echo safe_output($language->get('Subject')); ?>
                                <input required type="text" name="subject" class="form-control" value="<?php if (isset($_POST['subject'])) echo safe_output($_POST['subject']); ?>" size="50" />
                            </p>

                        </div>


                        <div class="clearfix"></div>

                        <?php if (count($departments) > 1) { ?>
                            <div class="col-md-2">
                                <p><?php echo safe_output($language->get('Department')); ?>
                                    <?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) { ?>
                                        <select name="department_id" id="update_department_id">
                                            <?php foreach ($departments as $department) { ?>
                                                <option value="<?php echo (int) $department['id']; ?>"<?php if (isset($_POST['department_id']) && ($_POST['department_id'] == $department['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($department['name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } else { ?>
                                        <select name="department_id" id="update_department_id">
                                            <?php foreach ($departments as $department) { ?>
                                                <?php if ($department['is_user_member'] || $department['public_view']) { ?>
                                                    <option value="<?php echo (int) $department['id']; ?>"<?php if (isset($_POST['department_id']) && ($_POST['department_id'] == $department['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($department['name']); ?></option>
                                                <?php } ?>
                                            <?php } ?>
                                        </select>
                                    <?php } ?>
                                </p>
                            </div>
                        <?php } ?>


                        <?php $plugins->run('add_ticket_form_after_department'); ?>

                        <div class="col-md-2">
                            <?php echo safe_output($language->get('Priority')); ?><br>
                            <select name="priority_id">
                                <?php foreach ($priorities as $priority) { ?>
                                    <option value="<?php echo (int) $priority['id']; ?>"<?php if (isset($_POST['priority_id']) && ($_POST['priority_id'] == $priority['id'])) { echo ' selected="selected"'; } ?>><?php echo safe_output($priority['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            
                          <?php $site->display_custom_field_forms(); ?>

                                <?php if ($config->get('storage_enabled')) { ?>
                                <p><?php echo safe_output($language->get('Attach File')); ?></p>
                                <?php } ?>                          
                                <?php if ($config->get('storage_enabled')) { ?>
                                <div class="col-lg-4 nopad-left">
                                    <div class="pull-left"><input name="file[]" type="file" /></div>
                                    <div class="pull-right"><a href="#" id="add_extra_file"><span class="glyphicon glyphicon-plus"></span><?php echo safe_output($language->get(' Attach More')); ?></a></div>
                                    <div id="attach_file_area"></div>
                                </div>
                                <?php } ?>  
                                                  
                        </div>

                        <div class="clearfix"></div>

                        <div class="extra_settings">
                            <div class="form-group">
                                <?php if ($auth->can('manage_tickets') || $auth->can('tickets_view_assigned_department')) { ?>
                                    <div class="col-md-2">
                                        <p><?php echo safe_output($language->get('On Behalf Of')); ?><br />
                                        <select name="user_id" id="user_id">
                                            <option value=""></option>
                                            <?php if (isset($_POST['user_id'])) { ?>
                                                <option value="<?php echo (int) $_POST['user_id']; ?>" selected="selected"></option>
                                            <?php } ?>
                                        </select></p>
                                    </div>
                                    <div class="col-md-2">
                                        <p><?php echo safe_output($language->get('Assigned To')); ?><br />
                                        <select name="assigned_user_id" id="assigned_user_id">
                                            <option value=""></option>
                                            <?php if (isset($_POST['assigned_user_id'])) { ?>
                                                <option value="<?php echo (int) $_POST['assigned_user_id']; ?>" selected="selected"></option>
                                            <?php } ?>
                                        </select></p>
                                    </div>
                                    <div class="col-md-2">
                                        <p><?php echo safe_output($language->get('Carbon Copy')); ?>
                                            <i data-toggle="tooltip" data-placement="right" data-original-title="<?php echo safe_output($language->get('Allows you to Carbon Copy this ticket to other users e.g. user@example.com,user2@example.net. Note: CCed users will be able to view the entire ticket thread via the guest portal.')); ?>" class="glyphicon glyphicon-question-sign"></i>
                                            <br />
                                            <input type="text" name="cc" class="form-control" placeholder="user@example.com" value="<?php if (isset($_POST['cc'])) echo safe_output($_POST['cc']); ?>" size="50" />
                                        </p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <div class="clearfix"></div>
            </div>

            <div class="clearfix"></div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4><?php echo safe_output($language->get('Description')); ?></h4>
                </div>
                <div class="form-group">
                    <textarea class="wysiwyg_enabled" name="description" cols="80" rows="12"><?php if (isset($_POST['description'])) echo safe_output($_POST['description']); ?></textarea>

                    <div class="panel-body">
                            <div class="form-group">
                                <div class="pull-right">
                                    <button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add Ticket')); ?></button>
                                </div>
                            </div>



                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

    </form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
