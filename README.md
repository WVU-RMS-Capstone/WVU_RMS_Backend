

# WVU Rehabilitation Monitoring System - Backend API - User Manual (NOT FULLY COMPLETE)
Manual goes over how to use the api in conjunciton with the native ios application.

This repo, when modifications are made to the `main` branch, will automatically update the azure server as it is watching the repository. So, to make a new version of the API (updating/adding new features), commit to `main` with the changes you want to make. Being sure to test locally first.

Note: 
Send all traffic here.
### Domain_Name = https://restapi-playerscompanion.azurewebsites.net


# Manual
Parameters in a url look like: 
**/filePath?{variable}={value}&{variable}={value}...** 
(Continued for however many parameters needed.)


# Authentication 
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

### getUserInfo | /users/auth.php
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
### updateUser | /users/auth.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getUserImage | /users/auth.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@

#  Athlete Logs
### addNotes | /users/athleteLogs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getRoster | /users/athleteLogs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getNotes | /users/athleteLogs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### updateProgress | /users/athleteLogs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getProgress | /users/athleteLogs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### programSignOff | /users/athleteLogs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@

#  Programs Information
### createExercise | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### fetchPremadePrograms | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### createProgram | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### fetchAllExercises | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### fetchExercises | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### addProgramExercises | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getProgramExercises | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### addAthleteProgram | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getAthleteProgram | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getProgramInfo | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### updateExercise | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### deleteExercise | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### deleteProgram | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### updateProgram | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### updateProgramsExercises | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@
### getProgramExercisesNames | /users/programs.php
EXAMPLE

    https://restapi-playerscompanion.azurewebsites.net/users/auth.php?action=createaccount&firstName=testing&lastName=testing&UID=2&email=testing@

