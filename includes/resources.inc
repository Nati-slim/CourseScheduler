    <script src="<?php echo $asseturl ."/js/jquery-1.9.1.min.js"; ?>"></script>
    <script src="<?php echo $asseturl ."/js/bootstrap.min.js"; ?>"></script>
    <script src="<?php echo $asseturl ."/js/coursepicker.js"; ?>"></script>
    <script src="<?php echo $asseturl ."/js/alertify.min.js"; ?>"></script>
    <link href="<?php echo $asseturl ."/css/bootstrap.min.css"; ?>" rel="stylesheet">
    <link href="<?php echo $asseturl ."/css/coursepicker.css"; ?>" rel="stylesheet">
    <link href="<?php echo $asseturl ."/css/alertify.core.css"; ?>"  rel="stylesheet" media="screen">
    <link href="<?php echo $asseturl ."/css/alertify.default.css"; ?>"  rel="stylesheet" media="screen">
    <link href="<?php echo $asseturl ."/css/bootstrap-responsive.css"; ?>" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://apps.janeullah.com/coursepicker/assets/js/html5shiv.js"></script>
    <![endif]-->
    <!--[if IE]><script type="text/javascript" src="http://apps.janeullah.com/coursepicker/assets/js/excanvas.js"></script><![endif]-->
    <style>
		body {
			padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
			background-image:url('http://apps.janeullah.com/coursepicker/assets/images/escheresque.png');
			background-repeat:repeat;
		}
		.modal-body{
			background-image:url('http://apps.janeullah.com/coursepicker/assets/images/escheresque.png') !important;
		}
		.modal-header{
			background-color: rgb(168, 50, 50);
			color: #ffffff;
		}
		.modal-footer{
			background-color: rgb(168, 50, 50);
			color: #ffffff;
		}
		.control-label{
			font-weight: bold;
			font-size: 120%;
		}

		.listInfo{
			border: 2 solid #000000;
		}

		#pngModal{
			width:810px;
		}

		#captcha{
			margin-left: 88px;
		}

		#shareModalBody{
			font-size:130%;
			font-weight:bold;
		}

		.navbar-inner {
			background-color: rgb(168, 50, 50);
		}
    </style>

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo $asseturl ."/ico/apple-touch-icon-144-precomposed.png";?>">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $asseturl ."/ico/apple-touch-icon-114-precomposed.png";?>">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $asseturl ."/ico/apple-touch-icon-72-precomposed.png";?>">
    <link rel="apple-touch-icon-precomposed" href="<?php echo $asseturl ."/ico/apple-touch-icon-57-precomposed.png";?>">
    <link rel="shortcut icon" href="<?php echo $asseturl ."assets/ico/favicon.png";?>">
	<script type="text/javascript">
		$(document).ready(function(){
			$('#sendMessage').submit(function(e){
				e.preventDefault();
                $.ajax({
                    type:'POST',
                    url: 'classes/controllers/auth.php',
                    data:$(this).serialize(),
                    success: function(response) {
                        $('#formMessage').append(response);
                        $('#formMessage').show();
                        setTimeout(function(){
								$('#formMessage').empty();
                                $('#formMessage').hide();
                            }, 4000);
                        Recaptcha.reload();
                    }
                });
                return false;
			});
		});
	</script>
