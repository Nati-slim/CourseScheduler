var daysOfWeek = [ "Monday", "Tuesday", "Wednesday", "Thursday", "Friday" ];
var timesOfDay = [ "8:00am", "9:00am", "10:00am", "11:00am", "12:00pm",
			"1:00pm", "2:00pm", "3:00pm", "4:00pm","5:00pm","6:00pm","7:00pm","8:00pm","9:00pm" ];
var colors = [ "#D9EDF7", "#0D63B6","#CC333F","#317A22","#E5924C","#C34B5F","#4682B4","#228B22","#EE3B3B","#6E7B8B","#7584B5" ];
var TOP_MARGIN = "0px";
var LEFT_MARGIN = "0px";
var RIGHT_MARGIN = "0px";
var BOTTOM_MARGIN = "0px";
var CELL_WIDTH = 130;
var CELL_HEIGHT = 50;
var CANVAS_WIDTH = 780;
var CANVAS_HEIGHT = 750;
var colorCounter = 0;
var pngOn = false;
var courseName = "";
var searchIsOn = false;
var ajaxCallSent = false;
//array to store the added class sections as JSON objects
var courseRectangles = new Array();
var sectionsGrabbed = new Array();
var canvasItem, canvasContext;
var courseListing;
var xPos, yPos;
/**
* Array object to store the sections from selecting a course
* Makes use of the fact that data is already in the session object so
* no need to make another visit to controller or database.
*/
var sectionListing = new Array();


$(document).ready(function(){
	initializeCanvas();
});

/***********************************************
 * PNG STUFF
 * ********************************************/

/**
* Create a .png file of the canvas object allowing user to save schedule
* https://developer.mozilla.org/en-US/docs/DOM/HTMLCanvasElement
*/
function createImage(canvas){
	try{
		//Create a URL containing a representation of the image in the format specified
		//default is .png
		var url = canvas.toDataURL();
        //Grab the img div
        var imgDiv = document.getElementById("canvasImage");
		//Create the image object
        var newImg = document.createElement("img");
        //Update the img src and alt attributes
        newImg.src = url;
        newImg.height = 730;
        newImg.width = 780;
        newImg.alt = "Your Fall Class Schedule via Course Picker by Jane Ullah";
        imgDiv.appendChild(newImg);
	}catch(e){
		console.log("Failed to write image of canvas.");
	}
}
/***********************************************
 * LISTENER STUFF
 * ********************************************/
 /**
Method to detect if a click is within the boundaries of any of the
drawn class meetings on the canvas.
*/
function updateListeners(ctx,courseRectangles){
	//console.log(courseRectangles);
	$("#scheduleCanvas").on("click",function(e){
		var x, y;
		//http://stackoverflow.com/questions/12704686/html5-with-jquery-e-offsetx-is-undefined-in-firefox
		//retrieve coordinates of the click
		if (e.offsetX == undefined){
			x = Math.floor(e.pageX-$("#scheduleCanvas").offset().left);
			y = Math.floor(e.pageY-$("#scheduleCanvas").offset().top);
		}else{
			x = e.offsetX;
			y = e.offsetY;
		}
		try{
			//Loop through the list of rectangles (rectangles represent the class meeting)
			//and check for the point/click being within the boundary of the rectangle
			Object.keys(courseRectangles).forEach(function(key){
				//add double click listener to the class meeting
				var classmtg = courseRectangles[key];
				if (classmtg.xCoord < x && classmtg.yCoord < y &&
					classmtg.xCoord + CELL_WIDTH > x && classmtg.yCoord + classmtg.rectangleHeight > y){
					console.log("Point is within " + classmtg.callNumber);
					$('.courseinfo').remove();
					//pass absolute coordinates to triggerOverlay
					triggerOverlay(classmtg,e.pageX,e.pageY);
					//end iteration if the point click has been found within the boundaries of a class meeting on the schedule
					throw true;
				}
			});//end of foreach loop
		}catch(e){
			if (e!==true){
				console.log("Error enumerating list of sections.");
			}
		}
	});//end of anonymous function
}

