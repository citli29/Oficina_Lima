# Tasks

## Implement API

### Database CRUD
#### Car - MVC
>##### Create and List Makes
>>  - [ ] api/makes
>>>    - [ ] GET *?make_name*
>>>    - [ ] POST *\*make_name*

>##### Read, Update and Delete Make
>>  - [ ] api/makes/{id}
>>>    - [ ] GET 
>>>    - [ ] PUT *\*make_name*
>>>    - [ ] DELETE

>##### Create and List Models
>>  - [ ] api/models
>>>    - [ ] GET *?model_name* *?make_name*
>>>    - [ ] POST *\*model_name* *\*make_id*

>##### Read, Update and Delete Model
>>  - [ ] api/models/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*model_name* *\*make_id*
>>>    - [ ] DELETE

>##### Create and List Cars
>>  - [ ] api/cars 
>>>    - [ ] GET *?plate* *?model_name* *?make_id* *?year* *?month*
>>>    - [ ] POST *\*plate* *\*make_id* *month* *year* *chassi_nr* *cc* *engine_code* *color_code* *model_id*

>##### Read, Update and Delete Car
>>  - [ ] api/cars/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*plate* *\*make_id* *month* *year* *chassi_nr* *cc* *engine_code* *color_code* *model_id*
>>>    - [ ] DELETE

#### Client - MVC
>##### Create and List Clients
>>  - [ ] api/clients
>>>    - [ ] GET *?name* *?phone* *?email*
>>>    - [ ] POST *\*name* *\*phone* *address* *email* *zip_code* *tax_nr*

>##### Read, Update and Delete Client
>>  - [ ] api/clients/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*name* *\*phone* *address* *email* *zip_code* *tax_nr*
>>>    - [ ] DELETE

#### Service - MVC
>##### Create and List Services
>>  - [ ] api/services
>>>    - [ ] GET *?client_name* *?checkin* *?checkout* *?car_plate* *?car_model* *?car_make*
>>>    - [ ] POST *\*client_id* *kms* *checkin* *checkout* *malfunction* *service* *car_id* *schedule_id*

>##### Read, Update and Delete Service
>>  - [ ] api/services/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*client_id* *kms* *checkin* *checkout* *malfunction* *service* *car_id* *schedule_id*
>>>    - [ ] DELETE

>##### Create and List User Times of a Service
>>  - [ ] api/services/{id}/user_times
>>>    - [ ] GET *?user_name* *?user_id* *?date* 
>>>    - [ ] POST *\*service_id* *\*user_id* *\*minutes* *\*date* 

>##### Read, Update and Delete User Time of a Service 
>>  - [ ] api/services/{id}/user_times/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*service_id* *\*user_id* *\*minutes* *\*date*
>>>    - [ ] DELETE

>##### Create and List Applied Products of a Service 
>>  - [ ] api/services/{id}/applied_products
>>>    - [ ] GET *?product_name* *?product_id* *?is_applied*
>>>    - [ ] POST *\*service_id* *\*product_id* *quantity* *is_applied*

>##### Read, Update and Delete Applied Products of a Service
>>  - [ ] api/services/{id}/applied_products/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*service_id* *\*product_id* *quantity* *is_applied*
>>>    - [ ] DELETE

>##### List User Times
>>  - [ ] api/services_user_time
>>>    - [ ] GET *?service_id* *?user_name* *?user_id* *?minutes* *?date*

>##### List Applied Products
>>  - [ ] api/services_applied_products
>>>    - [ ] GET *?service_id* *?product_name* *?product_id* *?is_applied*

#### Product - MVC
>##### Create and List Product Types
>>  - [ ] api/product_types
>>>    - [ ] GET *?designation*
>>>    - [ ] POST *\*designation*

>##### Read, Update and Delete Product Types
>>  - [ ] api/product_types/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*designation*
>>>    - [ ] DELETE

>##### Create and List Products
>>  - [ ] api/products
>>>    - [ ] GET *?designation* *?reference* *?p_t_name* *?p_t_id*
>>>    - [ ] POST *\*designation* *reference* product_type_id*

>##### Read, Update and Delete Products
>>  - [ ] api/products/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*designation* *reference* product_type_id*
>>>    - [ ] DELETE

#### Schedule - MVC
>##### Create and List Schedules
>>  - [ ] api/schedules
>>>    - [ ] GET *?date* *?car_model* *?car_make* *?car_plate* *?client_name* *?client_id* 
>>>    - [ ] POST *\*date* *\*description* *car_id* *model_id* *client_id*

>##### Read, Update and Delete Schedules 
>>  - [ ] api/schedules/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*date* *\*description* *car_id* *model_id* *client_id*
>>>    - [ ] DELETE

>##### Create a Service based on a Schedule Info
>>  - [ ] api/schedules/{id}/create_service
>>>    - [ ] POST *\*client_id* *kms* *checkin* *checkout* *malfunction* *service* *car_id* *schedule_id*

#### User - MVC
>##### Create and List Users
>>  - [ ] api/users
>>>    - [ ] GET *?name* *?email* *?user_type*
>>>    - [ ] POST *\*name* *\*email* *\*password* *\*user_type_id* *profile_pic* *nullified*

>##### Read, Update and Delete User 
>>  - [ ] api/users/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*name* *\*email* *\*user_type_id* *profile_pic* *nullified*
>>>    - [ ] DELETE

>##### Update Password
>>  - [ ] api/users/{id}/change_password
>>>    - [ ] POST *\*old_password* *\*new_password*

>##### Read User's User Times
>>  - [ ] api/users/{id}/user_times
>>>    - [ ] GET *?service_id* *?minutes* *?date*

>##### Create and List User Types
>>  - [ ] api/user_types
>>>    - [ ] GET *?designation*
>>>    - [ ] POST *\*designation*

>##### Read, Update and Delete User Type
>>  - [ ] api/user_types/{id}
>>>    - [ ] GET
>>>    - [ ] PUT *\*designation*
>>>    - [ ] DELETE

### App Actions

## Implement Front-End

