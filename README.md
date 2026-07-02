# Oficina_Lima
> [!IMPORTANT] In Development

> [!NOTE]
> api/app/
>   SQL > Data validation
>   Models > Database queries
>   Services > Business logic and Error Handling
>   Controllers > HTTP layer (request → service → response)

# API Cheat Sheet
## Methods
GET = read  
POST = create  
PUT = replace/update  
PATCH = partial update  
DELETE = remove  

## Rules
{id} = required path param  
?field = optional query/filter param  
*field = required field  
field = optional field

## API:

### Makes: 
#### api/makes
> GET *?name*
> POST *\*name*
#### api/makes/{id}
> GET 
>PUT *\*name*
>DELETE

### Models
#### api/models
>GET *?name* *?make_name*
>POST *\*name* *\*make_id*
#### api/models/{id}
>GET
>PUT *\*name* *\*make_id*
>DELETE

### Cars
#### api/cars 
>GET *?plate*  *?year* *?month* *?model_name* *?make_name* *?make_id*
>POST *\*plate* *month* *year* *chassi_nr* *cc* *engine_code* *color_code* *model_id* *\*make_id*
#### api/cars/{id}
>GET
>PUT *\*plate* *\*make_id* *month* *year* *chassi_nr* *cc* *engine_code* *color_code* *model_id*
>DELETE

### Clients
#### api/clients
>GET *?name* *?phone* *?email*
>POST *\*name* *\*phone* *address* *email* *zip_code* *tax_nr*
#### api/clients/{id}
>GET
>PUT *\*name* *\*phone* *address* *email* *zip_code* *tax_nr*
>DELETE

### Services
#### api/services
>GET *?client_name* *?checkin* *?checkout* *?car_plate* *?car_model* *?car_make*
>POST *\*client_id* *kms* *checkin* *checkout* *malfunction* *service* *car_id* *schedule_id*
#### api/services/{id}
>GET
>PUT *\*client_id* *kms* *checkin* *checkout* *malfunction* *service* *car_id* *schedule_id*
>DELETE
#### api/services/{id}/user_times
>GET *?user_name* *?user_id* *?date* 
>POST {*\*service_id*} *\*user_id* *\*minutes* *\*date* 
#### api/services/{id}/user_times/{id}
>GET
>PUT *\*service_id* *\*user_id* *\*minutes* *\*date*
>DELETE
#### api/services/{id}/applied_products
>GET *?product_name* *?product_id* *?is_applied*
>POST *\*service_id* *\*product_id* *quantity* *is_applied*
#### api/services/{id}/applied_products/{id}
>GET
>PUT *\*service_id* *\*product_id* *quantity* *is_applied*
>DELETE
#### api/services_user_time
> GET *?service_id* *?user_name* *?user_id* *?minutes* *?date*
#### api/services_applied_products
>GET *?service_id* *?product_name* *?product_id* *?is_applied*

### Products
#### api/product_types
>GET *?name*
>POST *\*name*
#### api/product_types/{id}
>GET
>PUT *\*name*
>DELETE
#### api/products
>GET *?name* *?reference* *?p_t_name* *?p_t_id*
>POST *\*name* *reference* product_type_id*
#### api/products/{id}
>GET
>PUT *\*name* *reference* product_type_id*
>DELETE

### Schedule
#### api/schedules
>GET *?date* *?car_model* *?car_make* *?car_plate* *?client_name* *?client_id* 
>POST *\*date* *\*description* *car_id* *model_id* *client_id*
#### api/schedules/{id}
>GET
>PUT *\*date* *\*description* *car_id* *model_id* *client_id*
>DELETE
#### api/schedules/{id}/create_service
>POST *\*client_id* *kms* *checkin* *checkout* *malfunction* *service* *car_id* *schedule_id*

### Users
#### api/users
>GET *?name* *?email* *?user_type*
>POST *\*name* *\*email* *\*password* *\*user_type_id* *profile_pic* *nullified*
#### api/users/{id}
>GET
>PUT *\*name* *\*email* *\*user_type_id* *profile_pic* *nullified*
>DELETE
#### api/users/{id}/change_password
>POST *\*old_password* *\*new_password*
#### api/users/{id}/user_times
>GET *?service_id* *?minutes* *?date*
#### api/user_types
>GET *?name*
>POST *\*name*
#### api/user_types/{id}
>GET
>PUT *\*name*
>DELETE
