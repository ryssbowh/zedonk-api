# Zedonk API

Simple wrapper to read a zedonk repository through its api.

You'll need to set your credentials before using it :
`ZedonkAPI\ZedonkAPI::setCredentials($url, $key, $user, $password)`

Each call will return an array of `\ZedonkAPI\ZedonkEntity` which has basic getters/setters

Filters must have the following form
```
[
    ['price > 100', 'and'],
    ["vat = 'true'", 'or'] //Note the extra quotes here
]
```

Change the default season name with `ZedonkAPI::setSeason($season)`

Available calls :
- `ZedonkAPI\ZedonkAPI::getProducts($filters = [])`
- `ZedonkAPI\ZedonkAPI::getCustomers($filters = [])`
- `ZedonkAPI\ZedonkAPI::getOrders($filters = [])`
- `ZedonkAPI\ZedonkAPI::getInventory($filters = [])`

Or you could call any report by its name : 
`ZedonkAPI\ZedonkAPI::call('MyCustomReport', $filters = [])`