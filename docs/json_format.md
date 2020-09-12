
## Default JSON Response Structure

The JSON response from an API is expected to be a JSON document containing either a single
object, or a JSON document that contains a `data` element that is an array of objects.

For example a single object could be:

```json
{
    "id": "52530121-cc5c-4f56-9c39-734db94c0607",
    "name": "bob"
}
```

Or a collection or objects:

```json
{
    "data": [
        {
            "id": "52530121-cc5c-4f56-9c39-734db94c0607",
            "name": "bob"
        },
        {
            "id": "05dbf6d3-2042-4363-bfb8-00153417e812",
            "name": "foo"
        }
    ]
}
```

For nested related data; it is expected to be keyed on a property name without any other
element names:

```json
{
    "id": "52530121-cc5c-4f56-9c39-734db94c0607",
    "name": "bob",
    "groups": [
        {
            "id": "31ea5893-809a-4512-b44d-43cad1da35cf",
            "name": "user"
        }
    ]
}
```

__Note:__ collections must be arrays of objects. Collections defined as objects will not be
correctly decoded by default. A custom response decoder will be required in those instances.

### Paginated Response Format

A paginated result set is expected to have the following structure:

```json
{
    "data": [
        {
            "id": "52530121-cc5c-4f56-9c39-734db94c0607",
            "name": "bob"
        },
        {
            "id": "05dbf6d3-2042-4363-bfb8-00153417e812",
            "name": "foo"
        }
    ],
    "meta": {
        "pagination": {
            "total": 200,
            "count": 30,
            "per_page": 30,
            "current_page": 1,
            "total_pages": 7,
            "links": {
                "next": "http:\\/\\/api.example.dev\\/v1\\/users?page=2"
            }
        }
    }
}
```

This behaviour can be changed by overriding the `ModelBuilder` and re-implementing the logic in
`fetch()`.
