<?php 
namespace sts\plugins;
use sts;

?>
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.common.core.js"></script>
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.common.key.js"></script>
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.common.dynamic.js"></script>
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.common.tooltips.js"></script>
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.bar.js"></script>              <!-- Just needed for bar graphs -->
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.line.js"></script>             <!-- Just needed for line graphs -->
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.pie.js"></script>             <!-- Just needed for pie graphs -->
<script type="text/javascript" src="<?php echo sts\safe_output($config->get('address')); ?>/user/plugins/<?php echo sts\safe_output($this->url_basename); ?>/../rgraph/libraries/RGraph.drawing.rect.js"></script>