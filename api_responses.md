# api/makes
## GET
### Response

{
    "success" : true,
    "make_list" : {
        {
            "id" : <id>
            "name" : <make name>
            "logo" : <logo path>
        }, ...
    }
}

## POST
### Request

{
    "name" : <make name>,
    "logo" : <logo path>
}

### Response

{    
    "success" : true,
    "make" : 
        {
            "id" : <id>
            "name" : <make name>
            "logo" : <logo path>
        }
}

# api/makes/{id}
## GET
### Response

{
    "success" : true,
    "make" : 
    {
            "id" : <id>
            "name" : <make name>
            "logo" : <logo path>
    }
}

## PUT
### Request

{
    "name" : <make name>,
    "logo" : <logo path>
}

### Response

{    
    "success" : true,
    "make" : 
        {
            "id" : <id>
            "name" : <make name>
            "logo" : <logo path>
        }
}
## DELETE
### Response

{    
    "success" : true,
    "make" : 
        {
            "id" : <id>
            "name" : <make name>
            "logo" : <logo path>
        }
}

# api/models
## GET

{
    "success" : true,
    "model_list" : {
        {
            "id" : <id>
            "name" : <model name>
            "make_id" : <make id>
            "make_name" : <make name>
        }, ...
    }
}

## POST
### Request

{
    "name" : <make name>,
    "make_id" : <make id>
}

### Response

{    
    "success" : true,
    "model" : 
        {
            "id" : <id>
            "name" : <model name>
            "make_id" : <make id>
            "make_name" : <make name>
    }
}

# api/models/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/cars 
## GET 
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/cars/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/clients
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/clients/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services/{id}/user_times
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services/{id}/user_times/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services/{id}/applied_products
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services/{id}/applied_products/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/services_user_time
## GET
### Response

# api/services_applied_products
## GET
### Response

# api/product_types
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/product_types/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/products
## GET 
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/products/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT 
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/schedules
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/schedules/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/schedules/{id}/create_service
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/users
## GET
### Response
## POST
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/users/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/users/{id}/change_password
## POST
### Request
### Response

# api/users/{id}/user_times
## GET *?service_id* *?minutes* *?date*
### Response

# api/user_types
## GET *?name*
### Response
## POST 
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}

# api/user_types/{id}
## GET
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## PUT
### Request
### Response
{
    "success" : true,
    "" : {
        "" : ""
    }
}
## DELETE
### Response

{
    "success" : true,
    "user_type" : {
        "id" : <id>
        "name" : <name>
    }
}

