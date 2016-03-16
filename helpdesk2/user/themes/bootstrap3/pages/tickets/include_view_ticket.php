<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

?>
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="pull-left">
            <h1 class="panel-title"><?php echo safe_output($ticket['subject']); ?></h1>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        <?php if ($config->get('gravatar_enabled')) { ?>
            <div class="pull-right gravatar">
                <?php $gravatar->setEmail($ticket['owner_email']); ?>
                <img src="<?php echo $gravatar->getUrl(); ?>" alt="Gravatar" />
            </div>
        <?php } ?>
        <?php if ($ticket['html'] == 1) { ?>
            <?php echo html_output($ticket['description']); ?>
        <?php } else { ?>
            <p><?php echo nl2br(safe_output($ticket['description'])); ?></p>
        <?php } ?>

        <div class="clearfix"></div>
        <br />
        <?php $site->view_custom_field_values(array('ticket' => $ticket)); ?>
    </div>
    <div class="panel-footer">
            <?php if ($auth->can('manage_tickets') == 2 && !empty($ticket['email_data'])) { ?>
                <div class="pull-left">
                <a class="btn btn-default btn-xs" href="<?php echo $config->get('address'); ?>/tickets/view_email/<?php echo (int) $ticket['id']; ?>/" title="View email data"><span class="glyphicon glyphicon-inbox"></span></a>
                </div>
            <?php } ?>

        <?php if ($auth->can('manage_tickets') || $auth->can('tickets_carbon_copy_reply')) { ?>
            <?php if (!empty($ticket['cc'])) { ?>
                <?php $cc = unserialize($ticket['cc']); ?>
                <div class="pull-left">
                    <a href="#" class="popover-item" data-html="true"
                        data-content="
                        <ul><?php foreach($cc as $cc_item) { ?>
                            <li><?php echo safe_output($cc_item); ?></li><?php } ?>
                        </ul>"
                        data-title="<?php echo safe_output($language->get('Carbon Copied Email Addresses')); ?>">
                        <span class="label label-success"><?php echo safe_output($language->get('Carbon Copied')); ?></span>
                    </a>
                </div>
                <?php } ?>
        <?php } ?>

            <?php
            $files = $tickets->get_files(array('id' => $ticket['id'], 'private' => 0));

            if (!empty($files)) { ?>
            <div class="pull-left">
                <a class="custom_modal btn btn-default btn-xs" data-href="<?php echo $config->get('address'); ?>/tickets/view_files_modal/<?php echo (int) $ticket['id']; ?>/"><?php echo safe_output($language->get('Attachments')); ?></a>

                <?php foreach($files as $file) { ?>
                    <a href="<?php echo $config->get('address'); ?>/files/download/<?php echo (int) $file['id']; ?>/?ticket_id=<?php echo (int) $ticket['id']; ?>" title="<?php echo safe_output($file['name']); ?>" class="btn btn-info btn-xs"><?php echo safe_output($file['name']); ?></a>
                <?php } ?>
            </div>
            <?php } ?>


        <div class="clearfix"></div>
    </div>
</div>
