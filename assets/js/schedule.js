$(function(){
    /**
     * Function to get an image url
     * of the schedule
     * 
     */ 
	$('#downloadSchedule').on('click',function(){
        grabImage();
	});
	
	/**Handling changing the user schedule in saveschedule.php
     * Switching the schedules when the user makes a change
     * 
     */ 
	$('#selectedSchedule').change(function(){
        var scheduleSelectedID = $('#selectedSchedule').val();
        var optionText = $('option[value="'+scheduleSelectedID+'"]').text();
        ga('send', 'Schedule change', 'User clicked', 'scheduleID', scheduleSelectedID);
		$('body').css('cursor', 'wait');
		$.ajax({
			type: "POST",
			url: 'http://apps.janeullah.com/coursepicker/classes/controllers/schedulecontroller.php',
			data: { action : "switchSchedule", scheduleID : scheduleSelectedID, optionChosen : optionText},
			dataType: "json"
		})
		.done(function(msg){
			$('body').css('cursor', 'auto');
			console.log(msg);
            if (msg.errorMessage.length == 0){
                $('#saveScheduleError').empty().hide();
                $('#saveScheduleSuccess').empty().append("Switched schedules!.").show(); 
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }else{
                $('#saveScheduleSuccess').empty().hide();
                $('#saveScheduleError').empty().append(msg.errorMessage).show();
            }
		})
		.fail(function(msg){
			$('body').css('cursor', 'auto');
            $('#saveScheduleSuccess').empty().hide();
            $('#saveScheduleError').empty().append(msg.responseText).show();
			console.log(msg.responseText);
		});	
	});
    
    //Tweeting schedule
    $('#tweetForm').submit(function(e){
        e.preventDefault();
        ga('send', 'Tweet schedule', 'User authenticated');
        $.ajax({
            type: "POST",
            url: 'http://apps.janeullah.com/coursepicker/auth/tweetphoto.php',
            data: $(this).serialize(),
            dataType: "json"
        })
        .done(function(msg){
            console.log(msg);
            if (msg.errorMessage.length == 0){
                if (msg.code == 200){
                    $('#tweetError').html("").hide();
                    $('#tweetSuccess').html("").append(msg.message).show();
                }else{
                    $('#tweetSuccess').html("").hide();
                    $('#tweetError').html("").append(msg.errorMessage).show();
                }
            }else{
                $('#tweetError').html("").append(msg.errorMessage).show();
                $('#tweetSuccess').html("").hide();                
            }
        })
        .fail(function(msg){
            console.log(msg.responseText);
        })
        .always(function(msg){
            setTimeout(function(){
				$('#tweetError').html("").hide();
                $('#tweetSuccess').html("").hide('slow',function(){});
            }, 20000);	 
        });
        return false;
    });
    
    /**
     * Saving the schedule
     * 
     */ 
    $('#saveScheduleForm').submit(function(e){
		e.preventDefault();
        var itemSelected = $('#scheduleID').val();
        var shortName1 = $('#shortName1').val();
        var shortName2 = $('#shortName2').val();
        if (itemSelected == 0){
            $('#saveScheduleError').empty().append("Please select a schedule to save!").show();
            $('#saveScheduleSuccess').empty().hide();
        }else if (shortName1 === shortName2){            
            ga('send', 'Schedule save', 'User clicked', 'shortName', shortName1);
            $('body').css('cursor', 'wait');
            $.ajax({
                type: "POST",
                url: 'http://apps.janeullah.com/coursepicker/classes/controllers/schedulecontroller.php',
                data: $(this).serialize(),
                dataType: "json"
            })
            .done(function(msg){
                $('body').css('cursor', 'auto');
                if (msg.errorMessage.length == 0){
                    $('#saveScheduleError').empty().hide();
                    $('#saveScheduleSuccess').empty().append("Successfully saved <strong>" + msg.shortName + "</strong> to database.").
                    show();setTimeout(function(){
                        location.reload();
                    }, 1000);
                }else{
                    $('#saveScheduleError').empty().append(msg.errorMessage).show();
                    $('#saveScheduleSuccess').empty().hide();
                }
                console.log(msg);
            })
            .fail(function(msg){
                $('body').css('cursor', 'auto');
                $('#saveScheduleError').append(msg.responseText).show();
                $('#saveScheduleSuccess').empty().hide();
                console.log(msg.responseText);
            });	
        }else{
            $('#saveScheduleError').empty().append("Both short name fields must match!").show();
            $('#saveScheduleSuccess').empty().hide();
        }
	});
    
    //$('#popoverOption').popover({ trigger: "hover" });
});

