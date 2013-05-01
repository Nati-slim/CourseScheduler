var daysOfWeek = [ "Monday", "Tuesday", "Wednesday", "Thursday", "Friday" ];
var timesOfDay = [ "8:00am", "9:00am", "10:00am", "11:00am", "12:00pm",
			"1:00pm", "2:00pm", "3:00pm", "4:00pm","5:00pm","6:00pm","7:00pm","8:00pm","9:00pm" ];
var TOP_MARGIN = "0px";
var LEFT_MARGIN = "0px";
var RIGHT_MARGIN = "0px";
var BOTTOM_MARGIN = "0px";
var CELL_WIDTH = 130;
var CELL_HEIGHT = 50;
var CANVAS_WIDTH = 780;
var CANVAS_HEIGHT = 750;

$(document).ready(function(){
	initializeCanvas();
});

/*
 Draw a single class meeting on the canvas
 */
function drawClassMeeting(ctx) {
	//grab the list of sections from the UserSchedule in the request object
	//convert to a Javascript object using jQuery.parseJSON
	//http://api.jquery.com/jQuery.parseJSON/
	try{
		var schedule = jQuery.parseJSON(sched);
		console.log(schedule);
		/*https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Object/keys
		https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/Array/forEach
		Enumerate the sections by chaining Object.keys which returns an array of the passed-in values
		and Array.forEach() which executes the anonymous (yeah, I know Dr. Dan doesn't like this) for each element
		of the array returned by Object.keys
		TODO: use a named function not anonymous function.*/
		//console.log(schedule);
		Object.keys(schedule).forEach(function(key){
			//debug printout
			console.log(schedule[key]);
			//method to parse a section and draw the section's meetings onto the canvas
			//parseSection(schedule[key],ctx);
		});
	}catch(e){
		console.log("Internal error: jQuery could not convert the list of sections to a Javascript object. Please try again.");
	}
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
		ctx.strokeStyle = "#634f44";
		ctx.moveTo(x, 0);
		ctx.lineTo(x, 750);
		ctx.stroke();
	}
	//Draw horizontal lines
	for ( var y = CELL_HEIGHT; y <= 730; y += CELL_HEIGHT) {
		ctx.lineWidth = 2;
		ctx.strokeStyle = "#634f44";
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
}
