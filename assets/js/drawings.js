		$(function(){
			$('#semesterSelection').change(function(){
				clearLocalStorage();
            	$('#semesterSelectionForm').submit();
			});	

			try{
				var schedule = $.parseJSON(sched);
				var size = Object.size(schedule);
				if (size > 0){
					$('#userSchedule').empty();
					Object.keys(schedule).forEach(function(key){
						var section = schedule[key];
						var classDiv = "<div class=\"individualSection\">";
						classDiv += "<span onclick=\"removeSection(" + section.courseNumber + ")\""  + " class=\"glyphicon glyphicon-remove pull-right\" style=\"margin-right:6px;margin-top:2px;font-size:140%;\"></span>";
						classDiv += "<span class=\"heading\">";
						classDiv += section.courseName + "</span>";
						classDiv += "<span class=\"row1 right\">" + section.lecturer + "</span><span class=\"row1 left\">" + section.coursePrefix + "-" + section.courseNumber + "</span><br/>";
						classDiv += "<span class=\"row2 right\">" +getBuildingName(section.buildingNumber) + " Room #" +  section.roomNumber+"</span><br/>";
						var mtgs = section.meetings;
						Object.keys(mtgs).forEach(function(key){
							classDiv += key + " : " + mtgs[key] + "<br/>";
						});
						classDiv += "</div>";
						$('#userSchedule').append(classDiv);
						//console.log(section);
					});
					$('#userSchedule').show();
				}
			}catch(e){
				console.log(e);
			}
		});

		
		function generateMeetingBlocks($mtg){

		}

		//http://stackoverflow.com/questions/5223/length-of-javascript-object-ie-associative-array
		Object.size = function(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) size++;
			}
			return size;
		};


		function clearLocalStorage() {
        	localStorage.clear();
        	return false;
    	}

		function populateSections(data){
			console.log(data);
			$('#sectionsFound').empty();
			$('#sectionFoundHeader').remove();
			var size = Object.keys(data).length;
			var counter = 0;
			var sectionDiv;
			var allSections = "";
			var heading = "<span id=\"sectionFoundHeader\" class=\"intro\"><span id=\"collapseColumn\" title=\"Collapse this column\" class=\"glyphicon glyphicon-arrow-up pull-left\"></span>Sections Found:<span class=\"badge pull-right\">" + size + "</span><br/></span>";
			/*Insert the head before the according div*/ 
			$('#sectionsFound').before(heading);
			//console.log(data);
			Object.keys(data).forEach(function(key){
				var section = data[key];
				sectionDiv = generateDiv(counter,section);
				allSections += sectionDiv;
				counter++;
			});
			/* Add the created divs to the main body of the accordion*/
			$('#sectionsFound').append(allSections);

			/* Add listener to the up/down sign for collapsing the column*/
			$('#collapseColumn').on('click',function(){
				var title = $('#collapseColumn').attr("title");
				if (title == "Collapse this column"){
					$('#collapseColumn').removeClass("glyphicon glyphicon-arrow-up pull-left").addClass("glyphicon glyphicon-arrow-down pull-left");
					$('#collapseColumn').attr("title","Expand this column");
					$('#sectionsFound').hide();
				}else{
					$('#collapseColumn').removeClass("glyphicon glyphicon-arrow-down pull-left").addClass("glyphicon glyphicon-arrow-up pull-left");
					$('#collapseColumn').attr("title","Collapse this column");
					$('#sectionsFound').show();
				}
			});
		}

		function addSectionIffy(callNumber){
			console.log(callNumber);
			$.ajax({
				type: "POST",
  				url: 'classes/controllers/schedulecontroller.php',
  				data: { action : "addSection", addSectionCallNumber : callNumber},
				dataType: "json"
	        })
			.done(function(msg){
  				console.log(msg);
				$('#errorMessage').show();
				$('#errorMessage').append(msg.errorMessage);

				if (msg.errorMessage = ""){
					alert("Empty");
				}
				setTimeout(function() {
					$('#errorMessage').fadeOut('fast');
				}, 5000);
			})
			.fail(function(msg){
				console.log("Error: " + msg.responseTextvalue);				
				/*Object.keys(msg).forEach(function(key){
					console.log("key: " + key + "value: " + msg[key]);
				});*/
			});
		}

		function addSection(){
			$('#addSectionForm').submit();
		}


		function removeSection(callNumber){
			console.log(callNumber);
			//$('#removeSectionForm').submit();
		}
		/*
		Retrieve the human-friendly version of UGA buildings.
		*/
		function getBuildingName(buildingNumber){
			var result = buildingNumber;
			try{
				Object.keys(uga_buildings).forEach(function(key){
					if (buildingNumber == key){
						result =  uga_buildings[key];
						//console.log("Found: " + result);
						throw true;
					}
				});
			}catch(e){
				if (e !== true){
					console.log("Error enumerating through list of buildings");
				}
			}
			return result;
		}

		/*
		 Generate divs for the accordion
		*/
		function generateDiv(index,section){
			var msg = "";
			msg += "<div class=\"panel panel-default\">";
			if (section.status === 'Available'){
				msg += "<div class=\"panel-heading\" style=\"background-color: #1C91FF\">";
			}else{
				msg += "<div class=\"panel-heading\" style=\"color:#ffffff;background-color: #DE0707\">";
			}
			msg += "<h4 class=\"panel-title\">";
			msg += "<a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse" + index+ "\">";
          	msg += section.courseName + " # " + section.callNumber + "</a></h4></div>";
			if (index > 0){
				msg += "<div id=\"collapse" + index + "\" class=\"panel-collapse collapse\">";
			}else{
				msg += "<div id=\"collapse" + index + "\" class=\"panel-collapse collapse in\">";
			}
      		msg += "<div class=\"panel-body\">";
			msg += "<span class=\"underline\">Lecturer</span>: " + section.lecturer + "<br/>";
			msg += "<span class=\"underline\">Status</span>: " + section.status + "<br/>";
			msg += "<span class=\"underline\">Building</span>: " + getBuildingName(section.buildingNumber) + "<br/>";
			msg += "<span class=\"underline\">Room</span>: " + section.roomNumber + "<br/>";
			msg += "<span class=\"underline\">Meetings</span><br/>";
			var mtgs = section.meetings;
			Object.keys(mtgs).forEach(function(key){
				msg += key + " : " + mtgs[key] + "<br/>";
			});
			if (section.status === 'Available'){
				msg += "<form name=\"addSectionForm\" id=\"addSectionForm\" method=\"post\" action=\"classes/controllers/schedulecontroller.php\">";
				msg += "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"addSection\"/>";
				msg += "<input type=\"hidden\" id=\"addSectionCallNumber\" name=\"addSectionCallNumber\" value=\"" + section.callNumber + "\"/>";
				msg += "<span title=\"Add this section to your schedule!\" onclick=\"addSection()\" class=\"glyphicon glyphicon-plus pull-right\"></span></form>";
			}
      		msg += "</div><!--panelBody--></div><!--panelCollapse--></div><!--panelDefault-->";
			return msg;
		}

