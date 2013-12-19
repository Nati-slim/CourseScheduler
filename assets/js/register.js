function logout(){
	$.ajax({
		type: "POST",
		url: 'classes/controllers/usercontroller.php',
		data: {action:"logout"},
		dataType: "json"
	})
	.done(function(msg){
		alert("Successfully logged out!");
		setTimeout(function(){
				location.reload();
        }, 1000);		
	})
	.fail(function(msg){
		alert(msg.responseText);
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
            //errorMessage will be empty if no problems
			if (msg.errorMessage.length > 0){
				$('#signupError').empty();
				$('#signupError').append(msg.errorMessage);
				$('#signupError').show();
				$('#signupSuccess').hide();
				setTimeout(function(){
					$('#signupError').empty().hide("slow",function(){});
				}, 10000);
			}else{
				$('#signupError').empty().hide();
				$('#signupSuccess').empty().append("Successfully signed up for CoursePicker! An activation email has been sent to the email address you provided. In the meantime, please login to save your created schedules.").show();
				$('#signupForm').hide("slow",function(){});
				console.log("Successfully signed up.");
                //Don't hide div if signup issue found
                setTimeout(function(){
                        location.reload();
                }, 5000);
			}
		})
		.fail(function(msg){
			$('body').css('cursor', 'auto');
			$('#signupError').empty().append(msg.responseText).show();
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
			console.log(msg);
			if (msg.errorMessage.length > 0){
				$('#loginError').empty();
				$('#loginError').append(msg.errorMessage).show();
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
                }, 2000);
				console.log("Successfully logged in.");
			}
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
