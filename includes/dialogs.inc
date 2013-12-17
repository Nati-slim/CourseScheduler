
	<!-- Download Schedule Modal -->
	<div class="modal fade" id="pngModal" tabindex="-1" role="dialog" aria-labelledby="pngModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="pngModalLabel">Download Schedule!</h4>
					</div>
					<div class="modal-body" id="canvasImage">

					</div>
				</div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	<!-- About Modal -->
	<div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="aboutModalLabel">About Course Picker!</h4>
					</div>
					<div class="modal-body">
						<p>
							This is a labor of love by <a href="http://janeullah.com" title="Jane Ullah!">Jane Ullah</a>. :)
                            I write at <a href="http://janetalkscode.com" title="Jane Talks Code!">Jane Talks Code</a> and tweet <a href="https://twitter.com/janetalkstech" title="@janetalkstech on Twitter">@janetalkstech</a>. The source code for this web application is hosted 
                            as <a href="https://github.com/janoulle/CourseScheduler" title="CoursePicker Source Code">CourseScheduler on Github</a>
						</p>
                        <p>
                            Usage:
                            <ul>
                                <li>Search for a course by either course prefix e.g. CSCI or course number (e.g. 1302) or any common names.</li>
                                <li>Choose from the autocomplete menu or manually enter the course you desire in this manner as long as you separate the course prefix from the course number by a dash</li>
                                <li>Select a section from the list</li>
                                <li>Optional:
                                    <ul>
                                        <li>Signup as a user to save your schedule</li>
                                        <li>Signup to also be able to save different versions of your schedule!</li>
                                    </ul>
                                </li>
                                        
                            </ul>
                        </p>
						<p>
							<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">CoursePicker</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.
						</p>					
					</div>
				</div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	<!-- Login Modal -->
	<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="loginModalLabel">Log In!</h4>
					</div>
					<div class="modal-body">
						<div class="alert alert-danger" id="loginError" style="display:none"></div>
						<div class="alert alert-success" id="loginSuccess" style="display:none"></div>
						<form id="loginForm" name="loginForm" class="form-signin" role="form" method="post" action="classes/controllers/usercontroller.php"  >
							<div class="form-group">
								<label for="loginUsername">Username</label>
								<input type="text" class="form-control" id="loginUsername" name="loginUsername" placeholder="Enter username" required>
							</div>
							<div class="form-group">
								<label for="loginPassword">Password</label>
								<input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Password" required>
							</div>
							<input type="hidden" id="action" name="action" value="login" />
							<button type="submit" class="btn btn-primary">Submit</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</form>
					</div>
				</div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	
	<!-- Signup Modal -->
	<div class="modal fade" id="signupModal" tabindex="-1" role="dialog" aria-labelledby="signupModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="signupModalLabel">Sign Up!</h4>
					</div>
					<div class="modal-body">
						<div class="alert alert-danger" id="signupError" style="display:none"></div>
						<div class="alert alert-success" id="signupSuccess" style="display:none"></div>
						<form id="signupForm" name="signupForm" class="form-signin" method="post" action="classes/controllers/usercontroller.php" role="form">
							  <div class="form-group">
									<label for="username">Username</label>
									<input type="text" class="form-control" id="username" name="username" placeholder="Enter email" required>
							  </div>
							  <div class="form-group">
									<label for="email">Email address</label>
									<input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
							  </div>
							  <div class="form-group">
									<label for="password1">Password</label>
									<input type="password" class="form-control" id="password1" name="password1" placeholder="Password" required>
							  </div>
							  <div class="form-group">
									<label for="password2">Password</label>
									<input type="password" class="form-control" id="password2" name="password2" placeholder="Password" required>
							  </div>
							  <div class="form-group">
								<?php 								
									require_once("includes/recaptcha/recaptchalib.php");
									require_once("../../creds/captcha.inc");
									$publickey = CP_PUBLIC_KEY;
									echo recaptcha_get_html($publickey);
								?>
							  </div>
							  <input type="hidden" id="action" name="action" value="newuser" />
							  <!-- onclick="registerUser()" -->
							  <button type="submit" class="btn btn-primary">Submit</button>
							  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</form>
					</div>
				</div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->	

