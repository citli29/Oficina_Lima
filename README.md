# Oficina_Lima
> [!NOTE]
> api/app/
>   SQL > Triggers checking 
>   Models > ONLY database queries
>   Services > business rules + validation + combining models
>   Controllers > HTTP layer (request → service → response)

# Features

## Actor
> O - Office
> M - Mechanic
> B - Both
## Calendar
> /api/calendar
- [ ] Search for a Schedule Instance - B
    > GET: /api/calendar?
- [ ] Browse Global Schedules - B 
    > GET: /api/calendar
- [ ] View Instance Schedule - B
    > GET: /api/calendar/{id}
- [ ] Add Instance Schedule - B
    > POST: /api/calendar
- [ ] Remove Instance Schedule - B
    > DELETE: /api/calendar/{id}
- [ ] Edit Instance Schedule - B
    > PUT: /api/calendar/{id}
- [ ] Create a Service Based on the Schedule Instance - B
    > POST: /api/calendar/{id}/service

## Services
> /api/services
> /api/service

- [ ] Browse a list of Services - B
    > GET: /api/services
- [ ] View a Service Instance - B
    > GET: /api/service/{id}
- [ ] Create a Service Instance - B
    > POST: /api/service/{id}
- [ ] Remove a Service Instance - O 
    > DELETE: /api/service/{id}
- [ ] Edit a Service Instance - B
    > PUT: /api/service/{id}

    ### Cars

- [ ] Link the Car to the Service - B
    - [ ] Create a new instance of a Car 
        > POST: /api/cars
    - [ ] Associate the service to an existent instance of a Car 
        > PUT: /api/service/{id}/car
    - [ ] Remove the association to the Car
        > DELETE: /api/service/{id}/car

    ### Clients

- [ ] Link the Client to the Service - O
    - [ ] Create a new instance of a Client 
        > POST: /api/clients
    - [ ] Associate the service to an existent instance of a Car
        > PUT: /api/service/{id}/client
    - [ ] Remove the association to the Car
        > DELETE: /api/service/{id}/client

    ### Products

- [ ] Link Products to the Service
    > POST: /api/service/{id}/product
- [ ] Mark the Product as Applied
    > PUT: /api/service/{id}/product
- [ ] Unmark the Product as Applied
    > PUT: /api/service/{id}/product
- [ ] Remove the Product from the Service
    > DELETE: /api/service/{id}/product

    ### User Time Register

- [ ] Add User Timer Register to the Service
    > POST: /api/service/{id}/time
- [ ] Remove User Timer Register to the Service
    > DELETE: /api/service/{id}/time
- [ ] Edit User Timer Register to the Service
    > PUT: /api/service/{id}/time

## Management

### Clients
> /api/clients
> /api/client

- [x] List with Search
    > GET: /api/clients
- [x] Create
    > POST: /api/clients
- [x] Read
    > GET: /api/client/{id}
- [x] Update
    > PUT: /api/client/{id}
- [x] Delete
    > DELETE: /api/client/{id}

### Products
> /api/products
> /api/product

> /api/product_types
> /api/product_type

- [x] List with Search 
    > GET: /api/products
- [x] Create
    > POST: /api/products
- [x] Read
    > GET: /api/product/{id}
- [x] Update
    > PUT: /api/product/{id}
- [x] Delete
    > DELETE: /api/product/{id}

### Cars
> /api/cars
> /api/car

> /api/models
> /api/model

> /api/makes
> /api/make

- [x] List with Search
    > GET: /api/cars
- [x] Create
    > POST: /api/cars
- [x] Read
    > GET: /api/car/{id}
- [x] Update
    > PUT: /api/car/{id}
- [x] Delete
    > DELETE: /api/car/{id}

