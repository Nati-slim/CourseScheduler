/*$(function() {
    /*$("#signup,#login").hover(
            function() {
                $(this).animate({ color: "#FC4349" }, 'slow');
            },function() {
                $(this).animate({ color: "#f0f0f0" }, 'slow');
        });
});*/

$(function(){
	//$('#signupForm').submit(
	
	
});
function registerUser(){
	$('body').css('cursor', 'wait');
	var user = new Parse.User();
	var username = $('#username').val();
	var email = $('#email').val();
	var pwd1 = $('#password1').val();
	var pwd2 = $('#password2').val();
	if (pwd1 == pwd2){
		user.set("username", username);
		user.set("password", pwd1);
		user.set("email", email);
		user.signUp(null, {
			success: function(user) {
				$('body').css('cursor', 'auto');
				$('#signupError').empty().hide();
				$('#signupForm').hide('slow', function(){ 
					$('#signupForm').hide(); 
				});
				$('#signupSuccess').empty().append("Successfully signed up. You are now logged in!").show();
			},
			error: function(user, error) {
				$('body').css('cursor', 'auto');
				$('#signupError').empty().append("Error: " + error.code + " " + error.message)
				.show();
				$('#signupSuccess').empty().hide();
			}
		});		
	}else{
		$('#signupSuccess').empty().hide();
		$('#signupError').empty().append("Error: mismatched passwords.").show();
	}
	return false;
}


function loginUser(){
	var username = $('#loginUsername').val();
	var pwd = $('#loginPassword').val();
	if (username.length != 0 && pwd.length != 0){
		$('body').css('cursor', 'wait');
		Parse.User.logIn(username, pwd, {
			success: function(user) {
				$('body').css('cursor', 'auto');
				$('#loginError').empty().hide();
				$('#loginForm').hide('slow', function(){ 
					$('#loginForm').hide(); 
				});
				$('#loginSuccess').empty().append("Successfully logged in!").show();
				$('#social').empty();
				var username = user.getUsername();
				console.log("User logged in: " + username);
				var logout = "<li style=\"font-weight:bold;\">Welcome " + username + "</li>"; 
				logout += "<li id=\"#logoutLi\"><a id=\"logout\" href=\"#logout\" onclick=\"logoutUser()\">Logout</a></li>";
				$('#social').append(logout);
				$('#social').show();
				
				/*setTimeout(function () { location.reload(true); }, 1000);
				var myModal = $('#loginModal').on('shown', function () {
					clearTimeout(myModal.data('hideInteval'))
					var id = setTimeout(function(){
						myModal.modal('hide');
					},1000);
					myModal.data('hideInteval', id);
				});	*/
				
			},
			error: function(user, error) {
				$('body').css('cursor', 'auto');
				$('#loginError').empty().append("Error: " + error.code + " " + error.message)
				.show();
				$('#loginSuccess').empty().hide();
				var signupLink = "<li id=\"signupLi\"><a id=\"signup\" data-toggle=\"modal\" href=\"#signupModal\">Sign Up</a></li>";
				var loginLink = "<li id=\"loginLi\"><a id=\"login\" data-toggle=\"modal\" href=\"#loginModal\">Log In</a></li>";
				$('#social').empty().append(signupLink);
				$('#social').append(loginLink);
				$('#social').show();
			}
		});
	}else{
		$('#loginSuccess').empty().hide();
		$('#loginError').empty()
		.append("Error: " + error.code + " " + error.message)
		.show();
		var signupLink = "<li id=\"signupLi\"><a id=\"signup\" data-toggle=\"modal\" href=\"#signupModal\">Sign Up</a></li>";
		var loginLink = "<li id=\"loginLi\"><a id=\"login\" data-toggle=\"modal\" href=\"#loginModal\">Log In</a></li>";
		$('#social').empty().append(signupLink);
		$('#social').append(loginLink);
		$('#social').show();
	}
	return false;
}

function logoutUser(){
	Parse.User.logOut();
}
