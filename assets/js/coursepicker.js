//Initialized variables
var colors = [ "#D9EDF7", "#0D63B6","#CC333F","#317A22","#E5924C","#C34B5F","#4682B4","#228B22","#EE3B3B","#6E7B8B","#7584B5" ];
var daysOfWeek = [ "Monday", "Tuesday", "Wednesday", "Thursday", "Friday","Saturday" ];
var timesOfDay = [ "8:00am", "9:00am", "10:00am", "11:00am", "12:00pm",
			"1:00pm", "2:00pm", "3:00pm", "4:00pm","5:00pm","6:00pm","7:00pm","8:00pm","9:00pm","10:00pm" ];
//var sched = '{"8363":{"courseName":"ENGLISH COMP I","coursePrefix":"ENGL","courseNumber":"1101","callNumber":8363,"lecturer":"No Professor","status":"Available","buildingNumber":56,"roomNumber":"0060","casTaken":9,"casRequired":20,"campus":"UNIV","semester":"201402","meetings":{"M":"0905A-0955A","W":"0905A-0955A","F":"0905A-0955A"},"errorMessage":""},"5432":{"courseName":"INTRO AFRI AMER ST","coursePrefix":"AFAM","courseNumber":"2000","callNumber":5432,"lecturer":"MORROW","status":"Available","buildingNumber":53,"roomNumber":"0230","casTaken":24,"casRequired":30,"campus":"UNIV","semester":"201402","meetings":{"T":"1100A-1215P","R":"1100A-1215P"},"errorMessage":""},"91181":{"courseName":"ADR SEMINAR","coursePrefix":"JURI","courseNumber":"5735","callNumber":91181,"lecturer":"BURCH","status":"Available","buildingNumber":45,"roomNumber":"0203","casTaken":19,"casRequired":100,"campus":"UNIV","semester":"201402","meetings":{"T":"0230P-0420P"},"errorMessage":""}}';
//var tempSched = '{"8363":{"courseName":"ENGLISH COMP I","coursePrefix":"ENGL","courseNumber":"1101","callNumber":8363,"lecturer":"No Professor","status":"Available","buildingNumber":56,"roomNumber":"0060","casTaken":9,"casRequired":20,"campus":"UNIV","semester":"201402","meetings":{"M":"0905A-0955A","W":"0905A-0955A","F":"0905A-0955A"},"errorMessage":""},"5432":{"courseName":"INTRO AFRI AMER ST","coursePrefix":"AFAM","courseNumber":"2000","callNumber":5432,"lecturer":"MORROW","status":"Available","buildingNumber":53,"roomNumber":"0230","casTaken":24,"casRequired":30,"campus":"UNIV","semester":"201402","meetings":{"T":"1100A-1215P","R":"1100A-1215P"},"errorMessage":""},"91181":{"courseName":"ADR SEMINAR","coursePrefix":"JURI","courseNumber":"5735","callNumber":91181,"lecturer":"BURCH","status":"Available","buildingNumber":45,"roomNumber":"0203","casTaken":19,"casRequired":100,"campus":"UNIV","semester":"201402","meetings":{"T":"0230P-0420P"},"errorMessage":""}}';
//var sListings = '{"91181":{"courseName":"ADR SEMINAR","coursePrefix":"JURI","courseNumber":"5735","callNumber":91181,"lecturer":"BURCH","status":"Available","buildingNumber":45,"roomNumber":"0203","casTaken":19,"casRequired":100,"campus":"UNIV","semester":"201402","meetings":{"T":"0230P-0420P"},"errorMessage":""}}';
//Declared variables
var ctx, CANVAS_WIDTH, CANVAS_HEIGHT, CELL_WIDTH, CELL_HEIGHT;
var schedObj, colorCounter = 0;
//arrays
var meetings = [];


$(document).ready(function(){
    CANVAS_WIDTH = $('canvas').width();
    CANVAS_HEIGHT = $('canvas').height();
    CELL_WIDTH = CANVAS_WIDTH/daysOfWeek.length;
    CELL_HEIGHT = CANVAS_HEIGHT/timesOfDay.length;
	drawTable();
    getSchedule();
    ctx.renderAll();
});

function getSchedule(){
    try{
        schedObj = JSON.parse(sched);
        Object.keys(schedObj).forEach(function(key){
           parseSection(schedObj[key]); 
        });
    }catch(e){
        console.log(e);
        console.log("Couldn't parse sections");
    }
}