/**
* Display popup div with course information
* when a drawn class meeting box/object on the canvas is clicked.
*/
function triggerOverlay(classmtg,x,y){
	//console.log("x: "+x+" y: "+y);
	//Get the div which houses the canvas
	var topMargin;
	var canvasDiv = document.getElementById("canvasDiv");
	//create div element which will contain the information about the meeting
	var courseinfodiv = document.createElement("div");
	courseinfodiv.setAttribute("class","courseinfo");
	courseinfodiv.setAttribute("id","courseInfoDiv");
	//Setting style attributes
	var leftMargin = classmtg.xCoord;
	//Set the y coordinate accordingly
	if (classmtg.yCoord > 480){
		topMargin = 250;
	}else{
		topMargin = 700-classmtg.yCoord;
	}
	var style = "margin-left: " + leftMargin+ "px; margin-top: -"+topMargin+"px;";
	courseinfodiv.setAttribute("style",style);
	//Add the text.
	var courseName = document.createTextNode(classmtg.courseName);
	var courseLecturer = document.createTextNode("Lecturer: "+classmtg.lecturer);
	var startTime = document.createTextNode("Start: "+classmtg.startHour+":"+classmtg.startMinute);
	var endTime = document.createTextNode("End: "+classmtg.endHour+":"+classmtg.endMinute);
	var courseCredit = document.createTextNode("Credits: "+classmtg.courseCredit);
	var br1 = document.createElement("br");
	var br2 = document.createElement("br");
	var br3 = document.createElement("br");
	var br4 = document.createElement("br");
	//add the text nodes and breakers to the newly created div.
	courseinfodiv.appendChild(courseName);
	courseinfodiv.appendChild(br1);
	courseinfodiv.appendChild(courseLecturer);
	courseinfodiv.appendChild(br2);
	courseinfodiv.appendChild(startTime);
	courseinfodiv.appendChild(br3);
	courseinfodiv.appendChild(endTime);
	courseinfodiv.appendChild(br4);
	courseinfodiv.appendChild(courseCredit);
	canvasDiv.appendChild(courseinfodiv);
}


/***********************************************
 * DRAWING STUFF
 * ********************************************/

/**
 * Return a starting point for
 * computing the coordinates to be drawn
 */
function findYCoord(startHour){
	if (startHour == 8){
		return CELL_HEIGHT;
	}else if (startHour == 9){
		return CELL_HEIGHT*2;
	}else if (startHour == 10){
		return CELL_HEIGHT*3;
	}else if (startHour == 11){
		return CELL_HEIGHT*4;
	}else if (startHour == 12){
		return CELL_HEIGHT*5;
	}else if (startHour == 13){
		return CELL_HEIGHT*6;
	}else if (startHour == 14){
		return CELL_HEIGHT*7;
	}else if (startHour == 15){
		return CELL_HEIGHT*8;
	}else if (startHour == 16){
		return CELL_HEIGHT*9;
	}else if (startHour == 17){
		return CELL_HEIGHT*10;
	}else if (startHour == 18){
		return CELL_HEIGHT*11;
	}else if (startHour == 19){
		return CELL_HEIGHT*12;
	}else if (startHour == 20){
		return CELL_HEIGHT*13;
	}else if (startHour == 21){
		return CELL_HEIGHT*14;
	}else{
		return CELL_HEIGHT*15;
	}
}


/*
 Draw a single class meeting on the canvas
 */
function drawClassMeeting(ctx) {
	//grab the list of sections from the UserSchedule in the request object
	//convert to a Javascript object using jQuery.parseJSON
	//http://api.jquery.com/jQuery.parseJSON/
	try{
		var schedule = jQuery.parseJSON(sched);
		//console.log(schedule);
		/*https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Object/keys
		https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/forEach
		Enumerate the sections by chaining Object.keys which returns an array of the passed-in values
		and Array.forEach() which executes the anonymous function.*/
		Object.keys(schedule).forEach(function(key){
			//method to parse a section and draw the section's meetings onto the canvas
			//console.log(schedule[key]);
			parseSection(schedule[key],ctx);
			colorCounter++;
			if (colorCounter == (colors.length-1)){
				colorCounter = 0;
			}
		});
	}catch(e){
		console.log("Internal error: jQuery could not convert the list of sections to a Javascript object. Please try again.");
	}
}

