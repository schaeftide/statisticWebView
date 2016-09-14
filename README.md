## README

welcome, this web view uses files provided by the factorio mod 'Statistic' (https://mods.factorio.com/mods/KaleR/Statistic)
 to create a web view of your statistic.
 
### Installation
just put all files in a folder access able by your web server and edit the config.json file.
In the config file you must enter the base path of your factorio installation.

After you have edited the config file, you must enable a scheduled job (e.b. cronjob) that will run every minute
and execute the ```cron.php``` file.

now have fun :)