function parseSection(section){
    colorCounter++;
    if (colorCounter == (colors.length-1)){
        colorCounter = 0;
    }    
	var coursePrefix = section.coursePrefix;
	var courseNumber = section.courseNumber;
	var callNumber = section.callNumber;
	//Loop through the meeting times
	//For each meeting time inside the section object
    try{
        Object.keys(section.meetings).forEach(function(key){
            var meeting = section.meetings[key];
            var dash = meeting.indexOf("-");
            var startHour, startMinute, endHour,endMinute;        
            var left = CELL_WIDTH/2 + 5;
            var top = 0;
            width = 120;
            var height = 0;
            
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

            //Set the starting point for fabric to start drawing        
            if (key.localeCompare("M") == 0){
                left = left;
                width = 115;
            } else if (key.localeCompare("T") == 0){
                left = left - 5 + CELL_WIDTH;
            } else if (key.localeCompare("W") == 0){
                left = left - 5 + CELL_WIDTH*2;
            } else if (key.localeCompare("R") == 0){
                left = left - 5 + CELL_WIDTH*3;
            } else if (key.localeCompare("F") == 0){
                left = left - 5 + CELL_WIDTH*4;
            } else if (key.localeCompare("S") == 0){
                left = left - 5 + CELL_WIDTH*5;
            }

            var startAMPM = meeting.charAt(4);
            var endAMPM = meeting.charAt(10);
            if (startAMPM == 'P' && startHour != 12){
                startHour += 12;
            }
            if (endAMPM == "P" && endHour != 12){
                endHour += 12;
            }

            height = getRectangleHeight(startHour,startMinute,endHour,endMinute);
            top = getYCoordinate(startHour,startMinute,key);
            //draw rectangle
            var rect = new fabric.Rect({
                left: left,
                top: top,
                fill: colors[colorCounter],
                width: width,
                height: height,
                rx: 10,
                ry: 10
            });
            //setting options for messing with the rectangle
            rect.set({
                selectable: false,
                borderColor: 'black',
                lockRotation: true,
                lockScalingX: true,
                lockScalingY: true
            });
            
            //draw text
            var sectionInfo = section.coursePrefix 
            var sectionInfo = new fabric.Text(coursePrefix + " " + courseNumber, {
                left: left + 10, 
                top: top,
                fontSize: 16,
                fontWeight: 'bold',
                textAlign: 'left',
                textTransformation: 'uppercase',
                fill: '#ffffff',
                fontFamily: 'Helvetica Neue'
            });
            sectionInfo.set('selectable',false);    
            
            var group = new fabric.Group([rect,sectionInfo],{
                left: left,
                top: top
            });  
            

            group.on('selected', function() {
                console.log('selected section ' + section.callNumber);
            });
            //group.set('selectable',false);     
            ctx.add(group);
        });
    }catch(e){
        console.log("error in parse section.");
        console.log(e);
    }
}

/**
 *  For fabric, top is offset from the top.
 * This returns the top value
 */
function getYCoordinate(startHour,startMinute,dayofWeek){
    var top = 30
	if (startHour == 8){
		return top;
	}else if (startHour == 9){
        if (startMinute == 0){
            return CELL_HEIGHT + top;
        }else{
            var extra = startMinute * 48/50;
            return CELL_HEIGHT + top + parseInt(extra);
        }
		
	}else if (startHour == 10){        
        if (startMinute == 0){
            return (CELL_HEIGHT*2) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*2) + top + parseInt(extra);
        } 
	}else if (startHour == 11){
        if (startMinute == 0){
            return (CELL_HEIGHT*3) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*3) + top + parseInt(extra);
        } 
	}else if (startHour == 12){
        if (startMinute == 0){
            return (CELL_HEIGHT*4) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*4) + top + parseInt(extra);
        } 
	}else if (startHour == 13){
        if (startMinute == 0){
            return (CELL_HEIGHT*5) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*5) + top + parseInt(extra);
        }  
	}else if (startHour == 14){
        if (startMinute == 0){
            return (CELL_HEIGHT*6) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*6) + top + parseInt(extra);
        }        
	}else if (startHour == 15){
        if (startMinute == 0){
            return (CELL_HEIGHT*7) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*7) + top + parseInt(extra);
        } 
	}else if (startHour == 16){
        if (startMinute == 0){
            return (CELL_HEIGHT*8) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*8) + top + parseInt(extra);
        } 
	}else if (startHour == 17){
        if (startMinute == 0){
            return (CELL_HEIGHT*9) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*9) + top + parseInt(extra);
        } 
	}else if (startHour == 18){
        if (startMinute == 0){
            return (CELL_HEIGHT*10) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*10) + top + parseInt(extra);
        } 
	}else if (startHour == 19){
        if (startMinute == 0){
            return (CELL_HEIGHT*11) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*11) + top + parseInt(extra);
        } 
	}else if (startHour == 20){
        if (startMinute == 0){
            return (CELL_HEIGHT*12) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*12) + top + parseInt(extra);
        } 
	}else if (startHour == 21){
        if (startMinute == 0){
            return (CELL_HEIGHT*13) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*13) + top + parseInt(extra);
        } 
	}else if (startHour == 22){
        if (startMinute == 0){
            return (CELL_HEIGHT*14) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*14) + top + parseInt(extra);
        } 
	}else{
        if (startMinute == 0){
            return (CELL_HEIGHT*15) + top;
        }else{
            var extra = startMinute * 48/50;
            return (CELL_HEIGHT*15) + top + parseInt(extra);
        } 
    }
}