/**
* Method which takes in references to a Section object and the canvas context,
* figures out the coordinates
* and draws the class meetings on the canvas
*
*/
function parseSection(section,ctx) {
	var coursePrefix = section.coursePrefix;
	var courseNumber = section.courseNumber;
	var callNumber = section.callNumber;
	//Loop through the meeting times
	//For each meeting time inside the section object
	Object.keys(section.meetings).forEach(function(key){
		var meeting = section.meetings[key];
		var dash = meeting.indexOf("-");
		var startHour, startMinute, endHour,endMinute;
		//If the item starts with 0 e.g. 08, 09, parseInt returns 0 so
		//if there is 0 in front, call parseInt on a shorter substring.
		if (meeting.charAt(0) == 0){
			startHour = parseInt(meeting.substring(1,2));
		}else{
			startHour = parseInt(meeting.substring(0,2));
		}
		startMinute = parseInt(meeting.substring(2,dash));
		//If the item starts with 0 e.g. 01, 02, parseInt returns 0 so
		//if there is 0 in front, call parseInt on a shorter substring.
		if (meeting.substring(dash+1,dash+3).charAt(0) == 0){
			endHour = parseInt(meeting.substring(dash+2,dash+3));
		}else{
			endHour = parseInt(meeting.substring(dash+1,dash+3));
		}
		if (meeting.substring(dash+3,dash+5).charAt(0) == 0){
			endMinute = parseInt(meeting.substring(dash+4,dash+5));
		}else{
			endMinute = parseInt(meeting.substring(dash+3,dash+5));
		}
		var xCoord = 0;
		var yCoord = 0;

		//Set the x coordinates
		if (key.localeCompare("M") == 0){
			xCoord = CELL_WIDTH;
		} else if (key.localeCompare("T") == 0){
			xCoord = CELL_WIDTH*2;
		} else if (key.localeCompare("W") == 0){
			xCoord = CELL_WIDTH*3;
		} else if (key.localeCompare("R") == 0){
			xCoord = CELL_WIDTH*4;
		} else if (key.localeCompare("F") == 0){
			xCoord = CELL_WIDTH*5;
		}

		var startAMPM = meeting.charAt(4);
		var endAMPM = meeting.charAt(10);
		if (startAMPM == 'P' && startHour != 12){
			startHour += 12;
		}
		if (endAMPM == "P" && endHour != 12){
			endHour += 12;
		}
		//obtain the yCoordinate for the specific class meeting
		yCoord = findY(startHour,startMinute);
		//draw the class meeting to the canvas
		if (!isNaN(startHour) || !isNaN(endHour) || !isNaN(yCoord)){
			drawMeeting(xCoord,yCoord,ctx,coursePrefix+" "+courseNumber,callNumber,startMinute,endMinute,startHour,endHour,section.courseName,section.courseCredit,section.courseLecturer);
		}
	});
}

/**
* Return the yCoordinates based on the hour & minute
* assuming a fixed x coordinate
*/
function findY(hour,minute){
	var yCoord = findYCoord(hour);
	if (minute/15 == 0){
		yCoord = yCoord;
	}else if (minute/15 == 1){
		yCoord = yCoord + CELL_HEIGHT/4;
	}else if (minute/15 == 2){
		yCoord = yCoord + 2*(CELL_HEIGHT/4);
	}else if (minute/15 == 3){
		yCoord = yCoord + 3*(CELL_HEIGHT/4);
	}else if (minute/10 == 1){
		yCoord = yCoord + CELL_HEIGHT/6;
	}else if (minute/10 == 2){
		yCoord = yCoord + 2*(CELL_HEIGHT/6);
	}else if (minute/10 == 4){
		yCoord = yCoord + 4*(CELL_HEIGHT/6);
	}else if (minute/10 == 5){
		yCoord = yCoord + 5*(CELL_HEIGHT/6);
	}else if (minute/5 == 1){
		yCoord = yCoord + CELL_HEIGHT/12;
	}else if (minute/5 == 5){
		yCoord = yCoord + 5*(CELL_HEIGHT/12);
	}else if (minute/5 == 7){
		yCoord = yCoord + 7*(CELL_HEIGHT/12);
	}else if (minute/5 == 11){
		yCoord = yCoord + 11*(CELL_HEIGHT/12);
	}
	return yCoord;
}

/**
 * Function to calculate & return the height of the
 * class meeting to be drawn on the canvas
 */
