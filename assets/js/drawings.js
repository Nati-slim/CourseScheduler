var selectOptions;
		$(function(){
			$('#semesterSelection').change(function(){
				clearLocalStorage();
            	$('#semesterSelectionForm').submit();
			});	
			
			//Add schedule
			try{
				//console.log(sched);
				//Gets converted to JSON object
				schedule = $.parseJSON(sched);
				var size = Object.size(schedule);
				if (size > 0){
					$('#userSchedule').empty().append("<span class=\"intro\">Class Schedule</span>");
					Object.keys(schedule).forEach(function(key){
						var section = schedule[key];
						var classDiv = "<div id=\"schedule_" + section.callNumber + "\" class=\"individualSection\">";
						classDiv += "<span onclick=\"removeSection(" + section.callNumber + ")\""  + " class=\"glyphicon glyphicon-remove pull-right delete\"></span>";
						classDiv += "<form method=\"post\" action=\"classes/controllers/schedulecontroller.php\" id=\"removeSectionForm_" + section.callNumber + "\" name=\"removeSectionForm_" + section.callNumber + "\">";
						classDiv += "<input type=\"hidden\" name=\"action\" id=\"action\" value=\"removeSection\" />";
						classDiv += "<input type=\"hidden\" id=\"sectionToBeRemoved_" + section.callNumber + "\" name=\"sectionToBeRemoved\" value=\"" + section.callNumber + "\" />";
						classDiv += "</form>";
						classDiv += "<span class=\"heading\">";
						classDiv += section.courseName + "</a></span>";
						classDiv += "<span class=\"row1 right\">" + section.lecturer + "</span><span class=\"row1 left\">";
						
						var campus = $('#infoMessage').text();
						if (campus.indexOf("Athens") > 0){
							classDiv += "<a href=\"http://bulletin.uga.edu/Link.aspx?cid=" + section.coursePrefix + "" + section.courseNumber;
							classDiv += "\" title=\"UGA Bulletin Listing for " + section.courseName + "\">";
							classDiv += section.coursePrefix + "-" + section.courseNumber + "</a></span><br/>";
						}else{
							classDiv += section.coursePrefix + "-" + section.courseNumber + "</span><br/>";
						}
						classDiv += "<span class=\"row2\">" +getBuildingName(section.buildingNumber) + " - Room #" +  section.roomNumber+"</span>";
						classDiv += "<span class=\"meetingTimes\">";
						var mtgs = section.meetings;
						Object.keys(mtgs).forEach(function(key){
							classDiv += "<span title=\"" +mtgs[key] + "\" ";
							classDiv += "class=\"day\">" 
							classDiv += key + "</span>";
							//+ " : " + mtgs[key] + "<br/>";
						});
						classDiv += "</span></div>";						
						$('#userSchedule').append(classDiv);
						//console.log(section);
					}); //object keys
					var removeAll = "<input class=\"form-control rounded-corners\" onclick=\"removeAll()\" name=\"removeAllButton\" id=\"removeAllButton\" type=\"submit\" value=\"Remove All\" />";
					$('#userSchedule').append(removeAll);
					$('#userSchedule').show();
					addMouseOverEffects();
				}
			}catch(e){
				console.log(e);
			}
			
		});
		
		/* Function called when user clicks the Remove All Button*/
		function removeAll(){
			$('#removeAllSectionInfo').show();
			$('body').css('cursor', 'wait');
			$.ajax({
				type: "POST",
				url: 'classes/controllers/schedulecontroller.php',
				data: { action : "removeAllSections"},
				dataType: "json"
			})
			.done(function(msg){
				$('body').css('cursor', 'auto');
				console.log(msg);
				if (msg.errorMessage.length == 0){
					$('#errorMessage').empty().hide();
					setTimeout(function(){
						location.reload();
					},1000);
				}else{
					$('#errorMessage').show().append(msgObj.errorMessage);
				}
			})
			.fail(function(msg){
				$('body').css('cursor', 'auto');
				console.log("Error: " + msg.responseTextvalue);				
			});
		}

		/* 
		 * Mouseover effect for user hovering over the days
		 * 
		 * */
		function addMouseOverEffects(){
			$('.day').bind('mouseover',function(e){
				$(this).addClass('hover');
			});	
					
			$('.day').bind('mouseout',function(e){
				$(this).removeClass('hover');
			});	
		}

		//http://stackoverflow.com/questions/5223/length-of-javascript-object-ie-associative-array
		Object.size = function(obj) {
			var size = 0, key;
			for (key in obj) {
				if (obj.hasOwnProperty(key)) size++;
			}
			return size;
		};

		/* 
		 * force localStorage removal of cached JSON object
		 * when user changes campuses
		 * */
		function clearLocalStorage() {
        	localStorage.clear();
        	return false;
    	}
    	
    	/* Add listener to checkboxes and return an array of currently selected items*/
    	function addCheckboxListener(){
			$('.checkedElement').change(function(){
				$('body').css('cursor', 'wait');
				$.ajax({
					type: "POST",
  					url: 'classes/controllers/coursecontroller.php',
  					data: { action : "filterSections", available: $('#Available').is(":checked"), full : $('#Full').is(":checked"), cancelled : $('#Cancelled').is(":checked")},
					dataType: "json"
				})
				.done(function(msg){
					$('body').css('cursor', 'auto');
					//console.log(msg);
					sListings = msg;
  					populateSections(msg);
				})
				.fail(function(msg){
					$('body').css('cursor', 'auto');
					console.log(msg + "Error getting sections.");
				});
			});			
		}
		

		/*
		 * Called after submission of user's course-number selection
		 * 
		 **/
		function populateSections(data){
			//console.log(data);
			var size = Object.keys(data).length;
			//console.log(size);
			if (size != 0){
				$('#controlCheckboxes').show();
				$('#sectionsFound').empty().show();
				$('#sectionFoundHeader').remove();
				var title = $('#collapseColumn').attr("title");
				if (title == "Collapse this column"){
					expandDiv();
				}
				var counter = 0;
				var sectionDiv;
				var allSections = "";
				sListings = data;
				var heading = "<span id=\"sectionFoundHeader\" class=\"intro\"><span id=\"collapseColumn\" title=\"Collapse this column\" class=\"glyphicon glyphicon-arrow-up pull-left\"></span>Sections Found:<span class=\"badge pull-right notification\">" + size + "</span><br/></span>";
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
					title = $('#collapseColumn').attr("title");
					if (title == "Collapse this column"){
						expandDiv();
					}else{
						collapseDiv();
					}
				});			
				addCheckboxListener();
			}else{				
				//$('#controlCheckboxes').hide();
				$('#sectionsFound').empty().show();
				$('#sectionFoundHeader').remove();
				$('#sectionsFound').append("<p class=\"alert-info\">No sections found.</p>");
			}
		}
		
		function collapseDiv(){
			$('#collapseColumn').removeClass("glyphicon glyphicon-arrow-down pull-left").addClass("glyphicon glyphicon-arrow-up pull-left");
			$('#collapseColumn').attr("title","Collapse this column");
			$('#sectionsFound').show("slow",function(){});
		}
		
		function expandDiv(){
			$('#collapseColumn').removeClass("glyphicon glyphicon-arrow-up pull-left").addClass("glyphicon glyphicon-arrow-down pull-left");
			$('#collapseColumn').attr("title","Expand this column");
			$('#sectionsFound').hide("slow",function(){});
		}
		
		/* Add a single section*/
		function addSection(callNumber){
			var formName = "#addSectionForm_" + callNumber;
			console.log(formName);
			$(formName).submit();
		}

		/*
		 * Remove a single section
		 */ 
		function removeSection(callNumber){
			var formName = "#removeSectionForm_" + callNumber;
			console.log(formName);
			$('body').css('cursor', 'wait');
			$.ajax({
				type: "POST",
  				url: 'classes/controllers/schedulecontroller.php',
  				data: { action : "removeSection", sectionToBeRemoved : callNumber},
				dataType: "json"
	        })
			.done(function(msg){
				$('body').css('cursor', 'auto');
				var schedSectionID = "#schedule_" + callNumber;
				$(schedSectionID).hide('slow', function(){ 
					$(schedSectionID).remove(); 
				});
				setTimeout(function () { location.reload(true); }, 1000);
  			})
  			.fail(function(msg){
				console.log("Error: " + msg.responseTextvalue);
			});
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
				msg += "<div class=\"panel-heading available\">";
			}else{
				msg += "<div class=\"panel-heading notavailable\">";
			}
			msg += "<h4 class=\"panel-title\">";
			msg += "<a data-toggle=\"collapse\" data-parent=\"#accordion\" href=\"#collapse" + index+ "\">";
          	msg += section.courseName + " # " + section.callNumber + "</a></h4></div>";
			if (index > 0){
				msg += "<div id=\"collapse" + index + "\" class=\"panel-collapse collapse\">";
			}else{
				//expand just the first section 
				msg += "<div id=\"collapse" + index + "\" class=\"panel-collapse collapse in\">";
			}
      		msg += "<div class=\"panel-body\">";
      		msg += "<span class=\"row1 right\">" + section.lecturer + "</span>";
      		msg += "<span class=\"row1 left\">";
      		//link the class url
			var campus = $('#infoMessage').text();
			if (campus.indexOf("Athens") > 0){
				msg += "<a href=\"http://bulletin.uga.edu/Link.aspx?cid=" + section.coursePrefix + "" + section.courseNumber;
				msg += "\" title=\"UGA Bulletin Listing for " + section.courseName + "\">";
				msg += section.coursePrefix + "-" + section.courseNumber + "</a></span><br/>";
			}else{
				msg += section.coursePrefix + "-" + section.courseNumber + "</span><br/>";
			}
			//change color depending on availability
			if (section.status == "Available"){
				msg += "<span class=\"row1 right available\">" + section.status + "</span>";
			}else if (section.status == "Full"){
				msg += "<span class=\"row1 right full\">" + section.status + "</span>";
			}else{
				msg += "<span class=\"row1 right cancelled\">" + section.status + "</span>";
			}
			//Slots opens
			msg += "<span class=\"row1 left\">" + section.casTaken + "/" + section.casRequired + "</span><br/>";
			msg += "<span class=\"row1 right\">" +  getBuildingName(section.buildingNumber) + "</span>";
			msg += "<span class=\"row1 left\">" + section.roomNumber +  "</span><br/>";
			var mtgs = section.meetings;
			Object.keys(mtgs).forEach(function(key){
				msg += key + " : " + mtgs[key] + "<br/>";
			});
			if (section.status === 'Available'){
				msg += "<form name=\"addSectionForm_" + section.callNumber + "\" id=\"addSectionForm_" + section.callNumber + "\" method=\"post\" action=\"classes/controllers/schedulecontroller.php\">";
				msg += "<input type=\"hidden\" id=\"action\" name=\"action\" value=\"addSection\"/>";
				msg += "<input type=\"hidden\" id=\"addSectionCallNumber_" + section.callNumber  + "\" name=\"addSectionCallNumber\" value=\"" + section.callNumber + "\"/>";
				msg += "<span title=\"Add this section to your schedule!\" onclick=\"addSection(" + section.callNumber + ")\" class=\"glyphicon glyphicon-plus pull-right plus-sign\"></span></form>";
			}
      		msg += "</div><!--panelBody--></div><!--panelCollapse--></div><!--panelDefault-->";
      		//console.log(msg);
			return msg;
		}
