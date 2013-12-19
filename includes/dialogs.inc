
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
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="font-weight:bold;color: black;opacity: 1;">&times;</button>
						<h4 class="modal-title" id="aboutModalLabel">About Course Picker!</h4>
					</div>
					<div class="modal-body">
						<p>
                            <strong>What</strong>:
							<ol id="what">
                                <li>This is a labor of love by <a href="http://janeullah.com" title="Jane Ullah!">Jane Ullah</a>. :) </li>
                                <li>Other projects: <a href="http://dawgtransit.com" title="DawgTransit - Get around UGA's campus with ease.">DawgTransit</a>, <a href="http://apps.janeullah.com/dawgspeak" title="UGA SLanguage Dictionary">DawgSpeak</a>, and <a href="https://play.google.com/store/apps/details?id=org.janeullah.android.healthrecords" title="Restaurant Health Inspection Records">Restaurant Health Inspection Records app</a> on Google Play.</li>
                                <li>I write at <a href="http://janetalkscode.com" title="Jane Talks Code!">Jane Talks Code</a> and tweet <a href="https://twitter.com/janetalkstech" title="@janetalkstech on Twitter">@janetalkstech</a>.</li>
                                <li>The source code for this web application is hosted <a href="https://github.com/janoulle/CourseScheduler" title="CoursePicker Source Code">on Github</a>.<br/><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">CoursePicker</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.</li>
                            </ol>                            
						</p>
                        <p>
                            <strong>Credits</strong>:
                            <ol id="credits">
                                <li>I'm using <a href="https://mixpanel.com/free/" title="Mixpanel">Mixpanel</a>, <a href="http://www.google.com/analytics/" title="Google Analytics">Google Analytics</a> and <a href="http://statcounter.com/" title="Statcounter">Statcounter</a> for event tracking and analytics. <a href="https://mixpanel.com/f/partner"><img src="//cdn.mxpnl.com/site_media/images/partner/badge_light.png" alt="Mobile Analytics" style="width:114px;height:36px;" width="114" height="36"/></a></li>
                                <li><a href="http://www.openwall.com/phpass/" title="Portable PHP password hashing framework">Portable PHP password hashing framework</a> or phpass was used for hashing the passwords and I made extensive use of the functions listed on the OpenWall article about <a href="http://www.openwall.com/articles/PHP-Users-Passwords" title="managing a PHP application's users and passwords">managing a PHP application's users and passwords</a>.</li>
                                <li><a href="https://github.com/PHPMailer/PHPMailer" title="PHPMailer">PHP Mailer</a> was used to handle the task of sending emails to users that sign up for the Course Picker site. Eventually, I'd like this task to be outsourced to Mailchimp.</li>
                                <li><a href="https://www.google.com/recaptcha" title="reCAPTCHA by Google">reCAPTCHA</a> is an "anti-bot service that helps digitize books". I used it in preventing automated submissions of the signup form.</li>
                                <li><a href="http://simplehtmldom.sourceforge.net/" title="PHP Simple HTML DOM Parser">PHP Simple HTML DOM Parser</a> is another awesome tool that I use a lot for scraping web pages to extract useful information. For parsing HTML with Java, I use <a href="http://jsoup.org/" title="Java HTML Parser">JSoup</a> and I highly recommend that as well.</li>
                            </ol>
                        </p>
                        
                        <p>
                            <strong>Usage</strong>:
                            <ol id="instructions">
                                <li>Terms: <strong>course prefix</strong>: CSCI or ENGL, <strong>course number</strong: e.g. 1302 or 1101 and <strong>course name</strong>: SOFT DEVELOPMENT or ENGLISH COMP I</li>
                                <li>Search for a course by any combination of the <strong>course prefix</strong>, <strong>course number</strong> and <strong>course name</strong>. Please don't be alarmed if you only type the course prefix and receive results that don't match the course prefix exactly. This is because the course prefix you are searching for is also in the course name of some of the results that were returned to you.</li>
                                <li>If you choose to search by couse prefix only, I recommend entering the term and having a space after the word. If that doesn't work, add a number after the space e.g. HIST 1 or HIST 2 to bring up specific results.</li>
                                <li>Choose from the autocomplete menu or manually enter the course you desire in this manner as long as you separate the course prefix from the course number by a dash or space</li>
                                <li>Select a section from the list</li>
                                <li><strong>Bonus</strong>Signup as a user to save your schedule and to have different versions of your schedule!</li>                                        
                            </ol>
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
									<input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
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

