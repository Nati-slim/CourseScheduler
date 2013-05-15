CourseScheduler
===============

My goal is to create a limited course scheduling application geared towards students at UGA in the Franklin College of Arts & Sciences. I'm learning Object Oriented Programming using PHP so please bear with me. The inspiration for this comes from a Web Programming group project where this same goal was achieved using Java. 

There will be architectural and design differences from this group project and to my knowledge, I'm not violating the honor code by putting this online.

5/5/2013 - v 0.1 released
5/10/2013 - v. 0.2 released


5/5/2013
========

1. Adding & Deletion of sections
2. Saving your schedule as a .png file works
3. Added a contact form with recaptcha for sending comments/questions my way
4. Commented php files as much as possible but please ask away if anything is not comprehensible
5. Saves the user's schedule in the $_SESSION object which means if you clear your cookies, the schedule will be gone as well.


5/10/2013
=========

1. Added a tabbed pane so the user can add all courses available for the Fall 2013 schedule to their list instead of being limited by my original interface
2. Updated contact form on schedule.php
3. Pulled out common css/js files into resources.inc and including this file in all the php pages that need access to the css/js files.


TODO
====
1. Work on the usability of the web application. Specifically, considering the addition of a "guided tour" or making sure that the flow of the app's usage is clear i.e. you submit your requirement selection, you submit your course selection and then you can select a section to view the times.
2. <del>Along with the usability, revisit the possibility of using AJAX calls and/or reduce the number of manual actions that the user has to take.</del>
