Install Process

1)first clone or download project
2)add .env file in main folder and then copy full code form .env.example and paste it in .env
3)composer install
4) change database name
5)migrate database ( php artisan mirgate)
6)in terminal run it : php artisan serve 

for check real notification for  task status update api
7)open another terminal and run it : php artisan  reverb:start
8)open another terminal and run it :  php artisan queue:listen
