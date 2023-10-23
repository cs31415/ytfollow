# ytfollow
Bulk subscribe to Youtube channels

### Dependencies

PHP

### Installation

Copy the contents of the src folder into a `ytfollow` folder under web root (`/Library/WebServer/Documents` for Apache on Mac OS). Copy the ytfollow_template.ini file under web root, rename it to ytfollow.ini and fill it out with your app's details from Google Developer Dashboard. Copy the client_secret_template.json to web root, rename it to client_secret.json and fill it out with details from your youtube app in the Google API Dashboard (See resources below).

Navigate to `http://localhost/ytfollow` and select the `Login to Youtube` button, followed by `Follow All Artists`. 

### Resources

- [OAuth](https://developers.google.com/identity/protocols/oauth2)
- [Subscriptions: Insert](https://developers.google.com/youtube/v3/docs/subscriptions/insert)
- [Google API Dashboard](https://console.cloud.google.com/apis/dashboard)

