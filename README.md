# Test task
Technological stack: PHP, Laravel.

## Extra
I done this task with real data. Ukrainian Holidays was load from Wikipedia.
This task still has "Easter issue".

## Description
You should develop very basic RESTful web service. This service should receive some date from user and check it for certain holidays. For example, user enters date 01.01.2021. Service checks this date and outputs statement like that: “It’s New Year on that date!”.

UI should be very simple: just one field for date and one button to submit this date for processing. And don’t forget about validation! System should not fall down in case of incorrect date filled.

## Technical details
All holidays must be stored in one array. To add new holiday or edit existing one - only array should be modified. All other code must remain untouched.

Date of holiday must not depend on year. Also, all elements in holidays array must have same structure. In other words, array must be easily portable to the database.

You should use MVC pattern. Business logic should be separated from controller. The perfect way is to create certain php class (laravel service) which will include all required business logic.

You should not use tricks like “second day of may” and similar within “strtotime” function. However using of “strtotime” function for other needs is permitted.

If holiday falls on Saturday or Sunday, the additional day off will be on nearest Monday. For example, Easter is always celebrated on Sunday and next Monday is always an official day off.

## List of holidays
Below you can find list of holidays. You must include all of them in your web service.

* 1st of January
* 7th of January
* From 1st of May till 7th of May
* Monday of the 3rd week of January
* Monday of the last week of March
* Thursday of the 4th week of November