function findHeight(hourDiff, startMin, endMin){
	var startPixels = CELL_HEIGHT - (startMin*CELL_HEIGHT)/60;
	var endPixels = (endMin*CELL_HEIGHT)/60;
	var block = CELL_HEIGHT;
	if (hourDiff > 1){
		block = hourDiff*CELL_HEIGHT;
		//return block+startPixels+endPixels;
		return block+endPixels
	}else if (hourDiff == 0){
		return ((endMin-startMin)*CELL_HEIGHT)/60;
	}else{
		return startPixels+endPixels;
	}
}

/**
*Draw a class meeting on the canvas
*e.g. the class meeting as a rectangle, informational text about the class meeting, etc
*/
function drawMeeting(x,y,ctx,text,callNumber,startMinute,endMinute,startHour,endHour,courseName,courseCredit,lecturer){
	//draw rectangle first
	ctx.fillStyle = colors[colorCounter];
	ctx.beginPath();
	var height = findHeight((endHour - startHour),startMinute,endMinute);
	ctx.rect(x,y,CELL_WIDTH,height);
	ctx.fill();
	//draw text on top of rectangle in this order!
	ctx.font = "16px Georgia";
	ctx.fillStyle = "#000000";
	ctx.textAlign = 'center';
	ctx.fillText(text,x+55,y+20);
	ctx.fillText(callNumber,x+40,y+35);
	//Finish out rectangle
	ctx.lineWidth = 2;
	ctx.strokeStyle = '#000000';
	ctx.stroke();
	//Store the coordinates of the drawn rectangles for use in click events
	var meetingObject = {"xCoord": x, "yCoord": y, "rectangleHeight": height,"callNumber": callNumber, "courseInfo": text, "courseName": courseName, "courseCredit": courseCredit, "lecturer": lecturer, "startHour": startHour, "startMinute": startMinute, "endHour": endHour, "endMinute": endMinute};
	courseRectangles.push(meetingObject);
}

/*
 Initialize the canvas with gridlines and labels
 */
function initializeCanvas() {
	var c = document.getElementById("scheduleCanvas");
	var ctx = c.getContext("2d");
	//Draw vertical lines
	for ( var x = CELL_WIDTH; x <= 650; x += CELL_WIDTH) {
		ctx.lineWidth = 2;
		ctx.strokeStyle = "#000000";
		ctx.moveTo(x, 0);
		ctx.lineTo(x, 750);
		ctx.stroke();
	}
	//Draw horizontal lines
	for ( var y = CELL_HEIGHT; y <= 730; y += CELL_HEIGHT) {
		ctx.lineWidth = 2;
		ctx.strokeStyle = "#000000";
		ctx.moveTo(0, y);
		ctx.lineTo(780, y);
		ctx.stroke();
	}
	ctx.font = "16px Georgia";
	var y = CELL_HEIGHT+30;

	//Draw hour text messengers
	for ( var counter = 0; counter < 14; counter++) {
		ctx.fillText(timesOfDay[counter], 25, y);
		y += CELL_HEIGHT;
	}

	//Draw Day text
	var x = CELL_WIDTH+10;
	for ( var counter = 0; counter < 5; counter++) {
		ctx.fillText(daysOfWeek[counter], x, 25);
		x += CELL_WIDTH+5;
	}
	drawClassMeeting(ctx);
	canvasContext = ctx;
	canvasItem = c;
	createImage(canvasItem);
	updateListeners(ctx,courseRectangles);
}


