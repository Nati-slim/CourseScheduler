			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="./">CoursePicker</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a href="./">Home</a></li>
						<li><a href="#aboutModal" data-toggle="modal" id="about">About</a></li>
						<li><a href="#contact" id="contact">Contact</a></li>
						<?php if (isset($session->userid)){ 
							echo "<li><a id=\"downloadSchedule\" href=\"#pngModal\" data-toggle=\"modal\">Download Schedule</a></li>";
						}
						?>
					</ul>
					<ul id="social" class="nav navbar-nav navbar-right">
						<!-- If session exists and user is logged in-->
						<?php if (isset($session->loggedIn) && $session->loggedIn){ 
							echo "<li class=\"gravatar\"><img id=\"gravatarImg\" class=\"gravatar\" src=\"" . $session->gravatar_url . "\" alt=\"Gravatar image for " . $session->username . "\"  title=\"Gravatar image for " . $session->username . "\"/>";
							$submenu = "<ul id=\"submenu\" class=\"sub\" style=\"display:none;\">"
										. "<li id=\"welcome\">Welcome, " .  $session->username. "</li>"
										. "<li id=\"logoutLi\"><a href=\"#logout\" onclick=\"logout()\">Logout</a></li>"
										."</ul>"
										."</li>";
							echo $submenu;
						} else {
							echo "<li id=\"signupLi\"><a id=\"signup\" data-toggle=\"modal\" href=\"#signupModal\">Sign Up</a></li>";						
							echo "<li id=\"loginLi\"><a id=\"login\" data-toggle=\"modal\" href=\"#loginModal\">Log In</a></li>";
						} ?>
					</ul>				  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->
