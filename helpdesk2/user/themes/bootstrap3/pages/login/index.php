<?php
namespace sts;
use sts as core;

if (!defined(__NAMESPACE__ . '\ROOT')) exit;

$site->set_title($language->get('Login'));
$site->set_config('container-type', 'container');

if (isset($_POST['submit'])) {
	if ($auth->login(array('username' => $_POST['username'], 'password' => $_POST['password']))) {
		if (isset($_SESSION['page'])) {
			header('Location: ' . safe_output($_SESSION['page']));
		}
		else {
			header('Location: ' . $config->get('address') . '/');
		}
		exit;
	}
	else {
		$message = $language->get('Login Failed');
	}
}
else {
	if ($config->get('facebook_enabled')) {
		if (isset($_SESSION['fb_'. $config->get('facebook_app_id') .'_user_id'])) {
			$message = 'Your current Facebook profile is not linked with ' . $config->get('name') . '. Please login with your local details.';
		}
	}
}

$login_message = $config->get('login_message');

include(ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_header.php');
?>
<div class="row">
<center><h1>Bienvenidos a la Mesa de Ayuda de la Plataforma PEGUI</h1></center>
<br>
<div class="col-md-4">
<img src="http://pegui.edu.co/helpdesk2/user/themes/bootstrap3/images/avatars.png">
</div>
	<div class="col-md-6">
	
		<p>
						

		Las peticiones, quejas o reclamos serán remitidios bajo la forma de "Tickets" y serán publicados internamente en esta plataforma.
		<ul>
			<li type="1">
				Al publicar un Ticket recibirá una copia del mismo a su correo electrónico con el número de Ticked (ID) que le ha sido asignado.
			</li>
			<li type="1">
				Recibido, uno de nuestros operadores empezará a tratar su solicitud. Recibirá un correo con un aviso de "cambio de estado" de su Ticket de "Abierto" a "En progreso".
			</li>
			<li type="1">
				Cuando la petición ha sido solucionada, recibirá un correo con aviso "cambio de estado" de su Ticket de "En progreso" a "Cerrado".		
			</li>
			<li type="1">
				En cualquier momento será posible contestar los correos que le manda nuestra plataforma de Tickets y serán automáticamente sumado al historial de su Ticket.		
			</li>        
		</ul>


		Seguimiento: usted será informado por correo sobre la etapa de resolución o proceso en el que está su Ticket. También podrá ingresar a la plataforma de mesa de ayuda para ver el estado del Ticket y sus respectivas respuestas. Esto lo podrá hacer ingresando por el enlace contenido en el correo recibido ("Ver Ticket")


		</p>
<a href="../guest/ticket_add/" class="btn btn-primary btn-lg">Crear un TICKET</a>
	</div>
</div>





<?php include(ROOT . '/user/themes/'. CURRENT_THEME .'/includes/html_footer.php'); ?>