$(document).ready(function(){
	var item = $('#sectionItem');
	var cItem = $('#courseitem');
	var reqItem = $('#requirementId');
	$('html, body').css("cursor", "wait");

	//Dump out the course list and section list when requirement selection changes
	reqItem.change(function(){
		item.empty();
		item.append("<option value=\"0\">Select A Section</option.");
		$('#meetings').hide();
		$('#meetings').empty();
		sectionListings = "[]";
		if (reqItem.val() == 0){
			$('#message').html("Please select a requirement.");
		}else{
			$('html, body').css("cursor", "auto");
			$('#message').html("");
			//Submit the form if the user has selected a valid requirement
			$('#pickRequirement').submit();
		}
	});


	//Dump out section list when course selection changes
	cItem.change(function(){
		item.empty();
		item.append("<option value=\"0\">Select A Section</option.");
		$('#meetings').hide();
		$('#meetings').empty();
		sectionListings = "[]";
		if (cItem.val() == 0){
			$('#message').html("Please choose a course.");
		}else{
			$('html, body').css("cursor", "auto");
			$('#message').html("");
			$('#courseForm').submit();
		}
	});

	//Append the meeting times to the DOM when user makes a selection
	item.change(function(){
		if (item.val() != 0){
			$('#meetings').hide();
			$('#meetings').empty();
			var sections = jQuery.parseJSON(sectionListings);
			try{
				Object.keys(sections).forEach(function(key){
					var section = sections[key];
					//console.log("Item #: " + item.val() + " Section #:" + section.callNumber);
					if (section.callNumber == item.val()){
						$('html, body').css("cursor", "auto");
						$('#sectionMark').removeClass("hidden");
						var mtgs = section.meetings;
						$('#meetings').append("<ol id=\"meetingDisplay\">");
						Object.keys(mtgs).forEach(function(day){
							$('#meetings ol').append("<li>"+day + " " + mtgs[day]);
						});
						//Add Section button creation
						$('#meetings').append("<form id=\"addSectionForm\" name=\"addSectionForm\" action=\"classes/controllers/controller.php\" method=\"post\">");
						$('#meetings form').append("<input type=\"hidden\" name=\"add\" value=\""+section.callNumber+"\">");
						$('#meetings form').append("<input class=\"btn btn-primary\" type=\"submit\" id=\"addSectionButton\" value=\"Add Section\"></form>");
						//Disable the Add Section button for unavailable sections
						if (section.status != "Available"){
							//Disable the submit button and replace the action attribute link
							//for the clever git who re-enables the button. :) TODO: block adding
							//full or cancelled course on the backend as well.
							$('#addSectionButton').attr("disabled", "disabled");
							$('#addSectionButton').addClass("btn-danger");
							$('#addSectionButton').val(section.status);
							$('#addSectionForm').attr("action","#");
						}
						$('#meetings').show();
						throw true;
					}
				});
			}catch(e){
				if (e!==true){
					console.log("Error enumerating list of sections.");
				}
			}
		}
	});

	//Populate the list of currently enrolled sections
	if (sched.length > 0){
		//console.log(sched.length + " " + sched);
		$('#scheduleInfo').append("<form id=\"deleteForm\" name=\"deleteForm\" action=\"classes/controllers/controller.php\" method=\"post\">");
		$('#scheduleInfo form').append("<select id=\"deleteSectionItem\" name=\"deleteSectionItem\">");
		$('#scheduleInfo form select').append("<option value=\"0\">Select A Section</option>");
		var schedul = jQuery.parseJSON(sched);
		Object.keys(schedul).forEach(function(key){
			var s = schedul[key];
			$('#scheduleInfo form select').append("<option value=\""+s.callNumber+"\">"+s.coursePrefix+"-"+s.courseNumber+"</option>");
		});
		$('#scheduleInfo form').append("<input type=\"hidden\" value=\"delete\" name=\"delete\">");
		$('#scheduleInfo form').append("<input type=\"submit\" class=\"btn btn-danger\" value=\"Delete Section\">");
		$('#scheduleInfo').show();
	}else{
		console.log("no sections chosen.");
	}
});



/***************************************************
 *  CLICK TO SEARCH DATABASE (AJAXY VERSION)
 * *************************************************/
