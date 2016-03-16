<?php 
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

?>
		<div class="clearfix"></div>
		<div class="modal fade" id="custom_modal_anchor" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>
		
        
	</div><!--/.container-->
	<footer class="no_print">
			<div class="pull-left">
				<p class="text-muted">
					<small>
						<?php echo safe_output($language->get('Copyright')); ?> <span class="glyphicon glyphicon-copyright-mark"></span> <a href="http://pegui.edu.co">PEGUI</a> <?php echo date('Y'); ?> - Powered by <a href="http://catedra.edu.co">Catedra e-learning</a>
					</small>
				</p>
			</div>
			<div class="pull-right">
				<p class="text-muted">
					<small>
						<?php echo safe_output(stop_timer()); ?>
					</small>
				</p>
			</div>
    </footer>

	<script type="text/javascript"> 
		$('.dropdown-toggle').dropdown();
        $('.navbar-toggle').click(function(){
            $('.navbar').toggleClass('open');
        });
	</script>

    <script>
        $(function(){
            if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                $(document).on('focusin', 'input, textarea', function() {
                    $('.form-inline .panel-footer').hide();
                })
                .on('focusout', 'input, textarea', function () {
                    $('.form-inline .panel-footer').slideDown();
                });
            }
        });
    </script>
</body>
</html>
