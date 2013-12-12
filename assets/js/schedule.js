
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
var url;
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
