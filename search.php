<?php

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Course Scheduler</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="A course scheduling app for the University of Georgia Computer Science students">
		<meta name="author" content="Jane Ullah">

		<!-- Le styles -->
		<script src="assets/js/jquery-1.9.1.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/alertify.min.js"></script>
		<link href="assets/css/bootstrap.css" rel="stylesheet">
		<link href="assets/css/coursepicker.css" rel="stylesheet">
		<link href="assets/css/alertify.core.css" rel="stylesheet" media="screen" />
		<link href="assets/css/alertify.default.css" rel="stylesheet" media="screen" />
	   <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		  <script src="assets/js/html5shiv.js"></script>
		<![endif]-->

		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="assets/ico/favicon.png">
		<script type="text/javascript">
			$('#courses').typeahead({
				name: 'UGA_Fall_Courses',
				prefetch: '../../csv/jsonfiles/courses.json',
				header: '<h3 class="item-name">NBA Teams</h3>',
				limit: 10
			});
		</script>
		<script type="text/javascript">
			// Charles Lawrence - Feb 16, 2012. Free to use and modify. Please attribute back to @geuis if you find this useful
			// Twitter Bootstrap Typeahead doesn't support remote data querying. This is an expected feature in the future. In the meantime, others have submitted patches to the core bootstrap component that allow it.
			// The following will allow remote autocompletes *without* modifying any officially released core code.
			// If others find ways to improve this, please share.
			$(document).ready(function(){
			var autocomplete = $('#courses').typeahead()
				.on('keyup', function(ev){

					ev.stopPropagation();
					ev.preventDefault();
					console.log("Got here.");
					//filter out up/down, tab, enter, and escape keys
					if( $.inArray(ev.keyCode,[40,38,9,13,27]) === -1 ){

						var self = $(this);

						//set typeahead source to empty
						self.data('typeahead').source = [];

						//active used so we aren't triggering duplicate keyup events
						if( !self.data('active') && self.val().length > 0){

							self.data('active', true);

							//Do data request. Insert your own API logic here.
							$.getJSON("assets/json/courses.json", function(data) {
								console.log(data);
								//set this to true when your callback executes
								self.data('active',true);

								//Filter out your own parameters. Populate them into an array, since this is what typeahead's source requires
								var arr = [], i=data.length;
								while(i--){
									arr[i] = data[i];
								}

								//set your results into the typehead's source
								self.data('typeahead').source = arr;

								//trigger keyup on the typeahead to make it search
								self.trigger('keyup');

								//All done, set to false to prepare for the next remote query.
								self.data('active', false);

							});

						}
					}
				});
			});
		</script>
	</head>

	<body>
		<input id="courses" data-provide="typeahead" class="typeahead" type="text" placeholder="Courses" autocomplete="off" spellcheck="false" style="position: relative; vertical-align: top; background-color: transparent;" dir="auto">
	</body>
</html>
