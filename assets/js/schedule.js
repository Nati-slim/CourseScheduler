$(function(){
	$('#downloadSchedule').on('click',function(){
		var imgUrl = "" + canvasItem.toDataURL();
		var index = imgUrl.indexOf(",");
		if (index > 0){
			console.log("Index: " + index);
			//Grabbing the base64 part only
			imgUrl = imgUrl.substring(index+1);
			//console.log("New imgurl: " + imgUrl);
			$('body').css('cursor', 'wait');
			$.ajax({
				type: "POST",
				url: 'classes/controllers/schedulecontroller.php',
				data: { action : "downloadSchedule", dataUrl : imgUrl}
			})
			.done(function(msg){
				$('body').css('cursor', 'auto');
				//console.log(msg);
				var msgObj = JSON.parse(msg);
				//console.log(msgObj);
				if (msgObj.imgToken.length > 0){
					//Should be a url to http://apps.janeullah.com/coursepicker/assets/schedules/schedule_autogenid.png
					var imgUrl = "http://apps.janeullah.com/coursepicker/assets/schedules/schedule_" + msgObj.imgToken + ".png";
					//console.log(imgUrl);
					$('#canvasImage').empty();
					$('#canvasImage').append("<a href=\"" + imgUrl + "\" title=\"Click to view image.\">Right-Click To Save Image</a>");
					$('#errorMessage').empty().hide();
				}else{
					$('#canvasImage').empty().append("<p class=\"alert-danger\">Unable to save .png file: " + msgObj.errorMessage + "</p>").show();
				}
			})
			.fail(function(msg){
				$('body').css('cursor', 'auto');
				$('#canvasImage').empty().append("<p class=\"alert-danger\">Error getting png file.</p>").show();
				console.log(msg + "Error getting png file.");
			});	
		}else{
			console.log("Index not found");
			$('#errorMessage').empty().append("Invalid image data url.").show();
			console.log("Invalid image data url.");
		}	
	});
});
