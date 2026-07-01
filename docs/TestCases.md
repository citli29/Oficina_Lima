# /api/makes
## GET
> Testar se a filtragem esta a ser bem feita:
>- make_name

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- make_name -> unique

# /api/makes/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- make_name -> unique

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/models
## GET
> Testar se a filtragem esta a ser bem feita:
>- make_name
>- model_name

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- make_id -> foreign key
>- model_name -> unique (combination make_id and model_name)

# /api/models/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- make_id -> foreign key
>- model_name -> unique (combination make_id and model_name)

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/cars
> Testar se a filtragem esta a ser bem feita:
>- plate
>- model_name
>- make_name
>- make_id
>- year
>- month

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- plate -> unique
>- make_id -> foreign key
>- model_id -> se make_id != null, model.make_id == make_id

# /api/cars/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- plate -> unique
>- make_id -> foreign key
>- model_id -> se make_id != null, model.make_id == make_id

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/clients
## GET
> Testar se a filtragem esta a ser bem feita:
>- name
>- phone
>- email

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- name -> obrigatorio
>- phone -> obrigatorio

# /api/clients/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- name -> obrigatorio
>- phone -> obrigatorio

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/services
## GET
> Testar se a filtragem esta a ser bem feita:
>- client_name
>- checkin
>- checkout
>- car_plate
>- car_model
>- car_make

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- client_id -> obrigatorio, unique
>- se car_id -> kms obrigatorio
>- checkout > checkin

# /api/services/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- client_id -> obrigatorio, unique
>- se car_id -> kms obrigatorio
>- checkout > checkin

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/services/{id}/user_times
## GET
> Testar se a filtragem esta a ser bem feita:
>- user_name
>- user_id
>- date

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- service_id -> foreign key
>- user_id -> foreign key
>- minutes -> obrigatorio
>- date -> obrigatorio

# /api/services/{id}/user_times/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- service_id -> foreign key
>- user_id -> foreign key
>- minutes -> obrigatorio
>- date -> obrigatorio

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/services/{id}/applied_products
## GET
> Testar se a filtragem esta a ser bem feita:
>- product_name
>- product_id
>- is_applied

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- service_id -> foreign key
>- product_id -> foreign key
>- quantity > 0 

# /api/services/{id}/applied_products/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- service_id -> foreign key
>- product_id -> foreign key
>- quantity > 0 

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/services_user_time
## GET
> Testar se a filtragem esta a ser bem feita:
>- service_id
>- user_name
>- user_id
>- minutes
>- date

# /api/services_applied_products
## GET
> Testar se a filtragem esta a ser bem feita:
>- service_id
>- product_name
>- product_id
>- is_applied

# /api/product_types
## GET
> Testar se a filtragem esta a ser bem feita:
>- designation

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- designation -> unique

# /api/product_types/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- designation -> unique

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/products
## GET
> Testar se a filtragem esta a ser bem feita:
>- designation
>- reference
>- p_t_name
>- p_t_id

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- designation -> unique

# /api/products/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- designation -> unique

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/schedules
## GET
> Testar se a filtragem esta a ser bem feita:
>- date
>- car_model
>- car_make
>- car_plate
>- client_name
>- client_id

## POST
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- date -> obrigatorio
>- description -> obrigatorio

# /api/schedules/{id}
## GET
>- Erro se nao existir
>- Sucesso se existir

## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- date -> obrigatorio
>- description -> obrigatorio

## DELETE
>- Erro se nao existir
>- Sucesso se existir

# /api/schedules/{id}/create_service
## PUT
> Testar se verifica os campos necessarios e se cumpre os requisitos
>- client_id -> obrigatorio, unique
>- se car_id -> kms obrigatorio
>- checkout > checkin
# /api/users
# /api/users/{id}
# /api/users/{id}/user_times
# /api/users/{id}/change_password
# /api/user_types
# /api/users_types/{id}
