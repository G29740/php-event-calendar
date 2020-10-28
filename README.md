# php-event-calendar
![image](https://user-images.githubusercontent.com/48676469/97444380-0fa92000-1967-11eb-9c18-cd748e931141.png)
A simple PHP script to fetch event details from a database and display in a calendar using Bootstrap 4, jQuery, Ajax and MySQL.

## Description
Features included are:
- Connection to database to retrieve events
- Clickable navigation buttons to display the previous and next month without refreshing the page (Ajax)
- Highlight of current date
- Highlight of days with an event
- Creation of Bootstrap modal dialogs for each event to contain details of event such as topic, location and start/end time
- Clickable cell with an event to display the related modal dialog
- Display of error messages

## How to use
- Setting up database:
  - Create the database (example "calendar_db") then create a table to contain event records
  ```
  CREATE TABLE `event` (
    `id` int(10) NOT NULL,
    `topic` text NOT NULL,
    `date` date NOT NULL,
    `streetnumber` int(5) NOT NULL,
    `streetname` varchar(100) NOT NULL,
    `postalcode` varchar(10) NOT NULL,
    `locality` varchar(100) NOT NULL,
    `startTime` time NOT NULL,
    `endTime` time NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
  ```
  - Add some sample data:
  ```
  INSERT INTO `event` (`id`, `topic`, `date`, `streetnumber`, `streetname`, `postalcode`, `locality`, `startTime`, `endTime`) VALUES
  (1, 'Topic 1\r\n1234567890', '2020-11-15', 1, 'Street name', '1234', 'Locality', '12:00:00', '15:00:00'),
  (2, 'AAAAAAA\r\nBBBBBBB\r\nCCCCCCC\r\nDDDDDDD', '2020-11-30', 999, 'Abcd', 'AA5 FF9', 'Abcd defg', '01:00:00', '20:00:00');
  ```
  - Set column `id` as primary key:
  ```
  ALTER TABLE `event`
  ADD PRIMARY KEY (`id`);
  ```
  - Set auto-increment on `id`
  ```
  ALTER TABLE `event`
    MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
  COMMIT;
  ```
- Open `dbconnection.php` and configure the connection details to your database on **line 6**
- Create a test HMTL page (example `index.php`) to call the `calendar.php` file:
  - Include references of Bootstrap 4 CSS and JS, jQuery (CDN can be used)
  - Include the reference of the `calendar.js` script
- Apply CSS on the calendar for better visuals if needed:
  - Use the selector `#calendar` to style the calendar container
  - Use the selector `#calendar .prev` or `#calendar .next` to style the arrow buttons
  - Use the selector `#calendar .calendar-day-names-header` to style the header with day names
  - Use the selector `#calendar .calendar-days td` to style the cells (days)
  - Use the selectors `#calendar .eventday`, `#calendar .today`, `#calendar .emptyday` to respectively style the cells with days with events, the cell with has today's day and the cells which are empty
