/*$(function() {
    /*$("#signup,#login").hover(
            function() {
                $(this).animate({ color: "#FC4349" }, 'slow');
            },function() {
                $(this).animate({ color: "#f0f0f0" }, 'slow');
        });
});*/

function logout(){
	$.ajax({
		type: "POST",
		url: 'classes/controllers/usercontroller.php',
		data: {action:"logout"},
		dataType: "json"
	})
	.done(function(msg){
		$('#infoMessage').empty().append("Successfully logged out!").hide("slow",function(){});
	})
	.fail(function(msg){
		console.log(msg.responseText);
	});
}

$(function(){
	//handle signups 
	$('#signupForm').submit(function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: 'classes/controllers/usercontroller.php',
			data: $(this).serialize(),
			dataType: "json"
		})
		.done(function(msg){
			$('body').css('cursor', 'auto');
			if (msg.errorMessage.length > 0){
				$('#signupError').empty();
				$('#signupError').append("Error: " + msg.errorMessage);
				$('#signupError').show();
				$('#signupSuccess').hide();
				setTimeout(function(){
					$('#signupError').empty().hide("slow",function(){});
				}, 10000);
			}else{
				$('#signupError').empty().hide();
				$('#signupSuccess').empty().append("Successfully signed up for CoursePicker! You can now login.").show();
				$('#signupForm').hide("slow",function(){});
			}
			console.log("Successfully signed up");
			//console.log(msg);
			setTimeout(function(){
					location.reload();
            }, 6000);
		})
		.fail(function(msg){
			$('body').css('cursor', 'auto');
			$('#signupError').empty().append("Error signing up.").show();
			$('#signupSuccess').empty().hide();
			console.log(msg.responseText);
		})
		.always(function(msg){
			Recaptcha.reload();
		});
		return false;
	});
	
	//Handle logins
	$('#loginForm').submit(function(e){
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: 'classes/controllers/usercontroller.php',
			data: $(this).serialize(),
			dataType: "json"
		})
		.done(function(msg){
			$('body').css('cursor', 'auto');
			if (msg.errorMessage.length > 0){
				$('#loginError').empty();
				$('#loginError').append("Error: " + msg.errorMessage).show();
				$('#loginSuccess').hide();
				setTimeout(function(){
					$('#loginError').empty().hide("slow",function(){});
				}, 10000);
			}else{
				$('#loginError').empty().hide();
				$('#loginForm').hide('slow', function(){ 
					$('#loginForm').hide(); 
				});
				$('#loginSuccess').empty().append("Successfully logged in!").show();				
				setTimeout(function(){
					location.reload();
                }, 6000);
			}
			console.log("Successfully logged in.");
			//console.log(msg);
		})
		.fail(function(msg){
			$('body').css('cursor', 'auto');
			$('#loginError').empty().append("Error: " + msg).show();
			$('#loginSuccess').empty().hide();
			console.log(msg.responseText);
		});
		return false;
	});	
});
