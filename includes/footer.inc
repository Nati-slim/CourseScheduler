
	<!-- About Image Modal -->
	<div class="modal hide fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="aboutModalLabel">About</h3>
		</div>
		<div class="modal-body">
			Created by <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah</a>.
			<a href="https://github.com/janoulle/CourseScheduler" title="Source Code for CoursePicker">Source code on Github</a> and licensed under the <a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/3.0/deed.en_US">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License</a>. I reserve the right to change the licensing at anytime, I'm not liable for any issues that result from you using this site or the code, etc. Use at your own risk.<br>
			<img src="<?php echo $asseturl . "/images/coursepicker-515x394.png";?>" style="width:515px;height:394px;" alt="Course Picker">
		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>

	<!-- Contact Image Modal -->
	<div class="modal hide fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="contactModalLabel">Contact</h3>
		</div>
		<div class="modal-body">
			<p id="formMessage" class="infoMessage" style="display:none;"></p>
			<form class="form-horizontal" id="sendMessage" name="sendMessage" action="<?php echo $emailurl;?>" method="post">
				<div class="control-group">
					<label class="control-label" for="name">Name</label>
					<div class="controls">
						<input tabindex="2" type="text" name="name" id="name" placeholder="Name" required>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="email">Email</label>
					<div class="controls">
						<input tabindex="3" type="email" name="email" id="email" placeholder="Email address" required>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="messageMe">Message</label>
					<div class="controls">
						<textarea tabindex="4" id="messageMe" name="message" placeholder="Enter message" required></textarea>
					</div>
				</div>
				<div class="control-group">
					<div id="captcha">
						<?php
							require_once($captchaurl);
							require_once($recaptchaurl);
							$publickey = RECAPTCHA_JANEULLAH_PUBLIC;
							echo recaptcha_get_html($publickey);
						?>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<input tabindex="6" class="btn btn-danger" type="reset" value="Clear">
						<input tabindex="5" class="btn btn-primary" type="submit" value="Send">
					</div>
				</div>
			</form>
		</div>
		<div class="modal-footer">
			<a tabindex="7" class="btn" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>

	<!-- PNG Image Modal -->
	<div class="modal hide fade" id="pngModal" tabindex="-1" role="dialog" aria-labelledby="pngModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="pngModalLabel">Right-click image and save as a .png file</h3>
		</div>
		<div class="modal-body" id="canvasImage">

		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>

	<!-- Share Modal -->
	<div class="modal hide fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="shareModalLabel">Copy and share the url</h3>
		</div>
		<div id="shareModalBody" class="modal-body">

		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>
