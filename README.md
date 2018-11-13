# Facebook-OAuth-Client-PHP
A PHP class to fetch user data from Facebook graph API using OAuth2. Mainly created for Facebook Login implementation in PHP. Extremely simple and easy use. Only one class, no dependencies.

## Requirements
- PHP7 (just remove type declarations for PHP5 compatibility)
- The curl extension must be installed and loaded in PHP
- Facebook OAuth Application

### Facebook OAuth Application
Before you start coding, you have to create an OAuth App at https://developers.facebook.com/apps/.
Once you create, your App will be given unique **App ID** and **App Secret** (find it at Settings -> Basic).

You will also need a Facebook Login product attached to your OAuth app. To do so, go to your app Dashboard, scroll down to "Add a Product", find "Facebook Login" product and click "Set Up". You will be asked for platform for this app, choose WWW - Web and save it.

One last thing you will need is **User Token**. You can find and generate it at https://developers.facebook.com/tools/accesstoken (you won't need App Token).

## Use example
We will have four files, for readability, even though it can be done in only two files:
- **Facebook_OAuth_Client.php** - The main class
- **fbClient.php** - We will create and configure an object here, and include it in the other files, so we don't have to do it multiple times
- **login.php** - Will only contain a "Facebook Login" button (anchor) which starts the process
- **callback.php** - A file that will be called by our OAuth App

#### `fbClient.php`
```php
<?php
require_once "Facebook_OAuth_Client.php";

$fbClient = new Facebook_OAuth_Client;
$fbClient->setClientId("YOUR_APP_ID");
$fbClient->setClientSecret("YOUR_APP_SECRET");
$fbClient->setUserToken("YOUR_USER_TOKEN");
$fbClient->setRedirectUri("http://localhost/YOUR_APP_FOLDER/callback.php");

// Permissions - The permissions your app will ask from user to be granted
//               https://developers.facebook.com/docs/graph-api/reference/user/permissions
$fbClient->setPermissions("public_profile,email");

// Fields - The data fields you want to fetch from API
//          https://developers.facebook.com/docs/places/fields/
$fbClient->setFields("name,email,first_name,last_name");
```

#### `login.php`
```php
<?php
require "fbClient.php";

$buttonUrl = $fbClient->createAuthUrl();

print "<a href='{$buttonUrl}'>Login via Facebook!</a>";
```

#### `callback.php`
```php
<?php
require "fbClient.php";

// OAuth App will set 'code' parameter when calling callback
if (isset($_GET["code"]))
{
    // Making sure everything went OK
    if ($fbClient->fetchAccessTokenWithAuthCode($_GET["code"]) &&
        $fbClient->fetchUserId() &&
        $data = $fbClient->fetchData()
    ) {
        // Success, data is fetched
        var_dump($data);
        
        // However, if user declined some of the permissions you had required,
        // there will be no errors, but you will not get declined data

        // So if we require user's email, we should check if we got email field,
        // otherwise, user declined that permission
        if (isset($data["email"]))
        {
            // Now goes your login implementation
            //      Register user to your database
            //      Write user data to session
            //      etc.
        }
        else
        {
            print "Email address permission is required for Facebook Login!";
        }
    }
    // If not, we can print out the last response from API to see what's the problem
    else
    {
        print "Error: " . $fbClient->getLastResponse();
    }
}
else
{
    print "Missing 'code' parameter!";
}
```
