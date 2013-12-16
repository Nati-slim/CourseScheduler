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
							Created by <a href="http://janetalkscode.com" title="Jane Talks Code!">Jane Ullah</a>
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
						<form id="loginForm" name="loginForm" class="form-signin" role="form" method="post" >
							<div class="form-group">
								<label for="loginUsername">Username</label>
								<input type="text" class="form-control" id="loginUsername" name="loginUsername" placeholder="Enter username">
							</div>
							<div class="form-group">
								<label for="loginPassword">Password</label>
								<input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Password">
							</div>
							<button type="button" onclick="loginUser()" class="btn btn-primary">Submit</button>
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
						<form id="signupForm" name="signupForm" class="form-signin" method="post" role="form">
							  <div class="form-group">
									<label for="username">Username</label>
									<input type="text" class="form-control" id="username" name="username" placeholder="Enter email">
							  </div>
							  <div class="form-group">
									<label for="email">Email address</label>
									<input type="email" class="form-control" id="email" name="email" placeholder="Enter email">
							  </div>
							  <div class="form-group">
									<label for="password1">Password</label>
									<input type="password" class="form-control" id="password1" name="password1" placeholder="Password">
							  </div>
							  <div class="form-group">
									<label for="password2">Password</label>
									<input type="password" class="form-control" id="password2" name="password2" placeholder="Password">
							  </div>
							  <button type="button" onclick="registerUser()" class="btn btn-primary">Submit</button>
							  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</form>
					</div>
				</div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->	

	<div id="dialog-modal" title="Save Schedule!">
		<p id="imgUrlInfo"></p>
	</div>
	
	<div id="dialog-removeAllSections" title="Remove All Sections">
		<p id="removeAllSectionInfo" style="display:none;"><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		All your sections will be removed if you proceed with this action.
		Save your schedule by clicking the "Download Schedule" link.</p>
	</div>