/**
 *
 * This just returns the height of the rectangle
 */ 
function getRectangleHeight(startHour,startMinute,endHour,stopMinute){
    var hourDiff = endHour - startHour;
    var minDiff = startMinute;
    
    if (endHour > startHour){
        hourDiff = endHour - startHour;
    }
    
    if (stopMinute > startMinute){
        minDiff = stopMinute - startMinute;
    }else if (stopMinute < startMinute){
        minDiff = startMinute - stopMinute;
    }
    //translate minDiff into pixel differences
    //cell height = 48, 60 mins in 1 hr
    return (CELL_HEIGHT * hourDiff) + (minDiff * 48/50);
}


function drawTable(){
    // create a wrapper around native canvas element (with id="c")
    ctx = new fabric.Canvas('scheduleCanvas', {
          backgroundColor: 'rgb(255,255,255)',
          selectionColor: '#129F68',
          selectionLineWidth: 2
    }); 
    ctx.selection = false;
    
    //Draw day row
    var line = new fabric.Line([0,30,CANVAS_WIDTH,30],{
        left:0,
        top:30,
        stroke: 'black'
    });
    line.set('selectable',false);
    ctx.add(line);
        
    //Draw time column
    var line = new fabric.Line([CELL_WIDTH/2,0,CELL_WIDTH/2,CANVAS_HEIGHT],{
        left:CELL_WIDTH/2,
        top:0,
        stroke: 'black'
    });
    line.set('selectable',false);
    ctx.add(line);
        
	//Draw vertical lines
    CELL_WIDTH = 120;
	for ( var x = CELL_WIDTH + CELL_WIDTH/2; x <= CANVAS_WIDTH - CELL_WIDTH; x += CELL_WIDTH) {
        var line = new fabric.Line([x, 0, x, CANVAS_HEIGHT], { 
            left: x,
            top: 0,
            stroke: 'black'
        });
        line.set('selectable',false);
        ctx.add(line);
	}

    
	//Draw horizontal lines
    CELL_HEIGHT = 48;
	for ( var y = (CELL_HEIGHT + 30); y <= CANVAS_HEIGHT - CELL_HEIGHT; y += CELL_HEIGHT) {
        var line = new fabric.Line([0, y, CANVAS_WIDTH, y], { 
            left: 0,
            top: y,
            stroke: 'black'
        });
        line.set('selectable',false);
        ctx.add(line);
	}
    

 	//Draw hour text messengers
    var y = 30;    
	for ( var counter = 0; counter < timesOfDay.length; counter++) {
		var time = new fabric.Text(timesOfDay[counter], {
            left: 2, 
            top: y+10,
            fontSize: 16,
            fontWeight: 'bold',
            textAlign: 'left',
            fill: '#000000',
            fontFamily: 'Helvetica Neue'
        });
        time.set('selectable',false);
        y += CELL_HEIGHT;
		ctx.add(time);
	}
    
 	//Draw days text messengers
	var x = CELL_WIDTH/2 + 30;
	for ( var counter = 0; counter < daysOfWeek.length; counter++) {
		var time = new fabric.Text(daysOfWeek[counter], {
            left: x, 
            top: 5,
            fontSize: 16,
            fontWeight: 'bold',
            textAlign: 'left',
            textTransformation: 'uppercase',
            fill: '#000000',
            fontFamily: 'Helvetica Neue'
        });
        time.set('selectable',false);
		x += CELL_WIDTH;
		ctx.add(time);
	}    
       
    var labeledRect = new LabeledRect({
      width: 100,
      height: 50,
      left: 100,
      top: 100,
      label: 'test',
      fill: '#aaf'
    });

    //ctx.add(labeledRect);   
    
    //Add trashcan icon
    var img = document.getElementById('trashcan');//$('#trashcan').get();
    var imgInstance = new fabric.Image(img, {
        left: 0,
        top: 0,
        opacity: 1
    });
    imgInstance.set({
        selectable: false
    });
    ctx.add(imgInstance); 
    //draw objects
    ctx.renderAll();
}

var LabeledRect = fabric.util.createClass(fabric.Rect, {

  type: 'labeledRect',

  initialize: function(options) {
    options || (options = { });

    this.callSuper('initialize', options);
    this.set('label', options.label || '');
    this.set({ rx: 10, ry: 10 });
  },

  toObject: function() {
    return fabric.util.object.extend(this.callSuper('toObject'), {
      label: this.get('label')
    });
  },

  _render: function(ctx) {
    this.callSuper('_render', ctx);

    // make font and fill values of labels configurable
    /*ctx.font = this.labelFont;
    ctx.fillStyle = this.labelFill;*/
    ctx.font = '20px Helvetica';
    ctx.fillStyle = '#333';    
    ctx.fillText(this.label, -this.width/2, -this.height/2 + 20);
  }
});

