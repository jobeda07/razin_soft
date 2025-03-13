Install Process

1) Clone or download the project.  
2) Add an .env file in the main folder and copy the full content from .env.example, then paste it into the new .env file.  
3) Run the following command to install dependencies:  

   composer install

4) Update the database name and credentials in the .env file.  
5) Migrate the database using:  

   php artisan migrate

6) Start the Laravel development server:  

   php artisan serve


**To check real-time notifications for task status update API:**  

7) Open another terminal and start the WebSocket server:  

   php artisan reverb:start

8) Open another terminal and start the queue listener:  
 
   php artisan queue:listen
