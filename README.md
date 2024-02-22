

# WVU Rehabilitation Monitoring System - Backend API - User Manual
Manual goes over how to use the api in conjunciton with the native ios application.

This repo, when modifications are made to the `main` branch, will automatically update the azure server as it is watching the repository. So, to make a new version of the API (updating/adding new features), commit to `main` with the changes you want to make. Being sure to test locally first.

Note: 
Send all traffic here.
### Domain_Name = https://restapi-playerscompanion.azurewebsites.net


# Manual
Parameters in a url look like: 
**/filePath?{variable}={value}&{variable}={value}...** 
(Continued for however many parameters needed.)


# Create an Account 
### createAccount | /users/auth.php
To have a user create an account we need to send the api the information of the user through the parameters in the URL.

Send a get request to **/users/auth.php** from our react-native app.
With the following parameters:

 - **action=createaccount**
 - **firstName={firstName}**
 - **lastName={lastName}**
 - **UID={UID}**
 - **email={email}**
 - **role={role}** Athlete or Trainer
   
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@

Note: This response from the URL will be `Username Already Exists."ID: 2"`

**Return Types**
`"True"` - If user creation was successful.
`"Username Already Exists."ID: 2""` - Stating that user is confirmed inside the database.
`"False"` - If the user gave invalid input, the database will reject and return back the error.

**NOTE**
All authentication is conducted through Firebase Authentication. Azure is used to store data on the users to include name, email, role, and UID, which comes from firebase itself. Frontend code handles authentication, while the backend will be used to verify the user and get access to the correct screens on the application. 

# Login Into Application
### Login |  /users/auth.php
To login, the API will read the url to gather the information from the user.

Send a get request to **/users/auth.php** from our react-native app.
With the following parameters:

 - **action=login**
 - **UID={UID}**
 - **email={email}**

EXAMPLE: 

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=login&UID=0000000000000000000000000000&email=test@

**Return Types**

`"Username does not exist. Create account."` - User does not have an account.

`"Invalid Credentials"` - Invalid login credentials were inputted.

## Logout | /users/auth.php
All we need to send to the API for a user to logout is the action variable value 'logout' and their UserID.

 - **action=logout**
 - **userid={ID}**

**This will be updated in the future by providing the API with the username instead of the userid**

You can get the userid via the user data methods below.

EXAMPLE:

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=logout&userid=15
https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=logout&userid=15



# User Data
All ways to get a users assigned workouts / user info

**All requests to this endpoint must have the bearer token in the authorization header of the get request. You will not be able to get information if the user does not have a active session.**

To get a session token from the user you must have the user login.

React-Native example code:

    fetch('https://restapi-playerscompanion.azurewebsites.net/users/users.php?action=userinfo
    ', {
    	headers: {
    		'Authorization': 'Bearer ' + session_token
    	}
    })
    .then(response => {
    // handle response
    
    })
    .catch(error => {
    // handle error
    
    });

The auth string should look like this:

    Bearer 9913f272ed8a08587cefb45634e15ef1788531f119a663acd505406962f72e1a

## Get UserInfo | /users/users.php
This returns the user info such as (Name, Player Number, Account Type...)

All you need to send to the API is the action variable with value 'userinfo' and you must include the authorization token in the auth header.

 - **action=userinfo**
+ **session_token in auth header**

EXAMPLE: 

    https://restapi-playerscompanion.azurewebsites.net/users/users.php?action=userinfo

**Return Types**

`"Missing Authorization Header."` - Must include session_token in auth header.

`"Session expired. Please login again."` - Users session expired. Typically this is if the user reached the 8hr session limit or if the user has logged out.

If there is a active session you will receive a json object like below:

    {
    "UserId": 15,
    "FirstName":"Grant",
    "MiddleName": "Perry",
    "LastName": "Holzemer",
    "UserType": "P",
    "Username": "grantiscool",
    "Password": "bae5e3208a3c700e3db642b6631e95b9",
    "PlayerNumber": 999999999,
    "Code": 99999999,
    "Position": null
    }