function updateSchedule(){
    var shortName = $('#savedShortName').val();
    var selectedScheduleID = $('#scheduleID').val();
    ga('send', 'Schedule update', 'User clicked', 'shortName', shortName);
    $('body').css('cursor', 'wait');
    $.ajax({
        type: "POST",
        url: 'http://apps.janeullah.com/coursepicker/classes/controllers/schedulecontroller.php',
        data: { action:"updateSchedule",scheduleID : selectedScheduleID, savedShortName: shortName},
        dataType: "json"
    })
    .done(function(msg){
        $('body').css('cursor', 'auto');
        if (msg.errorMessage.length == 0){
            $('#saveScheduleError').empty().hide();
            console.log(msg);
            $('#saveScheduleSuccess').empty().append("Successfully saved <strong>" + msg.shortName + "</strong> to database.").
            show();setTimeout(function(){
                location.reload();
            }, 5000);
        }else{
            $('#saveScheduleError').empty().append(msg.errorMessage).show();
            $('#saveScheduleSuccess').empty().hide();
        }
        console.log(msg);
    })
    .fail(function(msg){
        $('body').css('cursor', 'auto');
        $('#saveScheduleError').append(msg.responseText).show();
        $('#saveScheduleSuccess').empty().hide();
        console.log(msg.responseText);
    });	
    return false;
}

function grabImage(){
    var imgUrl = "" + getDataUrl();
    var imgToken = "";
    var index = imgUrl.indexOf(",");
    if (index > 0){
        //console.log("Index: " + index);
        //Grabbing the base64 part only
        imgUrl = imgUrl.substring(index+1);
            
        ga('send', 'Schedule png download', 'User clicked', 'imgurl', imgUrl);
        //console.log("New imgurl: " + imgUrl);
        $('body').css('cursor', 'wait');
        $.ajax({
            type: "POST",
            url: 'http://apps.janeullah.com/coursepicker/classes/controllers/schedulecontroller.php',
            data: { action : "downloadSchedule", dataUrl : imgUrl}
        })
        .done(function(msg){
            $('body').css('cursor', 'auto');
            console.log(msg);
            var msgObj = JSON.parse(msg);
            imgToken = msgObj.imgToken;
            if (msgObj.imgToken.length > 0){
                //Should be a url to http://apps.janeullah.com/coursepicker/assets/schedules/schedule_autogenid.png
                var imgUrl = "http://apps.janeullah.com/coursepicker/assets/schedules/schedule_" + msgObj.imgToken + ".png";
                //console.log(imgUrl);
                $('#canvasImage').empty();
                $('#canvasImage').append("Click <a href=\"" + imgUrl + "\" title=\"Click to view image or right-click to save link as.\">this link</a> to load your schedule as a .png file or right-click <a href=\"" + imgUrl + "\" title=\"Click to view image or right-click to save link as.\">the link</a> and choose \"Save Link As\".");
                //$('#canvasImage').append("<br/><a href=\"http://apps.janeullah.com/coursepicker/auth/index.php\" title=\"Tweet this schedule\"><img alt=\"Sign in with Twitter\" src=\"http://apps.janeullah.com/coursepicker/assets/img/signin-twitter.png\" style=\"border:0;\" width=\"158\" height=\"28\" /></a>");
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
    return imgToken;
}


function getDataUrl(){
    var c = document.getElementById("scheduleCanvas");
    return c.toDataURL();
}
