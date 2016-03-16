<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Dalegroup Tickets</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<link href="../user/themes/bootstrap3/sub/v5/css/bootstrap.css" rel="stylesheet">
	<link href="../user/themes/bootstrap3/sub/v5/css/responsive-tables.css" rel="stylesheet">    
	<link href="../user/themes/bootstrap3/sub/v5/css/bootstrap-custom.css" rel="stylesheet">    

	<script type="text/javascript" src="../system/libraries/js/jquery.js"></script>
	<script type="text/javascript" src="../system/libraries/js/respond.min.js"></script>
	<script type="text/javascript" src="../user/themes/bootstrap3/sub/v5/js/bootstrap.min.js"></script>	
	
	<link rel="stylesheet" href="../system/libraries/select2/select2.css" />	
	<script type="text/javascript" src="../system/libraries/select2/select2.min.js"></script>
		
	<script type="text/javascript"> 
	$(document).ready(function () {
		//Custom Selectmenu
		$('select').select2({ width: 'resolve' });
				
		//tooltip
		$(".glyphicon-question-sign").tooltip({html: true});
		
		//popover
		$('.popover-item').popover().click(function(e){e.preventDefault();});
		
	});
	</script>

</head>

<body>
	<nav class="navbar navbar-default navbar-fixed-top navbar-inverse" role="navigation">
	
		<div class="navbar-header">
			<a class="navbar-brand" href="index.php">Tickets</a>			
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		
		<div class="collapse navbar-collapse">			  		
			<ul class="nav navbar-nav">
				<li class="active"><a href="index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>		
			</ul>
		</div><!--/.nav-collapse -->	

	</nav>
	
    <div class="container">