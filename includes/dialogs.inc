
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
	
	<!-- Tweet Schedule Modal -->
	<div class="modal fade" id="tweetModal" tabindex="-1" role="dialog" aria-labelledby="tweetModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
						<h4 class="modal-title" id="tweetModalLabel">Tweet This Schedule!</h4>
					</div>
					<div class="modal-body" id="tweetSchedule">
                        <p id="tweetError" class="alert alert-danger" style="display:none;"></p>
                        <p id="tweetSuccess" class="alert alert-success" style="display:none;"></p>
                        <p>By clicking the "Tweet this" button, you are authorizing <a href="http://apps.janeullah.com/coursepicker/" title="CoursePicker">CoursePicker</a> to post a status update to your twitter
                        account with the attached schedule image <a href="<?php echo $session->imgUrl; ?>" title="Your Class Schedule">here</a>.</p>
                        <?php if (!isset($session->twitter_oauth_token)) { ?>
                            <p class="alert alert-info">It appears you haven't authorized this application to tweet on your behalf, please click the button to begin the process: <a href="http://apps.janeullah.com/coursepicker/auth/index.php" title="Sign in with Twitter to expose enhanced featuers of CoursePicker.!"><img alt="Sign in with Twitter" src="http://apps.janeullah.com/coursepicker/assets/img/signin-twitter.png" style="border:0;display:block;margin-left:100px;" width="158" height="28" /></a></p>
                        <?php } elseif (isset($currentSchedule) && $currentSchedule->isSaved()) { ?>
                            <form id="tweetNoPngForm" name="tweetNoPngForm" class="form-signin" role="form" action="http://apps.janeullah.com/coursepicker/auth/tweetphoto.php" method="post" style="margin-left:4px;">
                                <div class="form-group">
                                    <label for="status">Tweet Text</label>
                                    <textarea type="text" name="status" rows="3" cols="80">Check out my <?php echo $semesters[$semesterSelected]; ?> class schedule [http://apps.janeullah.com/coursepicker/share/id=<?php echo $currentSchedule->getShortName();?>] from @coursepicker #uga.</textarea>
                                    <input type="hidden" id="action" name="action" value="tweetSchedule" />
                                </div>
                                <input type="submit" class="btn btn-primary" value="Tweet This" />
                            </form>
                        <?php } else { ?>
                            <form id="tweetForm" name="tweetForm" class="form-signin" role="form" action="http://apps.janeullah.com/coursepicker/auth/tweetphoto.php" method="post" style="margin-left:4px;">
                                <div class="form-group">
                                    <label for="status">Tweet Text</label>
                                    <textarea type="text" name="status" rows="2" cols="70">Check out my <?php echo $semesters[$semesterSelected]; ?> class schedule created using @coursepicker http://bit.ly/coursepicker #uga.</textarea>
                                    <input type="hidden" id="action" name="action" value="tweetPng" />
                                </div>
                                <input type="submit" class="btn btn-primary" value="Tweet This" />
                            </form>
                        <?php } ?>
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
                        <ul class="nav nav-tabs">
                            <li><a href="#regularlogin" data-toggle="tab">Login</a></li>
                            <li><a href="#forgotPassword" data-toggle="tab">Forgot Password</a></li>
                            <li><a href="#twitterLogin" data-toggle="tab">Login with Twitter</a></li>
                        </ul>
                        
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="regularlogin">
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
                            <div class="tab-pane fade" id="twitterLogin">
                                <div class="form-group">
                                    <a href="http://apps.janeullah.com/coursepicker/auth/index.php" title="Sign in with Twitter to expose enhanced featuers of CoursePicker.!"><img alt="Sign in with Twitter" src="http://apps.janeullah.com/coursepicker/assets/img/signin-twitter.png" style="border:0;" width="158" height="28" /></a>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="forgotPassword">
                                <div class="alert alert-danger" id="forgotPwdError" style="display:none"></div>
                                <div class="alert alert-success" id="forgotPwdSuccess" style="display:none"></div>
                                <form id="forgotPwdForm" name="forgotPwdForm" class="form-signin" role="form" method="post" action="classes/controllers/authcontroller.php"  >
                                    <div class="form-group">
                                        <label for="forgotPwdUsername">Username</label>
                                        <input type="text" class="form-control" id="forgotPwdUsername" name="forgotPwdUsername" placeholder="Username" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="forgotPwdEmail">Email</label>
                                        <input type="email" class="form-control" id="forgotPwdEmail" name="forgotPwdEmail" placeholder="Email" required>
                                    </div>
                                    <div class="form-group">
                                        <img id="captcha" src="http://apps.janeullah.com/coursepicker/includes/securimage/securimage_show.php" alt="CAPTCHA Image" />
                                        <input type="text" name="captcha_code" size="10" maxlength="6" />
                                        <a href="#" onclick="document.getElementById('captcha').src = 'http://apps.janeullah.com/coursepicker/includes/securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>
                                    </div>
                                     <input type="hidden" id="action" name="action" value="forgotPassword" />
                                    <button type="submit" id="forgotPwdBtn" class="btn btn-primary">Submit</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </form>
                            </div>
                        </div>
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
									require_once dirname(__FILE__) . '/recaptcha/recaptchalib.php';
									require_once dirname(__FILE__) . '/../../../creds/captcha.inc';
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

