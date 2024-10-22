<h1 align="center">
DogelyChain
<br><br>
</h1>

## What is the DogelyChain

Its a Opensource blockchain analytics for tracking all Dogecoin Miners and Where the money flows to see how decentralized the Doge Blockchain really is

## How to Install 💻

1- Get an Linux Hosting Account or Web Server that supports ```PHP (V. 8 =>)``` + ```MySQL/MariaDB``` (also works locally with Docker or Xampp for exemple)

2- Upload all files (excluding dogelychain.sql and readme.md) to your Hosting Account.

3- Now edite the file ```inc/config.php``` and add your database conections and Dogecoin Core Node settings

4- Create two cron tasks to run every minute to poin to ```inc/blocks-cron.php``` and for ```inc/miners-cron.php```

5- Import the ```dogelychain.sql``` to your ```MySQL/MariaDB``` server

###Notes:
- This is a Beta Version and it stores all Blockcks data including some metrics to track more than just the Miners