$(document).ready(function(){
	var msg;
	var typeahead = $('#courses');
	var item = $('#sectionItem2');
	$('html, body').css("cursor", "wait");
	//When a change is detected in the typeahead box
	typeahead.change(function(){
		if (typeahead.val().length >= 9){
			//parse value
			var value = typeahead.val();
			var dash = value.indexOf("-");
			//proper selection is of the form: XXXX-3452 or XXXX-2564L
			if (dash == 4){
				var split = value.split(" ");
				var prefix = split[0].substring(0,dash);
				var num = split[0].substring(dash+1);
				courseName = value.substring(dash+6).trim();
				//Change the value
				typeahead.val(prefix+"-"+num);
				//TODO: switch to AJAX
				//$('#sendCourse').submit();
				$.ajax({
					type: "POST",
					url: 'classes/controllers/controller.php',
					data: typeahead.serialize(),
					success: function(response){
						//response is JSON data of sections belonging to the course
						//update the global sectionListings object with the new values.
						//TODO:
						//console.log(response);
						$('html, body').css("cursor", "auto");
						sectionListings = response;
						ajaxCallSent = true;
						exposeSection(response);
					}
				});
			}else{
				msg = "Please enter or select a valid course.";
			}
		}else{
			msg = "Please select a valid course.";
		}
	});

	//When user selects a section from the dropdown box.
	item.change(function(){
		if (item.val() != 0){
			$('#meetings2').hide();
			$('#meetings2').empty();
			var sections = jQuery.parseJSON(sectionListings);
			try{
				Object.keys(sections).forEach(function(key){
					var section = sections[key];
					//console.log("Item #: " + item.val() + " Section #:" + section.callNumber);
					if (section.callNumber == item.val()){
						$('html, body').css("cursor", "auto");
						var mtgs = section.meetings;
						$('#meetings2').append("<ol id=\"meetingDisplay2\">");
						Object.keys(mtgs).forEach(function(day){
							$('#meetings2 ol').append("<li>"+day + " " + mtgs[day]);
						});
						//Add Section button creation
						$('#meetings2').append("<form id=\"addSectionForm2\" name=\"addSectionForm2\" action=\"classes/controllers/controller.php\" method=\"post\">");
						$('#meetings2 form').append("<input type=\"hidden\" name=\"add\" value=\""+section.callNumber+"\">");
						$('#meetings2 form').append("<input class=\"btn btn-primary\" type=\"submit\" id=\"addSectionButton2\" value=\"Add Section\"></form>");
						//Disable the Add Section button for unavailable sections
						if (section.status != "Available"){
							//Disable the submit button and replace the action attribute link
							//for the clever git who re-enables the button. :) TODO: block adding
							//full or cancelled course on the backend as well.
							$('#addSectionButton2').attr("disabled", "disabled");
							$('#addSectionButton2').addClass("btn-danger");
							$('#addSectionButton2').val(section.status);
							$('#addSectionForm2').attr("action","#");
						}
						$('#meetings2').show();
						throw true;
					}
				});
			}catch(e){
				if (e!==true){
					console.log("Error enumerating list of sections.");
				}
			}
		}
	});
});

/**
 * Expose the list of sections after user selects
 * a course from the typeahead input
 *
 */
function exposeSection(sectlistings){
	var item = $('#sectionItem2');
	$('#sectionItem2').empty().append("<option value=\"0\">Select A Section</option>");
	$('#courseName').empty().append(courseName).show();
	$('#meetings2').hide().empty();
	var sections = jQuery.parseJSON(sectlistings);
	try{
		Object.keys(sections).forEach(function(key){
			var section = sections[key];
			console.log(section);
			item.append("<option value=\""+section.callNumber+"\">"+section.coursePrefix+"-"+section.courseNumber+" Section # "+section.callNumber+"</option>");
		});
	}catch(e){
		if (e!==true){
			console.log("Error enumerating list of sections.");
		}
	}
}

/*********************************************
 *
 * FOR SAVING/RETRIEVING SCHEDULES
 * *******************************************/

$(document).ready(function(){
	var savelink = $('#saveSchedule');
	var sharelink = $('#shareSchedule');

	//SHARE SCHEDULE CODE
	sharelink.on('click',function(){
		$('#shareModalBody').empty();
		if (sched.length <= 0){
			$('#shareModalBody').append("Please add at least 1 section to your schedule.");
		}else{
			//Make ajax call to share schedule
			$.ajax({
				type: "POST",
				url: 'http://apps.janeullah.com/coursepicker/classes/controllers/sharingcontroller.php',
				data: { action : "Share"},
				success: function(response){
					if (response != -1){
						var url = "http://apps.janeullah.com/coursepicker/classes/controllers/sharingcontroller.php?schedule="+response;
						var msg = "<a href=\""+url+"\" title=\"Share your schedule\">"+"Share</a>";
						$('#shareModalBody').append(msg);
					}else{
						$('#shareModalBody').append("Problem retrieving short URL.");
					}
				}
			});
		}
	});
});
