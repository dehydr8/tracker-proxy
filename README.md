# Bittorrent Tracker Proxy

## Usage
__GET__ ```/peers.php?tracker=[TRACKER_URL]&hash=[INFO_HASH]```

## Response

__SUCCESS__
```json
{
  "error":false,
  "data":{
    "count":2,
    "peers":[
      {
        "ip":"1.2.3.4",
        "port":12345
      },
      {
        "ip":"4.3.2.1",
        "port":54321
      }
    ]
  }
}
```

__FAILURE__
```json
{ "error":true, "message":"No connection response." }
```
