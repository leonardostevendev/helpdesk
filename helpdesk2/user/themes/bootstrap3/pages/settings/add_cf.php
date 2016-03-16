<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Custom Fields'));
$site->set_config('container-type', 'container');

if (!$auth->can('manage_system_settings')) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

$cf_table = $_GET['table_access'];

$enabled = false;
if (class_exists($cf_table, false)) {
	$cf_class = new $cf_table();
	if ($cf_class->is_cf_enabled()) {
		$enabled = true;
	}
}

if (!$enabled) {
	header('Location: ' . $config->get('address') . '/');
	exit;
}

if (isset($_POST['add'])) {
	if (!empty($_POST['name'])) {
		$add_array['type']				= $_POST['type'];
		$add_array['name']				= $_POST['name'];
		$add_array['client_modify']		= 1;
		$add_array['enabled']			= $_POST['enabled'] ? 1 : 0;
		$add_array['allowed_values']	= '';

		$id = $cf_class->add_cf(array('columns' => $add_array));
				
		/*
		if ($add_array['type'] == 'dropdown') {
			foreach($_POST['dropdown_field'] as $index => $value){
				if (!empty($value)) {
					$ticket_custom_fields->add_field(array('ticket_field_group_id' => $id, 'value' => $value));
				}
			}
		}
		*/
		
		header('Location: ' . $config->get('address') . '/' . $cf_class->get_cf_settings_url());
		exit;
	}
	else {
		$message = $language->get('Name Empty');
	}
}



include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">

	<form method="post" action="<?php echo safe_output($_SERVER['REQUEST_URI']); ?>">

	<div class="col-md-3">
		<div class="panel panel-default">
            <div class="panel-heading">
                <div class="pull-left">
                    <h4><?php echo safe_output($language->get('Custom Fields')); ?></h4>
                </div>

                <div class="pull-right">
                    <button type="submit" name="add" class="btn btn-success"><?php echo safe_output($language->get('Add')); ?></button>
                    <a href="<?php echo $config->get('address'); ?>/<?php echo safe_output($cf_class->get_cf_settings_url()); ?>" class="btn btn-default"><?php echo safe_output($language->get('Cancel')); ?></a>
                </div>

                <div class="clearfix"></div>	
            </div>
            <div class="panel-body">
                <p><?php echo $language->get('Custom Fields allow you to add extra global fields to your tickets.'); ?></p>
                <h4><?php echo $language->get('Input Options'); ?></h4>
                <ul>
                    <li><?php echo $language->get('Text Input (single line of text).'); ?></li>
                    <li><?php echo $language->get('Text Area (multiple lines of text).'); ?></li>
                    <!--<li><?php echo $language->get('Dropdown box with options.'); ?></li>-->
                </ul>
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

			<div class="panel-body">								

				
                <p><?php echo $language->get('Name'); ?></p>
                <div class="pull-left"><input type="text" class="form-control" name="name" value="<?php if (isset($_POST['name'])) echo safe_output($_POST['name']); ?>" size="30" /></div>
                <div class="clearfix"></div><br>
				
				<p><?php echo $language->get('Enabled'); ?><br />
					<select name="enabled">
						<option value="0"<?php if (isset($_POST['enabled']) && $_POST['enabled'] == '0') { echo ' selected="selected"'; } ?>><?php echo $language->get('No'); ?></option>
						<option value="1"<?php if (isset($_POST['enabled']) && $_POST['enabled'] == '1') { echo ' selected="selected"'; } ?>><?php echo $language->get('Yes'); ?></option>
					</select>
				</p>
				
				<!--
				<p><?php echo $language->get('Guest & Submitter Can View/Edit'); ?><br />
					<select name="client_modify">
						<option value="0"<?php if (isset($_POST['client_modify']) && $_POST['client_modify'] == '0') { echo ' selected="selected"'; } ?>><?php echo $language->get('No'); ?></option>
						<option value="1"<?php if (isset($_POST['client_modify']) && $_POST['client_modify'] == '1') { echo ' selected="selected"'; } ?>><?php echo $language->get('Yes'); ?></option>
					</select>
				</p>
				-->
				
				<p><?php echo $language->get('Input Type'); ?><br />
					<select name="type" id="custom_field_type">
						<option value="textinput"<?php if (isset($_POST['type']) && $_POST['type'] == 'textinput') { echo ' selected="selected"'; } ?>><?php echo $language->get('Text Input'); ?></option>
						<option value="textarea"<?php if (isset($_POST['type']) && $_POST['type'] == 'textarea') { echo ' selected="selected"'; } ?>><?php echo $language->get('Text Area'); ?></option>
						<!--<option value="dropdown"<?php if (isset($_POST['type']) && $_POST['type'] == 'dropdown') { echo ' selected="selected"'; } ?>><?php echo $language->get('Drop Down'); ?></option>-->
						<option value="date"<?php if (isset($_POST['type']) && $_POST['type'] == 'date') { echo ' selected="selected"'; } ?>><?php echo $language->get('Date'); ?></option>
						<option value="datetime"<?php if (isset($_POST['type']) && $_POST['type'] == 'datetime') { echo ' selected="selected"'; } ?>><?php echo $language->get('Date & Time'); ?></option>
					</select>
				</p>
				
				<div id="dropdown_fields">
					<br />
					<h3><a name="add_dropdown"></a><?php echo $language->get('Dropdown Fields'); ?> <a id="add_dropdown_field" href="#add_dropdown" class="btn btn-info"><?php echo $language->get('Add'); ?></a></h3>
					<div class="dropdown_field">
						<p><?php echo $language->get('Option'); ?><br /><input type="text" class="form-control" name="dropdown_field[]" value="" size="30" /></p>
					</div>
					<div class="extra_dropdown_field"></div>
				</div>

			</div>
			<div class="clearfix"></div>	

		</div>
			
		<div class="clearfix"></div>

	</div>

	</form>
</div>
<?php include(core\ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>